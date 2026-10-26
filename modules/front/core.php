<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Front\FrontController;
use Modules\Front\Landing;

/*
 * ماژول front — فرانت‌اند سایت هم یک ماژول است:
 * لندینگ فروش وصله (صفحه اصلی)، فرم سرنخ درخواست تست و مدیریت آن‌ها.
 */

return [
    'slug'        => 'front',
    'title'       => 'فرانت‌اند',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    'description' => 'نمای عمومی سایت',
    'version'     => '0.5.0',
    'system'      => true,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'front.landing' => static fn(Container $c): Landing => new Landing($c->get('db')),
    ],

    'migrate' => static function (Container $c): void {
        $c->get('front.landing')->migrate();
    },

    'permissions' => [
        'leads.view' => 'مشاهده درخواست‌های آزمایشی',
        'landing.manage' => 'مدیریت قیمت‌های لندینگ',
    ],

    'admin_menu' => [
        ['label' => 'درخواست‌های آزمایشی', 'path' => '/admin/leads', 'permission' => 'leads.view'],
        ['label' => 'فیلدهای لندینگ', 'path' => '/admin/landing/fields', 'permission' => 'landing.manage'],
        ['label' => 'فایل‌های HTML لندینگ', 'path' => '/admin/landing/html', 'permission' => 'landing.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/', [FrontController::class, 'home'], $module);
        $router->post('/lead', [FrontController::class, 'leadSubmit'], $module);
        $router->get('/admin/leads', [FrontController::class, 'leadsAdmin'], $module);
        $router->post('/admin/leads/{id}/delete', [FrontController::class, 'leadDelete'], $module);
        $router->get('/admin/landing/fields', [FrontController::class, 'fieldsAdmin'], $module);
        $router->post('/admin/landing/fields/save', [FrontController::class, 'fieldSave'], $module);
        $router->post('/admin/landing/fields/{id}/delete', [FrontController::class, 'fieldDelete'], $module);
        $router->post('/admin/landing/fields/prices', [FrontController::class, 'pricesSave'], $module);
        $router->get('/admin/landing/html', [FrontController::class, 'htmlAdmin'], $module);
        $router->post('/admin/landing/html/save', [FrontController::class, 'htmlSave'], $module);
        $router->post('/admin/landing/html/{id}/delete', [FrontController::class, 'htmlDelete'], $module);
        $router->post('/admin/landing/html/{id}/default', [FrontController::class, 'htmlSetDefault'], $module);
    },
];
