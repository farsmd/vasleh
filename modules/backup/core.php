<?php
declare(strict_types=1);

use Core\Container;
use Core\Request;
use Core\Router;
use Modules\Backup\AdminBackupController;
use Modules\Backup\BackupManager;

/*
 * ماژول backup — پشتیبان‌گیری کامل/دیتابیس/انتخابی با دانلود ZIP،
 * بکاپ دوره‌ای خودکار (هوک) و بازگردانی امن با بکاپ ایمنی.
 * همه عملیات پنل فقط برای مالک است.
 */

return [
    'slug'        => 'backup',
    'title'       => 'پشتیبان‌گیری',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 3 3 3 3 8"/><path d="M1 3h22v5H1z"/><path d="M10 12h4"/><path d="M2 8l2.5 11a2 2 0 0 0 2 1.5h11a2 2 0 0 0 2-1.5L22 8"/></svg>',
    'description' => 'بکاپ کامل، دیتابیس و انتخابی + بکاپ دوره‌ای + بازگردانی',
    'version'     => '1.0.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'backup.manager' => static fn (Container $c): BackupManager => new BackupManager((string) $c->get('base_path')),
    ],

    'permissions' => [
        'backup.manage' => 'مدیریت پشتیبان‌گیری',
    ],

    'hooks' => [
        // بکاپ دوره‌ای: در اولین درخواست بعد از موعد ساخته می‌شود (هاست اشتراکی کرون ندارد).
        'before_dispatch' => static function (Request $request, Container $c): ?Response {
            if (str_starts_with($request->path, '/admin/backup') || str_starts_with($request->path, '/api')) {
                return null;
            }
            try {
                $c->get('backup.manager')->maybeRunPeriodic();
            } catch (\Throwable) {
                // بکاپ دوره‌ای هیچ‌وقت نباید جلوی پاسخ را بگیرد
            }
            return null;
        },
    ],

    'admin_menu' => [
        ['label' => 'پشتیبان‌گیری', 'path' => '/admin/backup', 'permission' => 'backup.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/admin/backup', [AdminBackupController::class, 'index'], $module);
        $router->post('/admin/backup/create', [AdminBackupController::class, 'create'], $module);
        $router->post('/admin/backup/settings', [AdminBackupController::class, 'settingsSave'], $module);
        $router->get('/admin/backup/download/{name}', [AdminBackupController::class, 'download'], $module);
        $router->post('/admin/backup/delete/{name}', [AdminBackupController::class, 'delete'], $module);
        $router->post('/admin/backup/restore/{name}', [AdminBackupController::class, 'restoreExisting'], $module);
        $router->post('/admin/backup/restore-upload', [AdminBackupController::class, 'restoreUpload'], $module);
    },
];
