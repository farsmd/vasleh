<?php
declare(strict_types=1);

namespace Modules\Api;

use Core\Container;
use Core\Request;
use Core\Response;
use InvalidArgumentException;
use PDOException;

/**
 * endpointهای راه‌دور API — احراز فقط با توکن Bearer (بدون سشن و CSRF).
 * هدر: Authorization: Bearer <token>  یا  X-Api-Token: <token>
 */
final class RemoteController
{
    public function __construct(private readonly Container $container) {}

    private function tokens(): ApiTokens
    {
        return $this->container->get('api.tokens');
    }

    private function guard(): ?FileGuard
    {
        return new FileGuard((string) $this->container->get('base_path'));
    }

    /** @return array|Response ردیف توکن معتبر، یا پاسخ ۴۰۱ */
    private function requireToken(): array|Response
    {
        $header = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
        $token = '';
        if (preg_match('/^Bearer\s+(.+)$/i', $header, $m) === 1) {
            $token = trim($m[1]);
        } elseif (!empty($_SERVER['HTTP_X_API_TOKEN'])) {
            $token = trim((string) $_SERVER['HTTP_X_API_TOKEN']);
        }
        $row = $this->tokens()->authenticate($token);
        if ($row === null) {
            return Response::json(['ok' => false, 'error' => 'unauthorized'], 401);
        }
        return $row;
    }

    private function jsonBody(): array
    {
        $raw = (string) file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private function fail(InvalidArgumentException $e): Response
    {
        return Response::json(['ok' => false, 'error' => $e->getMessage()], 400);
    }

    public function ping(Request $request): Response
    {
        $auth = $this->requireToken();
        if ($auth instanceof Response) {
            return $auth;
        }
        $modules = [];
        foreach ($this->container->get('loader')->active() as $slug => $definition) {
            $modules[] = ['slug' => $slug, 'title' => (string) ($definition['title'] ?? $slug), 'version' => (string) ($definition['version'] ?? '')];
        }
        return Response::json([
            'ok'      => true,
            'app'     => 'وصله',
            'php'     => PHP_VERSION,
            'time'    => date('c'),
            'token'   => ['id' => (int) $auth['id'], 'name' => (string) $auth['name']],
            'modules' => $modules,
        ]);
    }

    public function files(Request $request): Response
    {
        $auth = $this->requireToken();
        if ($auth instanceof Response) {
            return $auth;
        }
        $path = (string) $request->input('path', '');
        try {
            $entries = $this->guard()->list($path);
        } catch (InvalidArgumentException $e) {
            return $this->fail($e);
        }
        return Response::json(['ok' => true, 'path' => $path, 'entries' => $entries]);
    }

    public function fileRead(Request $request): Response
    {
        $auth = $this->requireToken();
        if ($auth instanceof Response) {
            return $auth;
        }
        $path = (string) $request->input('path', '');
        try {
            $data = $this->guard()->read($path);
        } catch (InvalidArgumentException $e) {
            return $this->fail($e);
        }
        return Response::json(['ok' => true, 'path' => $path] + $data);
    }

    public function fileWrite(Request $request): Response
    {
        $auth = $this->requireToken();
        if ($auth instanceof Response) {
            return $auth;
        }
        $body = $this->jsonBody();
        $path = (string) ($body['path'] ?? '');
        $content = (string) ($body['content'] ?? '');
        if (($body['encoding'] ?? 'utf8') === 'base64') {
            $decoded = base64_decode($content, true);
            if ($decoded === false) {
                return Response::json(['ok' => false, 'error' => 'base64 نامعتبر است'], 400);
            }
            $content = $decoded;
        }
        try {
            $size = $this->guard()->write($path, $content);
        } catch (InvalidArgumentException $e) {
            return $this->fail($e);
        }
        return Response::json(['ok' => true, 'path' => $path, 'size' => $size]);
    }

    public function fileDelete(Request $request): Response
    {
        $auth = $this->requireToken();
        if ($auth instanceof Response) {
            return $auth;
        }
        $path = (string) ($request->input('path') ?? $this->jsonBody()['path'] ?? '');
        try {
            $this->guard()->delete($path);
        } catch (InvalidArgumentException $e) {
            return $this->fail($e);
        }
        return Response::json(['ok' => true, 'path' => $path, 'deleted' => true]);
    }

    public function dbTables(Request $request): Response
    {
        $auth = $this->requireToken();
        if ($auth instanceof Response) {
            return $auth;
        }
        $pdo = $this->container->get('db')->pdo();
        $rows = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type IN ('table','view') AND name NOT LIKE 'sqlite_%' ORDER BY name")
            ->fetchAll();
        foreach ($rows as &$row) {
            $count = $pdo->query('SELECT COUNT(*) FROM "' . str_replace('"', '', (string) $row['name']) . '"')->fetchColumn();
            $row['rows'] = (int) $count;
        }
        return Response::json(['ok' => true, 'tables' => $rows]);
    }

    public function dbQuery(Request $request): Response
    {
        $auth = $this->requireToken();
        if ($auth instanceof Response) {
            return $auth;
        }
        $body = $this->jsonBody();
        $sql = trim((string) ($body['sql'] ?? ''));
        $params = array_values((array) ($body['params'] ?? []));
        if ($sql === '') {
            return Response::json(['ok' => false, 'error' => 'sql خالی است'], 400);
        }
        $first = strtolower((string) strtok($sql, " \t\n("));
        $isRead = in_array($first, ['select', 'with', 'pragma', 'explain'], true);

        try {
            $stmt = $this->container->get('db')->pdo()->prepare($sql);
            $stmt->execute($params);
        } catch (PDOException $e) {
            return Response::json(['ok' => false, 'error' => 'خطای SQL: ' . $e->getMessage()], 400);
        }

        if ($isRead) {
            $rows = $stmt->fetchAll();
            $total = count($rows);
            return Response::json([
                'ok'        => true,
                'type'      => 'read',
                'rows'      => array_slice($rows, 0, 1000),
                'count'     => min($total, 1000),
                'truncated' => $total > 1000,
            ]);
        }
        return Response::json([
            'ok'           => true,
            'type'         => 'write',
            'affected'     => $stmt->rowCount(),
            'lastInsertId' => (int) $this->container->get('db')->pdo()->lastInsertId() ?: null,
        ]);
    }
}
