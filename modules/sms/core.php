<?php

declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Sms\AdminSmsController;
use Modules\Sms\SmsClient;
use Modules\Sms\SmsSender;
use Modules\Sms\SmsSettings;

/*
 * ماژول sms — پنل پیامکی SMS.ir برای وصله:
 * کلاینت بومی (بدون Composer) با همان API پکیج smsir-php؛ تنظیمات کلید/خط/قالب،
 * کد یک‌بارمصرف ورود مشتری فروشگاه، پیامک ثبت و تغییر وضعیت سفارش، گزارش ارسال.
 * به فروشگاه نیاز دارد (ورود OTP و اطلاع‌رسانی سفارش).
 */

return [
    'slug'        => 'sms',
    'title'       => 'پیامک',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
    'description' => 'پنل پیامکی SMS.ir: کد تأیید ورود با موبایل و اطلاع‌رسانی پیامکی سفارش‌ها',
    'version'     => '1.0.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session', 'shop'],

    'services' => [
        'sms.settings' => static fn (Container $c): SmsSettings => new SmsSettings($c->get('db')),
        'sms.client' => static fn (Container $c): SmsClient => new SmsClient($c->get('sms.settings')->apiKey()),
        'sms.sender' => static fn (Container $c): SmsSender => new SmsSender($c->get('db'), $c->get('sms.settings'), $c->get('sms.client')),
    ],

    'permissions' => [
        'sms.manage' => 'مدیریت پیامک',
    ],

    'migrate' => static function (Container $c): void {
        $c->get('sms.settings')->migrate();
        $c->get('sms.sender')->migrate();
    },

    'admin_menu' => [
        ['label' => 'تنظیمات پیامک', 'path' => '/admin/sms', 'permission' => 'sms.manage'],
        ['label' => 'گزارش ارسال', 'path' => '/admin/sms/log', 'permission' => 'sms.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/sms', [AdminSmsController::class, 'settings'], $module);
        $router->post('/admin/sms/settings', [AdminSmsController::class, 'settingsSave'], $module);
        $router->get('/admin/sms/log', [AdminSmsController::class, 'logPage'], $module);
    },
];
