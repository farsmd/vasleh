<?php
declare(strict_types=1);

use Core\Container;
use Core\Response;
use Core\Router;
use Core\View;
use Modules\Sqlite\Database;

/*
 * ماژول sqlite — مدیریت دیتابیس.
 * سرویس db را برای بقیه ماژول‌ها ثبت می‌کند و در مدیریت،
 * صفحه وضعیت دیتابیس دارد. فرانت عمومی ندارد.
 */

return [
    'slug'        => 'sqlite',
    'title'       => 'دیتابیس SQLite',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>',
    'description' => 'مدیریت دیتابیس SQLite و ارائه سرویس db به ماژول‌ها',
    'version'     => '0.1.0',
    'system'      => true,

    'permissions' => [
        'db.view' => 'مشاهده وضعیت دیتابیس',
    ],

    'admin_menu' => [
        ['label' => 'دیتابیس', 'path' => '/admin/database', 'permission' => 'db.view'],
    ],

    'services' => [
        'db' => static fn (Container $c): Database =>
            new Database($c->get('base_path') . '/storage/database.sqlite'),
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/database', static function () use ($c): Response {
            /** @var Database $db */
            $db = $c->get('db');
            $content = View::render('sqlite', 'admin', 'status', ['info' => $db->info()]);
            return Response::html(View::render('admin', 'admin', 'layout', [
                'title'   => 'وضعیت دیتابیس',
                'content' => $content,
                'menu'    => $c->get('loader')->adminMenu(),
                'user'    => $c->has('session') ? $c->get('session')->user() : null,
                'csrf'    => $c->has('session') ? $c->get('session')->csrfToken() : '',
            ]));
        }, $module);
    },
];
