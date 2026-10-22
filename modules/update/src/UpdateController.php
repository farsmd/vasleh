<?php
declare(strict_types=1);

namespace Modules\Update;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;
use Modules\Session\SessionManager;

/** صفحه آپدیت — فقط مالک. (CSRF همه POSTهای /admin با هوک session است.) */
final class UpdateController
{
    public function __construct(private readonly Container $container) {}

    private function session(): SessionManager
    {
        return $this->container->get('session');
    }

    private function updater(): Updater
    {
        return $this->container->get('updater');
    }

    private function ownerOnly(): ?Response
    {
        if (($this->session()->user()['role'] ?? '') !== 'owner') {
            return Response::forbidden('آپدیت سیستم فقط برای مالک است');
        }
        return null;
    }

    private function page(array $data): Response
    {
        $session = $this->session();
        $content = View::render('update', 'admin', 'update', $data + [
            'csrf' => $session->csrfToken(),
        ]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => 'آپدیت سیستم',
            'content' => $content,
            'menu'    => $this->container->get('loader')->adminMenu(),
            'user'    => $session->user(),
            'csrf'    => $session->csrfToken(),
        ]));
    }

    public function index(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $updater = $this->updater();
        return $this->page([
            'preflight' => $updater->preflight(),
            'check'     => $updater->check(),
            'result'    => null,
            'runError'  => null,
        ]);
    }

    public function run(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $updater = $this->updater();
        $result = null;
        $error = null;
        $actor = (string) ($this->session()->user()['username'] ?? '');
        try {
            $result = $updater->apply();
            if ($this->container->has('logger')) {
                $this->container->get('logger')->user($actor, 'نصب آپدیت', $result['from'] . ' ← ' . $result['to']);
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            if ($this->container->has('logger')) {
                $this->container->get('logger')->system('خطای آپدیت', $error, $actor);
            }
        }
        return $this->page([
            'preflight' => $updater->preflight(),
            'check'     => $updater->check(),
            'result'    => $result,
            'runError'  => $error,
        ]);
    }
}
