<?php
declare(strict_types=1);

namespace Modules\Session;

use Core\Request;
use Modules\Sqlite\Database;

/**
 * سشن امن — درس‌گرفته از تجربه هاست اشتراکی لاینرلایت:
 * سشن‌های خود PHP روی هاست اشتراکی زود پاک می‌شدند؛ اینجا داده سشن
 * داخل دیتابیس می‌ماند و کوکی فقط یک شناسه «امضاشده» با HMAC است.
 * کوکی: HttpOnly + SameSite=Lax (+ Secure روی HTTPS)، چرخش شناسه هنگام ورود.
 */
final class SessionManager
{
    private const COOKIE = 'app_session';

    private ?string $id = null;

    /** @var array<string, mixed> */
    private array $data = [];

    private bool $started = false;

    public function __construct(
        private readonly Database $db,
        private readonly string $appKey,
        private readonly int $lifetime = 604800, // ۷ روز
    ) {}

    public function start(Request $request): void
    {
        if ($this->started) {
            return;
        }
        $this->started = true;
        $this->db->pdo()->exec('DELETE FROM app_sessions WHERE expires_at < ' . time());

        $raw = (string) ($_COOKIE[self::COOKIE] ?? '');
        if (str_contains($raw, '.')) {
            [$id, $signature] = explode('.', $raw, 2);
            if (preg_match('/^[a-f0-9]{64}$/', $id) === 1 && hash_equals($this->sign($id), $signature)) {
                $stmt = $this->db->pdo()->prepare('SELECT data FROM app_sessions WHERE id = ? AND expires_at >= ?');
                $stmt->execute([$id, time()]);
                $json = $stmt->fetchColumn();
                if (is_string($json)) {
                    $this->id = $id;
                    $this->data = (array) json_decode($json, true);
                    return;
                }
            }
        }

        $this->id = bin2hex(random_bytes(32));
        $this->data = [];
        $this->persist();
        $this->sendCookie();
    }

    private function sign(string $id): string
    {
        return hash_hmac('sha256', $id, $this->appKey);
    }

    private function persist(): void
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO app_sessions (id, data, expires_at) VALUES (?, ?, ?)
             ON CONFLICT(id) DO UPDATE SET data = excluded.data, expires_at = excluded.expires_at'
        );
        $stmt->execute([$this->id, json_encode($this->data, JSON_UNESCAPED_UNICODE), time() + $this->lifetime]);
    }

    private function sendCookie(): void
    {
        setcookie(self::COOKIE, $this->id . '.' . $this->sign((string) $this->id), [
            'expires'  => time() + $this->lifetime,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => (($_SERVER['HTTPS'] ?? '') !== '' && ($_SERVER['HTTPS'] ?? '') !== 'off'),
        ]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
        $this->persist();
    }

    /** @return array{id:int, username:string, role:string}|null */
    public function user(): ?array
    {
        $user = $this->data['_user'] ?? null;
        return is_array($user) ? $user : null;
    }

    /** هنگام ورود شناسه سشن عوض می‌شود تا سشن مهمان قابل سوءاستفاده نباشد. */
    public function login(array $user): void
    {
        $this->db->pdo()->prepare('DELETE FROM app_sessions WHERE id = ?')->execute([$this->id]);
        $this->id = bin2hex(random_bytes(32));
        $this->data = ['_user' => $user, '_csrf' => bin2hex(random_bytes(32))];
        $this->persist();
        $this->sendCookie();
    }

    public function logout(): void
    {
        $this->db->pdo()->prepare('DELETE FROM app_sessions WHERE id = ?')->execute([$this->id]);
        $this->data = [];
        setcookie(self::COOKIE, '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    }

    public function csrfToken(): string
    {
        $token = (string) ($this->data['_csrf'] ?? '');
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $this->data['_csrf'] = $token;
            $this->persist();
        }
        return $token;
    }

    public function validCsrf(?string $token): bool
    {
        return is_string($token) && $token !== '' && hash_equals($this->csrfToken(), $token);
    }
}
