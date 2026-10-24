<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Priceextract\AdminPriceController;
use Modules\Priceextract\Extractor;

/*
 * ماژول priceextract — استخراج قیمت:
 * منابع قیمت (URL + آداپتور JSON یا جدول HTML + نگاشت فیلدها)،
 * اجرای استخراج با ذخیره آیتم‌ها (نام/کد/قیمت/دسته) و سوابق اجراها.
 */

return [
    'slug'        => 'priceextract',
    'title'       => 'استخراج قیمت',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>',
    'description' => 'استخراج قیمت: دریافت فهرست قیمت از یک آدرس (JSON API یا جدول HTML) و نمایش لیست قیمت‌ها',
    'version'     => '1.0.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'priceextract.extractor' => static fn (Container $c): Extractor => new Extractor($c->get('db')),
    ],

    'permissions' => [
        'priceextract.manage' => 'مدیریت استخراج قیمت',
    ],

    'migrate' => static function (Container $c): void {
        $c->get('priceextract.extractor')->migrate();
    },

    'admin_menu' => [
        ['label' => 'منابع قیمت', 'path' => '/admin/priceextract', 'permission' => 'priceextract.manage'],
        ['label' => 'فهرست قیمت‌ها', 'path' => '/admin/priceextract/items', 'permission' => 'priceextract.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/priceextract', [AdminPriceController::class, 'sources'], $module);
        $router->post('/admin/priceextract/sources/save', [AdminPriceController::class, 'sourceSave'], $module);
        $router->post('/admin/priceextract/sources/delete/{id}', [AdminPriceController::class, 'sourceDelete'], $module);
        $router->post('/admin/priceextract/sources/run/{id}', [AdminPriceController::class, 'sourceRun'], $module);
        $router->post('/admin/priceextract/import', [AdminPriceController::class, 'importCsv'], $module);
        $router->get('/admin/priceextract/sources/runs/{id}', [AdminPriceController::class, 'runs'], $module);
        $router->get('/admin/priceextract/items', [AdminPriceController::class, 'items'], $module);
    },
];
