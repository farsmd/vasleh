<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Production\AdminProductionController;
use Modules\Production\Production;

/*
 * ماژول production — ساخت و تولید:
 * فرمول ساخت (BOM) روی کالاهای انبار، دستور تولید با بچ، کسر خودکار مواد
 * هنگام شروع، ورود خروجی هنگام اتمام و برگشت مواد هنگام لغو.
 * به ماژول انبارداری نیاز دارد؛ اتصال خروجی به محصول فروشگاه اختیاری است.
 */

return [
    'slug'        => 'production',
    'title'       => 'ساخت و تولید',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20h20"/><path d="M4 20V9l5 3V9l5 3V4h6v16z"/><path d="M17 8h.01"/></svg>',
    'description' => 'ساخت و تولید: فرمول ساخت (BOM)، دستور تولید، کسر مواد از انبار و ورود محصول ساخته‌شده',
    'version'     => '1.0.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session', 'inventory'],

    'services' => [
        'production.manager' => static fn (Container $c): Production => new Production($c->get('db'), $c->get('inventory.stock')),
    ],

    'permissions' => [
        'production.manage' => 'مدیریت تولید',
    ],

    'migrate' => static function (Container $c): void {
        $c->get('production.manager')->migrate();
    },

    'admin_menu' => [
        ['label' => 'دستورهای تولید', 'path' => '/admin/production/orders', 'permission' => 'production.manage'],
        ['label' => 'دستور تازه', 'path' => '/admin/production/orders/create', 'permission' => 'production.manage'],
        ['label' => 'فرمول‌های ساخت', 'path' => '/admin/production/recipes', 'permission' => 'production.manage'],
        ['label' => 'فرمول تازه', 'path' => '/admin/production/recipes/create', 'permission' => 'production.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/production/recipes', [AdminProductionController::class, 'recipes'], $module);
        $router->get('/admin/production/recipes/create', [AdminProductionController::class, 'recipeCreateForm'], $module);
        $router->post('/admin/production/recipes/create', [AdminProductionController::class, 'recipeCreate'], $module);
        $router->get('/admin/production/recipes/edit/{id}', [AdminProductionController::class, 'recipeEditForm'], $module);
        $router->post('/admin/production/recipes/edit/{id}', [AdminProductionController::class, 'recipeUpdate'], $module);
        $router->post('/admin/production/recipes/delete/{id}', [AdminProductionController::class, 'recipeDelete'], $module);

        $router->get('/admin/production/orders', [AdminProductionController::class, 'orders'], $module);
        $router->get('/admin/production/orders/create', [AdminProductionController::class, 'orderCreateForm'], $module);
        $router->post('/admin/production/orders/create', [AdminProductionController::class, 'orderCreate'], $module);
        $router->get('/admin/production/orders/{id}', [AdminProductionController::class, 'orderDetail'], $module);
        $router->post('/admin/production/orders/{id}/start', [AdminProductionController::class, 'orderStart'], $module);
        $router->post('/admin/production/orders/{id}/complete', [AdminProductionController::class, 'orderComplete'], $module);
        $router->post('/admin/production/orders/{id}/cancel', [AdminProductionController::class, 'orderCancel'], $module);
    },
];
