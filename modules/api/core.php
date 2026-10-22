<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Api\AdminApiController;
use Modules\Api\ApiTokens;
use Modules\Api\RemoteController;

/*
 * ماژول api — دسترسی راه‌دور برنامه‌ای (برای ابزارهای بیرونی و خودکارسازی):
 * با توکن Bearer جدا از سشن: فهرست/خواندن/نوشتن/حذف فایل‌ها در محدوده ریشه برنامه
 * و کوئری روی دیتابیس. ساختن و لغو توکن فقط از پنل و فقط برای مالک.
 * فایل‌های حیاتی (کلید برنامه و خود فایل دیتابیس) از راه فایل‌ها بسته‌اند؛
 * دیتابیس فقط از راه endpoint کوئری در دسترس است.
 * این ماژول سیستم نیست: غیرفعال‌کردنش از مدیریت ماژول‌ها یعنی بستن کامل API.
 */

return [
    'slug'        => 'api',
    'title'       => 'دسترسی API',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>',
    'description' => 'API توکن‌محور برای فایل‌ها و دیتابیس (دسترسی راه‌دور و خودکارسازی)',
    'version'     => '0.1.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'permissions' => [
        'api.manage' => 'مدیریت توکن‌های API',
    ],

    'admin_menu' => [
        ['label' => 'دسترسی API', 'path' => '/admin/api', 'permission' => 'api.manage'],
    ],

    'services' => [
        'api.tokens' => static fn (Container $c): ApiTokens => new ApiTokens($c->get('db')),
    ],

    'migrate' => static function (Container $c): void {
        $c->get('api.tokens')->migrate();
    },

    'routes' => static function (Router $router, Container $c, string $module): void {
        // پنل مدیریت توکن‌ها
        $router->get('/admin/api', [AdminApiController::class, 'index'], $module);
        $router->post('/admin/api/tokens', [AdminApiController::class, 'store'], $module);
        $router->post('/admin/api/tokens/revoke/{id}', [AdminApiController::class, 'revoke'], $module);

        // خود API — احراز با توکن، بدون سشن و بدون CSRF
        $router->get('/api/ping', [RemoteController::class, 'ping'], $module);
        $router->get('/api/files', [RemoteController::class, 'files'], $module);
        $router->get('/api/file', [RemoteController::class, 'fileRead'], $module);
        $router->post('/api/file', [RemoteController::class, 'fileWrite'], $module);
        $router->add('PUT', '/api/file', [RemoteController::class, 'fileWrite'], $module);
        $router->add('DELETE', '/api/file', [RemoteController::class, 'fileDelete'], $module);
        $router->get('/api/db/tables', [RemoteController::class, 'dbTables'], $module);
        $router->post('/api/db/query', [RemoteController::class, 'dbQuery'], $module);
    },
];
