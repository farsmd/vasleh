<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Inventory\AdminInventoryController;
use Modules\Inventory\Stock;

/*
 * ماژول inventory — انبارداری:
 * چند انبار، کالاها (ماده اولیه/محصول) با واحد و حد هشدار کمبود،
 * موجودی per-warehouse، اسناد ورود/خروج/اصلاح، انتقال بین انبارها و کاردکس.
 * سرویس inventory.stock برای ماژول‌های دیگر (مثلاً تولید) هم قابل استفاده است.
 */

return [
    'slug'        => 'inventory',
    'title'       => 'انبارداری',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8l-9-5-9 5v8l9 5 9-5z"/><path d="M3 8l9 5 9-5"/><path d="M12 13v8"/><path d="M7.5 5.5l9 5"/></svg>',
    'description' => 'انبارداری: انبارها، کالاها، موجودی، ورود و خروج، انتقال بین انبارها، کاردکس و هشدار کمبود',
    'version'     => '1.0.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'inventory.stock' => static fn (Container $c): Stock => new Stock($c->get('db')),
    ],

    'permissions' => [
        'inventory.manage' => 'مدیریت انبار',
    ],

    'migrate' => static function (Container $c): void {
        $stock = $c->get('inventory.stock');
        $stock->migrate();
        $stock->seedIfEmpty();
    },

    'admin_menu' => [
        ['label' => 'موجودی انبار', 'path' => '/admin/inventory', 'permission' => 'inventory.manage'],
        ['label' => 'کالاهای انبار', 'path' => '/admin/inventory/items', 'permission' => 'inventory.manage'],
        ['label' => 'ثبت گردش', 'path' => '/admin/inventory/move', 'permission' => 'inventory.manage'],
        ['label' => 'کاردکس', 'path' => '/admin/inventory/kardex', 'permission' => 'inventory.manage'],
        ['label' => 'انبارها', 'path' => '/admin/inventory/warehouses', 'permission' => 'inventory.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/inventory', [AdminInventoryController::class, 'dashboard'], $module);
        $router->get('/admin/inventory/items', [AdminInventoryController::class, 'items'], $module);
        $router->post('/admin/inventory/items/save', [AdminInventoryController::class, 'itemSave'], $module);
        $router->post('/admin/inventory/items/delete/{id}', [AdminInventoryController::class, 'itemDelete'], $module);
        $router->get('/admin/inventory/warehouses', [AdminInventoryController::class, 'warehouses'], $module);
        $router->post('/admin/inventory/warehouses/save', [AdminInventoryController::class, 'warehouseSave'], $module);
        $router->post('/admin/inventory/warehouses/delete/{id}', [AdminInventoryController::class, 'warehouseDelete'], $module);
        $router->get('/admin/inventory/move', [AdminInventoryController::class, 'moveForm'], $module);
        $router->post('/admin/inventory/move', [AdminInventoryController::class, 'moveSave'], $module);
        $router->post('/admin/inventory/transfer', [AdminInventoryController::class, 'transferSave'], $module);
        $router->get('/admin/inventory/kardex', [AdminInventoryController::class, 'kardex'], $module);
    },
];
