<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Shop\AdminOrdersController;
use Modules\Shop\AdminShopController;
use Modules\Shop\Cart;
use Modules\Shop\Catalog;
use Modules\Shop\Coupons;
use Modules\Shop\Customers;
use Modules\Shop\Orders;
use Modules\Shop\ShopTemplates;
use Modules\Shop\ShopController;
use Modules\Shop\ShopSettings;
use Modules\Shop\Wishlist;

/*
 * ماژول shop — فروشگاه کامل:
 * محصولات با فیلدهای سفارشی و واریانت (ویژگی‌ها)، دسته‌بندی، سبد خرید،
 * لیست قیمت، ثبت سفارش + کد پیگیری، فاکتور فروش و تنظیمات (ارسال/مالیات).
 */

return [
    'slug'        => 'shop',
    'title'       => 'فروشگاه',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1.5-5h15L21 9"/><path d="M4 9h16v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/><path d="M9 21V13h6v8"/></svg>',
    'description' => 'فروشگاه کامل: محصولات، فیلدهای سفارشی، ویژگی و واریانت، دسته‌بندی، سبد خرید، لیست قیمت، سفارش و پیگیری، فاکتور فروش',
    'version'     => '1.3.2',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'shop.catalog'  => static fn (Container $c): Catalog => new Catalog($c->get('db')),
        'shop.orders'   => static fn (Container $c): Orders => new Orders($c->get('db')),
        'shop.settings' => static fn (Container $c): ShopSettings => new ShopSettings($c->get('db')),
        'shop.cart'     => static fn (Container $c): Cart => new Cart($c->get('session')),
        'shop.coupons'  => static fn (Container $c): Coupons => new Coupons($c->get('db')),
        'shop.customers' => static fn (Container $c): Customers => new Customers($c->get('db')),
        'shop.templates' => static fn (Container $c): ShopTemplates => new ShopTemplates($c->get('db')),
        'shop.wishlist' => static fn (Container $c): Wishlist => new Wishlist($c->get('session')),
    ],

    'permissions' => [
        'shop.manage' => 'مدیریت فروشگاه',
    ],

    'migrate' => static function (Container $c): void {
        $settings = $c->get('shop.settings');
        $settings->migrate();
        $settings->seedDefaults();
        $catalog = $c->get('shop.catalog');
        $catalog->migrate();
        $catalog->seedIfEmpty();
        $c->get('shop.orders')->migrate();
        $c->get('shop.coupons')->migrate();
        $c->get('shop.customers')->migrate();
        $c->get('shop.templates')->migrate();
    },

    'admin_menu' => [
        ['label' => 'محصولات', 'path' => '/admin/shop/products', 'permission' => 'shop.manage'],
        ['label' => 'محصول تازه', 'path' => '/admin/shop/products/create', 'permission' => 'shop.manage'],
        ['label' => 'دسته‌بندی‌ها', 'path' => '/admin/shop/categories', 'permission' => 'shop.manage'],
        ['label' => 'فیلدهای محصول', 'path' => '/admin/shop/fields', 'permission' => 'shop.manage'],
        ['label' => 'ویژگی‌ها و واریانت', 'path' => '/admin/shop/attributes', 'permission' => 'shop.manage'],
        ['label' => 'سفارش‌ها', 'path' => '/admin/shop/orders', 'permission' => 'shop.manage'],
        ['label' => 'کوپن‌ها', 'path' => '/admin/shop/coupons', 'permission' => 'shop.manage'],
        ['label' => 'قالب‌های فروشگاه', 'path' => '/admin/shop/templates', 'permission' => 'shop.manage'],
        ['label' => 'لیست قیمت', 'path' => '/admin/shop/pricelist', 'permission' => 'shop.manage'],
        ['label' => 'تنظیمات فروشگاه', 'path' => '/admin/shop/settings', 'permission' => 'shop.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        // فرانت فروشگاه
        $router->get('/shop', [ShopController::class, 'index'], $module);
        $router->get('/shop/product/{slug}', [ShopController::class, 'product'], $module);
        $router->get('/shop/cart', [ShopController::class, 'cartShow'], $module);
        $router->post('/shop/cart/add', [ShopController::class, 'cartAdd'], $module);
        $router->post('/shop/cart/update', [ShopController::class, 'cartUpdate'], $module);
        $router->post('/shop/cart/remove', [ShopController::class, 'cartRemove'], $module);
        $router->get('/shop/checkout', [ShopController::class, 'checkoutForm'], $module);
        $router->post('/shop/checkout', [ShopController::class, 'checkoutPlace'], $module);
        $router->get('/shop/search', [ShopController::class, 'search'], $module);
        $router->get('/shop/wishlist', [ShopController::class, 'wishlistShow'], $module);
        $router->post('/shop/wishlist/toggle', [ShopController::class, 'wishlistToggle'], $module);
        $router->post('/shop/coupon/apply', [ShopController::class, 'couponApply'], $module);
        $router->post('/shop/coupon/remove', [ShopController::class, 'couponRemove'], $module);
        $router->get('/shop/success/{code}', [ShopController::class, 'success'], $module);
        $router->get('/shop/account', [ShopController::class, 'account'], $module);

        $router->post('/shop/account/logout', [ShopController::class, 'accountLogout'], $module);
        $router->get('/shop/account/orders', [ShopController::class, 'accountOrders'], $module);
        $router->get('/shop/account/orders/{code}', [ShopController::class, 'accountOrderDetail'], $module);
        $router->get('/shop/account/profile', [ShopController::class, 'accountProfile'], $module);
        $router->post('/shop/account/profile', [ShopController::class, 'accountProfileSave'], $module);
        $router->post('/shop/account/otp/request', [ShopController::class, 'accountOtpRequest'], $module);
        $router->post('/shop/account/otp/verify', [ShopController::class, 'accountOtpVerify'], $module);
        $router->get('/shop/track', [ShopController::class, 'trackForm'], $module);
        $router->post('/shop/track', [ShopController::class, 'trackLookup'], $module);
        $router->get('/shop/invoice/{code}', [ShopController::class, 'invoice'], $module);
        $router->get('/shop/pricelist', [ShopController::class, 'pricelist'], $module);

        // پنل — محصولات و کاتالوگ
        $router->get('/admin/shop/products', [AdminShopController::class, 'products'], $module);
        $router->get('/admin/shop/products/create', [AdminShopController::class, 'productCreateForm'], $module);
        $router->post('/admin/shop/products/create', [AdminShopController::class, 'productCreate'], $module);
        $router->get('/admin/shop/products/edit/{id}', [AdminShopController::class, 'productEditForm'], $module);
        $router->post('/admin/shop/products/edit/{id}', [AdminShopController::class, 'productUpdate'], $module);
        $router->post('/admin/shop/products/delete/{id}', [AdminShopController::class, 'productDelete'], $module);

        $router->get('/admin/shop/categories', [AdminShopController::class, 'categories'], $module);
        $router->post('/admin/shop/categories/save', [AdminShopController::class, 'categorySave'], $module);
        $router->post('/admin/shop/categories/delete/{id}', [AdminShopController::class, 'categoryDelete'], $module);

        $router->get('/admin/shop/fields', [AdminShopController::class, 'fields'], $module);
        $router->post('/admin/shop/fields/save', [AdminShopController::class, 'fieldSave'], $module);
        $router->post('/admin/shop/fields/delete/{id}', [AdminShopController::class, 'fieldDelete'], $module);

        $router->get('/admin/shop/attributes', [AdminShopController::class, 'attributes'], $module);
        $router->post('/admin/shop/attributes/save', [AdminShopController::class, 'attributeSave'], $module);
        $router->post('/admin/shop/attributes/delete/{id}', [AdminShopController::class, 'attributeDelete'], $module);
        $router->post('/admin/shop/attributes/{id}/values', [AdminShopController::class, 'attributeValueAdd'], $module);
        $router->post('/admin/shop/attributes/values/delete/{id}', [AdminShopController::class, 'attributeValueDelete'], $module);

        $router->get('/admin/shop/pricelist', [AdminShopController::class, 'pricelist'], $module);
        $router->post('/admin/shop/pricelist', [AdminShopController::class, 'pricelistSave'], $module);

        $router->get('/admin/shop/settings', [AdminShopController::class, 'settingsForm'], $module);
        $router->post('/admin/shop/settings', [AdminShopController::class, 'settingsSave'], $module);

        $router->get('/admin/shop/coupons', [AdminShopController::class, 'coupons'], $module);

        $router->get('/admin/shop/templates', [AdminShopController::class, 'templates'], $module);
        $router->get('/admin/shop/templates/create', [AdminShopController::class, 'templateForm'], $module);
        $router->post('/admin/shop/templates/create', [AdminShopController::class, 'templateSave'], $module);
        $router->get('/admin/shop/templates/edit/{id}', [AdminShopController::class, 'templateForm'], $module);
        $router->post('/admin/shop/templates/edit/{id}', [AdminShopController::class, 'templateSave'], $module);
        $router->post('/admin/shop/templates/{id}/default', [AdminShopController::class, 'templateDefault'], $module);
        $router->post('/admin/shop/templates/{id}/delete', [AdminShopController::class, 'templateDelete'], $module);
        $router->post('/admin/shop/coupons/save', [AdminShopController::class, 'couponSave'], $module);
        $router->post('/admin/shop/coupons/delete/{id}', [AdminShopController::class, 'couponDelete'], $module);

        // پنل — سفارش‌ها و فاکتور
        $router->get('/admin/shop/orders', [AdminOrdersController::class, 'index'], $module);
        $router->get('/admin/shop/orders/{id}', [AdminOrdersController::class, 'detail'], $module);
        $router->post('/admin/shop/orders/{id}/status', [AdminOrdersController::class, 'statusSave'], $module);
        $router->post('/admin/shop/orders/{id}/delete', [AdminOrdersController::class, 'delete'], $module);
        $router->get('/admin/shop/invoice/{id}', [AdminOrdersController::class, 'invoice'], $module);
    },
];
