<?php
declare(strict_types=1);

use Core\Container;
use Core\Request;
use Core\Response;
use Core\Router;
use Modules\Reports\Logger;
use Modules\Reports\ReportsController;

/*
 * ماژول reports — گزارشات (سیستمی):
 * یک جدول لاگ واحد (reports_log) با سه نوع ردیف:
 *  - system: تغییرات و رویدادهای سیستم (ماژول‌ها، توکن‌ها، آپدیت، تلاش ورود ناموفق…)
 *  - user: کارهای هر کاربر واردشده (ورود، ساخت کاربر، تغییر رمز…)
 *  - visit: بازدید روت‌ها و ویوها با IP و مشخصات بازدیدکننده (از هوک before_dispatch)
 * بقیه ماژول‌ها از راه سرویس logger رویداد ثبت می‌کنند (اگر ماژول حاضر باشد).
 * بازدید صفحات خود گزارشات ثبت نمی‌شود تا لاگ با نویز خودش پر نشود.
 * ثبت لاگ هرگز نباید سایت را خراب کند: همه‌جا try/catch دارد.
 */

return [
    'slug'        => 'reports',
    'title'       => 'گزارشات',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>',
    'description' => 'لاگ سیستم، فعالیت کاربران و گزارش بازدید روت‌ها و ویوها',
    'version'     => '0.1.0',
    'system'      => true,
    'requires'    => ['sqlite', 'session'],

    'permissions' => [
        'reports.view' => 'مشاهده گزارشات (لاگ سیستم، فعالیت کاربران، بازدیدها)',
    ],

    'admin_menu' => [
        ['label' => 'لاگ سیستم', 'path' => '/admin/reports', 'permission' => 'reports.view'],
        ['label' => 'فعالیت کاربران', 'path' => '/admin/reports/users', 'permission' => 'reports.view'],
        ['label' => 'بازدیدها', 'path' => '/admin/reports/visits', 'permission' => 'reports.view'],
    ],

    'services' => [
        'logger' => static fn (Container $c): Logger => new Logger($c->get('db')),
    ],

    'migrate' => static function (Container $c): void {
        $c->get('logger')->migrate();
    },

    'hooks' => [
        'before_dispatch' => static function (Request $request, Container $c): ?Response {
            try {
                if (str_starts_with($request->path, '/admin/reports')) {
                    return null; // خود گزارشات، بازدید حساب نمی‌شوند
                }
                $actor = 'مهمان';
                if ($c->has('session')) {
                    $session = $c->get('session');
                    $session->start($request);
                    $user = $session->user();
                    if ($user !== null) {
                        $actor = (string) $user['username'];
                    } elseif (str_starts_with($request->path, '/api/')) {
                        $actor = 'api';
                    }
                }
                $c->get('logger')->visit($request->method, $request->path, $actor);
            } catch (\Throwable) {
                // لاگ هیچ‌وقت نباید جلوی پاسخ را بگیرد
            }
            return null;
        },
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/reports', [ReportsController::class, 'log'], $module);
        $router->get('/admin/reports/users', [ReportsController::class, 'users'], $module);
        $router->get('/admin/reports/visits', [ReportsController::class, 'visits'], $module);
        $router->post('/admin/reports/prune', [ReportsController::class, 'prune'], $module);
    },
];
