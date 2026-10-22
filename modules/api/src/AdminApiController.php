<?php
declare(strict_types=1);

namespace Modules\Api;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;
use Modules\Session\SessionManager;

/** صفحه مدیریت توکن‌های API — فقط مالک. (CSRF همه POSTهای /admin با هوک session است.) */
final class AdminApiController
{
    public function __construct(private readonly Container $container) {}

    private function session(): SessionManager
    {
        return $this->container->get('session');
    }

    private function tokens(): ApiTokens
    {
        return $this->container->get('api.tokens');
    }

    private function ownerOnly(): ?Response
    {
        if (($this->session()->user()['role'] ?? '') !== 'owner') {
            return Response::forbidden('مدیریت توکن‌های API فقط برای مالک است');
        }
        return null;
    }

    public function index(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $session = $this->session();
        $newToken = $session->get('_api_new_token');
        if ($newToken !== null) {
            $session->set('_api_new_token', null); // فقط یک بار نمایش
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $scheme . '://' . (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');

        $content = View::render('api', 'admin', 'tokens', [
            'tokens'   => $this->tokens()->all(),
            'csrf'     => $session->csrfToken(),
            'newToken' => is_string($newToken) ? $newToken : null,
            'baseUrl'  => $baseUrl,
        ]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => 'دسترسی API',
            'content' => $content,
            'menu'    => $this->container->get('loader')->adminMenu(),
            'user'    => $session->user(),
            'csrf'    => $session->csrfToken(),
        ]));
    }

    public function store(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $name = trim((string) $request->input('name', ''));
        $created = $this->tokens()->create($name);
        $this->session()->set('_api_new_token', $created['token']);
        if ($this->container->has('logger')) {
            $actor = (string) ($this->session()->user()['username'] ?? '');
            $this->container->get('logger')->user($actor, 'ساخت توکن API', $name !== '' ? $name : 'بدون نام');
        }
        return Response::redirect('/admin/api');
    }

    public function revoke(Request $request, array $params): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $id = (int) ($params['id'] ?? 0);
        $this->tokens()->revoke($id);
        if ($this->container->has('logger')) {
            $actor = (string) ($this->session()->user()['username'] ?? '');
            $this->container->get('logger')->user($actor, 'لغو توکن API', '#' . $id);
        }
        return Response::redirect('/admin/api');
    }
}
