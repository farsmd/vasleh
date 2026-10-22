<?php
declare(strict_types=1);

use Core\Container;
use Core\Request;
use Core\Response;
use Core\Router;
use Modules\Account\AccountController;

/*
 * ماژول account — بخش کاربری:
 * پروفایل کاربر واردشده و تغییر کلمه عبور.
 * گارد ورود و CSRF با هوک خود ماژول اعمال می‌شود
 * (سشن را از سرویس ماژول session می‌گیرد؛ هسته چیزی نمی‌داند).
 */

return [
    'slug'        => 'account',
    'title'       => 'حساب کاربری',
    'description' => 'بخش کاربری: پروفایل و تغییر کلمه عبور',
    'version'     => '0.1.0',
    'system'      => false,
    'requires'    => ['session'],

    'permissions' => [
        'account.access' => 'دسترسی به بخش کاربری',
    ],

    'admin_menu' => [
        ['label' => 'حساب کاربری', 'path' => '/account', 'permission' => 'account.access'],
    ],

    'hooks' => [
        'before_dispatch' => static function (Request $request, Container $c): ?Response {
            if (!str_starts_with($request->path, '/account')) {
                return null;
            }
            /** @var \Modules\Session\SessionManager $session */
            $session = $c->get('session');
            $session->start($request);

            if ($session->user() === null) {
                return Response::redirect('/admin/login');
            }
            if ($request->method === 'POST' && !$session->validCsrf($request->input('_csrf'))) {
                return Response::forbidden('توکن امنیتی نامعتبر است');
            }
            return null;
        },
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/account', [AccountController::class, 'profile'], $module);
        $router->get('/account/password', [AccountController::class, 'passwordForm'], $module);
        $router->post('/account/password', [AccountController::class, 'passwordUpdate'], $module);
    },
];
