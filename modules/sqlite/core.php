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
