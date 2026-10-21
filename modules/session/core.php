<?php
declare(strict_types=1);

use Core\Container;
use Core\Request;
use Core\Response;
use Core\Router;
use Core\View;
use Modules\Session\Auth;
use Modules\Session\SessionManager;

/*
 * ماژول session — سشن امن، ورود مدیر و مدیریت دسترسی‌های مدیریت.
 * گارد امنیتی با هوک before_dispatch در هسته اجرا می‌شود،
 * بدون اینکه هسته چیزی از این ماژول بداند.
 */

return [
    'slug'        => 'session',
    'title'       => 'سشن امن و دسترسی',
    'description' => 'سشن دیتابیسی با کوکی امضاشده، ورود مدیر، کاربران و نقش‌های مدیریت',
    'version'     => '0.1.0',
    'system'      => true,
    'requires'    => ['sqlite'],

    'permissions' => [
        'users.manage' => 'مدیریت کاربران و دسترسی‌های مدیریت',
    ],

    'admin_menu' => [
        ['label' => 'کاربران و دسترسی', 'path' => '/admin/users', 'permission' => 'users.manage'],
    ],

    'services' => [
        'session' => static fn (Container $c): SessionManager =>
            new SessionManager($c->get('db'), (string) $c->get('app.key')),
        'auth' => static fn (Container $c): Auth => new Auth($c->get('db')),
    ],

    'migrate' => static function (Container $c): void {
        $c->get('auth')->migrate();
    },

    'hooks' => [
        'before_dispatch' => static function (Request $request, Container $c): ?Response {
            /** @var SessionManager $session */
            $session = $c->get('session');
            $session->start($request);
            $path = $request->path;

            if (!str_starts_with($path, '/admin')) {
                return null;
            }

            // گارد ورود: همه مسیرهای مدیریت به‌جز صفحه ورود
            if ($path !== '/admin/login' && $session->user() === null) {
                return Response::redirect('/admin/login');
            }

            // مدیریت کاربران فقط برای مالک
            if ($path === '/admin/users' && ($session->user()['role'] ?? '') !== 'owner') {
                return Response::forbidden('مدیریت کاربران فقط برای مالک است');
            }

            // CSRF روی همه POSTهای مدیریت (ازجمله ورود)
            if ($request->method === 'POST' && !$session->validCsrf($request->input('_csrf'))) {
                return Response::forbidden('توکن امنیتی نامعتبر است');
            }

            return null;
        },
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/login', static function (Request $request) use ($c): Response {
            /** @var SessionManager $session */
            $session = $c->get('session');
            if ($session->user() !== null) {
                return Response::redirect('/admin');
            }
            return Response::html(View::render('session', 'admin', 'login', [
                'csrf'  => $session->csrfToken(),
                'error' => $request->input('err') === '1',
            ]));
        }, $module);

        $router->post('/admin/login', static function (Request $request) use ($c): Response {
            /** @var Auth $auth */
            $auth = $c->get('auth');
            $user = $auth->attempt((string) $request->input('username', ''), (string) $request->input('password', ''));
            if ($user === null) {
                return Response::redirect('/admin/login?err=1');
            }
            $c->get('session')->login($user);
            return Response::redirect('/admin');
        }, $module);

        $router->post('/admin/logout', static function () use ($c): Response {
            $c->get('session')->logout();
            return Response::redirect('/admin/login');
        }, $module);

        $router->get('/admin/users', static function (Request $request) use ($c): Response {
            $content = View::render('session', 'admin', 'users', [
                'users'       => $c->get('auth')->users(),
                'permissions' => $c->get('loader')->permissions(),
                'csrf'        => $c->get('session')->csrfToken(),
                'error'       => $request->input('err') === '1',
            ]);
            return Response::html(View::render('admin', 'admin', 'layout', [
                'title'   => 'کاربران و دسترسی',
                'content' => $content,
                'menu'    => $c->get('loader')->adminMenu(),
                'user'    => $c->get('session')->user(),
                'csrf'    => $c->get('session')->csrfToken(),
            ]));
        }, $module);

        $router->post('/admin/users', static function (Request $request) use ($c): Response {
            $ok = $c->get('auth')->create(
                trim((string) $request->input('username', '')),
                (string) $request->input('password', ''),
                (string) $request->input('role', 'admin'),
            );
            return Response::redirect('/admin/users' . ($ok ? '' : '?err=1'));
        }, $module);
    },
];
