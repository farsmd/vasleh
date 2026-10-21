<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Admin\AdminController;
use Modules\Admin\ModulesController;

/*
 * ماژول admin — خود پنل مدیریت هم یک ماژول است:
 * داشبورد + مدیریت ماژول‌ها (فهرست، ساخت، ویرایش، فعال/غیرفعال).
 * پوسته مدیریت (لی‌اوت و منو) را این ماژول ارائه می‌کند
 * و منو از admin_menu همه ماژول‌های فعال جمع می‌شود.
 */

return [
    'slug'        => 'admin',
    'title'       => 'مدیریت',
    'description' => 'پنل مدیریت و مدیریت ماژول‌ها',
    'version'     => '0.1.0',
    'system'      => true,

    'permissions' => [
        'admin.access'   => 'ورود به پنل مدیریت',
        'modules.manage' => 'مدیریت ماژول‌ها (ساخت، ویرایش، فعال/غیرفعال)',
    ],

    'admin_menu' => [
        ['label' => 'داشبورد', 'path' => '/admin', 'permission' => 'admin.access'],
        ['label' => 'مدیریت ماژول‌ها', 'path' => '/admin/modules', 'permission' => 'modules.manage'],
        ['label' => 'ساخت ماژول', 'path' => '/admin/modules/create', 'permission' => 'modules.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin', [AdminController::class, 'index'], $module);
        $router->get('/admin/modules', [ModulesController::class, 'index'], $module);
        $router->get('/admin/modules/create', [ModulesController::class, 'create'], $module);
        $router->post('/admin/modules/create', [ModulesController::class, 'store'], $module);
        $router->get('/admin/modules/edit/{slug}', [ModulesController::class, 'edit'], $module);
        $router->post('/admin/modules/edit/{slug}', [ModulesController::class, 'update'], $module);
        $router->post('/admin/modules/toggle/{slug}', [ModulesController::class, 'toggle'], $module);
    },
];
