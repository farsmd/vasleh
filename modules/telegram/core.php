<?php

declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Telegram\AdminTelegramController;
use Modules\Telegram\TelegramClient;
use Modules\Telegram\TelegramSettings;
use Modules\Telegram\TgController;

/*
 * ماژول telegram — ربات و مینی‌اپ تلگرام فروشگاه:
 * پنل /admin/telegram (توکن ربات، نام کاربری، متن خوش‌آمد، فعال‌سازی وبهوک)،
 * وبهوک /tg/webhook (پاسخ /start با دکمهٔ باز کردن فروشگاه) و مینی‌اپ /tgapp
 * (فهرست، جستجو، سبد، ثبت سفارش از همان مسیر فروشگاه + زیبال).
 * توکن و راز وبهوک در دیتابیس‌اند و هرگز نمایش داده نمی‌شوند.
 */

return [
    'slug'        => 'telegram',
    'title'       => 'تلگرام',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>',
    'description' => 'ربات و مینی‌اپ تلگرام فروشگاه: سفارش از داخل تلگرام با همان مسیر فروشگاه و پیامک',
    'version'     => '1.0.1',
    'system'      => false,
    'requires'    => ['sqlite', 'session', 'shop'],

    'services' => [
        'telegram.settings' => static fn (Container $c): TelegramSettings => new TelegramSettings($c->get('db')),
        'telegram.client' => static fn (Container $c): TelegramClient => TelegramClient::fromEnv($c->get('telegram.settings')->token()),
    ],

    'permissions' => [
        'telegram.manage' => 'مدیریت تلگرام',
    ],

    'migrate' => static function (Container $c): void {
        $c->get('telegram.settings')->migrate();
    },

    'admin_menu' => [
        ['label' => 'تلگرام', 'path' => '/admin/telegram', 'permission' => 'telegram.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/telegram', [AdminTelegramController::class, 'index'], $module);
        $router->post('/admin/telegram/settings', [AdminTelegramController::class, 'settingsSave'], $module);
        $router->post('/admin/telegram/webhook', [AdminTelegramController::class, 'webhookActivate'], $module);
        $router->post('/tg/webhook', [TgController::class, 'webhook'], $module);
        $router->get('/tgapp', [TgController::class, 'miniapp'], $module);
        $router->post('/tgapp/order', [TgController::class, 'order'], $module);
    },
];
