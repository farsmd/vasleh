<?php

declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Zibal\AdminZibalController;
use Modules\Zibal\ZibalClient;
use Modules\Zibal\ZibalController;
use Modules\Zibal\ZibalPayments;

/*
 * ماژول zibal — درگاه پرداخت زیبال برای فروشگاه:
 * تنظیمات مرچنت (تست/واقعی)، ثبت پرداخت هر سفارش، هدایت به درگاه و بازگشت
 * با تأیید سروری (verify) + تطبیق مبلغ. به ماژول فروشگاه نیاز دارد.
 */

return [
    'slug'        => 'zibal',
    'title'       => 'درگاه زیبال',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>',
    'description' => 'درگاه پرداخت زیبال: اتصال تسویه فروشگاه به درگاه، تأیید سروری پرداخت و ثبت مرجع',
    'version'     => '1.0.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session', 'shop'],

    'services' => [
        'zibal.payments' => static fn (Container $c): ZibalPayments => new ZibalPayments($c->get('db')),
        'zibal.client' => static fn (Container $c): ZibalClient => new ZibalClient(),
    ],

    'permissions' => [
        'zibal.manage' => 'مدیریت درگاه زیبال',
    ],

    'migrate' => static function (Container $c): void {
        $c->get('zibal.payments')->migrate();
    },

    'admin_menu' => [
        ['label' => 'تنظیمات زیبال', 'path' => '/admin/zibal', 'permission' => 'zibal.manage'],
        ['label' => 'پرداخت‌های زیبال', 'path' => '/admin/zibal/payments', 'permission' => 'zibal.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/zibal/callback', [ZibalController::class, 'callback'], $module);
        $router->get('/admin/zibal', [AdminZibalController::class, 'settings'], $module);
        $router->post('/admin/zibal/settings', [AdminZibalController::class, 'settingsSave'], $module);
        $router->get('/admin/zibal/payments', [AdminZibalController::class, 'paymentsPage'], $module);
    },
];
