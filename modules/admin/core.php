<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Admin\AdminController;
use Modules\Admin\MenuOrderController;
use Modules\Admin\ModulesController;
use Modules\Admin\ThemeController;

/*
 * ماژول admin — خود پنل مدیریت هم یک ماژول است:
 * داشبورد + مدیریت ماژول‌ها (فهرست، ساخت، ویرایش، فعال/غیرفعال).
 * پوسته مدیریت (لی‌اوت و منو) را این ماژول ارائه می‌کند
 * و منو از admin_menu همه ماژول‌های فعال جمع می‌شود.
 */

return [
    'slug'        => 'admin',
    'title'       => 'مدیریت',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>',
    'description' => 'پنل مدیریت و مدیریت ماژول‌ها',
    'version'     => '0.8.0',
    'system'      => true,

    'permissions' => [
        'admin.access'   => 'ورود به پنل مدیریت',
        'modules.manage' => 'مدیریت ماژول‌ها (ساخت، ویرایش، فعال/غیرفعال)',
    ],

    'admin_menu' => [
        ['label' => 'داشبورد', 'path' => '/admin', 'permission' => 'admin.access'],
        ['label' => 'مدیریت ماژول‌ها', 'path' => '/admin/modules', 'permission' => 'modules.manage'],
        ['label' => 'ترتیب منو', 'path' => '/admin/menu', 'permission' => 'modules.manage'],
        ['label' => 'قالب ادمین', 'path' => '/admin/theme', 'permission' => 'modules.manage'],
        ['label' => 'ساخت ماژول', 'path' => '/admin/modules/create', 'permission' => 'modules.manage'],
        ['label' => 'نصب ماژول از ZIP', 'path' => '/admin/modules/install', 'permission' => 'modules.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin', [AdminController::class, 'index'], $module);
        $router->get('/admin/menu', [MenuOrderController::class, 'index'], $module);
        $router->post('/admin/menu/move', [MenuOrderController::class, 'move'], $module);
        $router->post('/admin/menu/save', [MenuOrderController::class, 'save'], $module);
        $router->get('/admin/theme', [ThemeController::class, 'index'], $module);
        $router->post('/admin/theme', [ThemeController::class, 'save'], $module);
        $router->get('/admin/modules', [ModulesController::class, 'index'], $module);
        $router->get('/admin/modules/create', [ModulesController::class, 'create'], $module);
        $router->post('/admin/modules/create', [ModulesController::class, 'store'], $module);
        $router->get('/admin/modules/install', [ModulesController::class, 'installForm'], $module);
        $router->post('/admin/modules/install', [ModulesController::class, 'install'], $module);
        $router->get('/admin/modules/file/{slug}', [ModulesController::class, 'fileEdit'], $module);
        $router->post('/admin/modules/file/{slug}', [ModulesController::class, 'fileSave'], $module);
        $router->get('/admin/modules/edit/{slug}', [ModulesController::class, 'edit'], $module);
        $router->post('/admin/modules/edit/{slug}', [ModulesController::class, 'update'], $module);
        $router->post('/admin/modules/toggle/{slug}', [ModulesController::class, 'toggle'], $module);
        $router->post('/admin/modules/delete/{slug}', [ModulesController::class, 'delete'], $module);
    },
];
