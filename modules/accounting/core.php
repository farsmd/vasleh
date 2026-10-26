<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Accounting\AdminAccountingController;
use Modules\Accounting\Ledger;

/*
 * ماژول accounting — حسابداری ساده:
 * حساب‌های پولی (صندوق/بانک) با موجودی اول دوره، دسته‌های درآمد/هزینه،
 * اسناد دریافت/پرداخت با طرف حساب و مرجع، انتقال بین حساب‌ها،
 * گزارش ماهانه و تفکیک دسته + وارد کردن فروش‌های پرداخت‌شده فروشگاه.
 */

return [
    'slug'        => 'accounting',
    'title'       => 'حسابداری',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="11" x2="8.01" y2="11"/><line x1="12" y1="11" x2="12.01" y2="11"/><line x1="16" y1="11" x2="16.01" y2="11"/><line x1="8" y1="15" x2="8.01" y2="15"/><line x1="12" y1="15" x2="12.01" y2="15"/><line x1="16" y1="15" x2="16.01" y2="15"/><line x1="8" y1="19" x2="8.01" y2="19"/><line x1="12" y1="19" x2="12.01" y2="19"/><line x1="16" y1="19" x2="16.01" y2="19"/></svg>',
    'description' => 'حسابداری: حساب‌های پولی، اسناد درآمد و هزینه، انتقال بین حساب‌ها و گزارش‌های مالی',
    'version'     => '1.0.1',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'accounting.ledger' => static fn (Container $c): Ledger => new Ledger($c->get('db')),
    ],

    'permissions' => [
        'accounting.manage' => 'مدیریت حسابداری',
    ],

    'migrate' => static function (Container $c): void {
        $ledger = $c->get('accounting.ledger');
        $ledger->migrate();
        $ledger->seedIfEmpty();
    },

    'admin_menu' => [
        ['label' => 'داشبورد حسابداری', 'path' => '/admin/accounting', 'permission' => 'accounting.manage'],
        ['label' => 'اسناد', 'path' => '/admin/accounting/transactions', 'permission' => 'accounting.manage'],
        ['label' => 'سند تازه', 'path' => '/admin/accounting/transactions/create', 'permission' => 'accounting.manage'],
        ['label' => 'حساب‌های پولی', 'path' => '/admin/accounting/accounts', 'permission' => 'accounting.manage'],
        ['label' => 'دسته‌ها', 'path' => '/admin/accounting/categories', 'permission' => 'accounting.manage'],
        ['label' => 'انتقال بین حساب‌ها', 'path' => '/admin/accounting/transfers', 'permission' => 'accounting.manage'],
        ['label' => 'گزارش‌ها', 'path' => '/admin/accounting/reports', 'permission' => 'accounting.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/accounting', [AdminAccountingController::class, 'dashboard'], $module);
        $router->get('/admin/accounting/transactions', [AdminAccountingController::class, 'transactions'], $module);
        $router->get('/admin/accounting/transactions/create', [AdminAccountingController::class, 'transactionCreateForm'], $module);
        $router->post('/admin/accounting/transactions/create', [AdminAccountingController::class, 'transactionCreate'], $module);
        $router->get('/admin/accounting/transactions/edit/{id}', [AdminAccountingController::class, 'transactionEditForm'], $module);
        $router->post('/admin/accounting/transactions/edit/{id}', [AdminAccountingController::class, 'transactionUpdate'], $module);
        $router->post('/admin/accounting/transactions/delete/{id}', [AdminAccountingController::class, 'transactionDelete'], $module);
        $router->post('/admin/accounting/import-shop', [AdminAccountingController::class, 'importShop'], $module);
        $router->get('/admin/accounting/accounts', [AdminAccountingController::class, 'accounts'], $module);
        $router->post('/admin/accounting/accounts/save', [AdminAccountingController::class, 'accountSave'], $module);
        $router->post('/admin/accounting/accounts/delete/{id}', [AdminAccountingController::class, 'accountDelete'], $module);
        $router->get('/admin/accounting/categories', [AdminAccountingController::class, 'categories'], $module);
        $router->post('/admin/accounting/categories/save', [AdminAccountingController::class, 'categorySave'], $module);
        $router->post('/admin/accounting/categories/delete/{id}', [AdminAccountingController::class, 'categoryDelete'], $module);
        $router->get('/admin/accounting/transfers', [AdminAccountingController::class, 'transfers'], $module);
        $router->post('/admin/accounting/transfers', [AdminAccountingController::class, 'transferSave'], $module);
        $router->post('/admin/accounting/transfers/delete/{id}', [AdminAccountingController::class, 'transferDelete'], $module);
        $router->get('/admin/accounting/reports', [AdminAccountingController::class, 'reports'], $module);
    },
];
