<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Update\UpdateController;
use Modules\Update\Updater;

/*
 * ماژول update — آپدیت از گیت‌هاب:
 * نسخه سه‌بخشی برنامه (هسته . ماژول‌های سیستمی . بقیه) در فایل version.json
 * ریشه نگه داشته می‌شود. این ماژول نسخه روی گیت‌هاب را می‌خواند، اگر تازه‌تر
 * بود با یک کلیک دانلود و نصب می‌کند — بدون دست‌زدن به storage
 * (دیتابیس، کلید، بکاپ‌ها) و با بکاپ خودکار قبل از نصب.
 * ماژول سیستمی است تا همیشه در دسترس بماند.
 */

return [
    'slug'        => 'update',
    'title'       => 'آپدیت سیستم',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>',
    'description' => 'بررسی و نصب نسخه تازه از گیت‌هاب با نسخه‌بندی سه‌بخشی (هسته/سیستمی/بقیه)',
    'version'     => '0.1.0',
    'system'      => true,
    'requires'    => ['session'],

    'permissions' => [
        'update.manage' => 'بررسی و نصب آپدیت از گیت‌هاب',
    ],

    'admin_menu' => [
        ['label' => 'آپدیت', 'path' => '/admin/update', 'permission' => 'update.manage'],
    ],

    'services' => [
        'updater' => static fn (Container $c): Updater => new Updater((string) $c->get('base_path')),
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/update', [UpdateController::class, 'index'], $module);
        $router->post('/admin/update/run', [UpdateController::class, 'run'], $module);
    },
];
