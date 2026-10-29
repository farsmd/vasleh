<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Personal\MeController;
use Modules\Personal\Personal;

/*
 * ماژول personal — مدیریت شخصی در /me:
 * تودو، زمان‌بندی روزانه/هفتگی با ساعت، نوت، پروژه (نیازها + شروع +
 * ساعت مورد نیاز). همه داده‌ها فقط مال همان کاربر است؛ پروژه و نوت
 * به دو شکل قابل اشتراک‌اند: با کاربر دیگر وصله (فقط خواندنی) و لینک
 * عمومی فقط‌خواندنی. تودو و رویداد همیشه خصوصی‌اند.
 */

return [
    'slug'        => 'personal',
    'title'       => 'مدیریت شخصی',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>',
    'description' => 'مدیریت شخصی: تودو، زمان‌بندی روز و هفته، نوت و پروژه با اشتراک‌گذاری',
    'version'     => '1.4.1',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'personal.store' => static fn (Container $c): Personal => new Personal($c->get('db')),
    ],

    'permissions' => [
        'personal.use' => 'استفاده از مدیریت شخصی',
    ],

    'migrate' => static function (Container $c): void {
        $c->get('personal.store')->migrate();
        $c->get('personal.store')->migrateTaskyar();
    },

    'admin_menu' => [
        ['label' => 'مدیریت شخصی', 'path' => '/me', 'permission' => 'personal.use'],
        ['label' => 'داشبورد شخصی', 'path' => '/me/dashboard', 'permission' => 'personal.use'],
        ['label' => 'تنظیمات شخصی', 'path' => '/me/settings', 'permission' => 'personal.use'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/me', [MeController::class, 'tasks'], $module);
        $router->get('/me/manifest.webmanifest', [MeController::class, 'pwaManifest'], $module);
        $router->get('/me/sw.js', [MeController::class, 'pwaServiceWorker'], $module);
        $router->get('/me/dashboard', [MeController::class, 'dashboard'], $module);
        $router->get('/me/calendar', [MeController::class, 'calendar'], $module);
        $router->get('/me/settings', [MeController::class, 'settings'], $module);
        $router->post('/me/settings', [MeController::class, 'settingsSave'], $module);
        $router->get('/me/day', [MeController::class, 'dayRedirect'], $module);
        $router->get('/me/week', [MeController::class, 'weekRedirect'], $module);
        $router->get('/me/todos', [MeController::class, 'todosRedirect'], $module);
        $router->post('/me/task', [MeController::class, 'taskSave'], $module);
        $router->post('/me/task/{kind}/{id}/toggle', [MeController::class, 'taskToggleAction'], $module);
        $router->post('/me/task/{kind}/{id}/delete', [MeController::class, 'taskDeleteAction'], $module);
        $router->post('/me/todos', [MeController::class, 'todoCreate'], $module);
        $router->post('/me/todos/{id}/toggle', [MeController::class, 'todoToggle'], $module);
        $router->post('/me/todos/{id}/delete', [MeController::class, 'todoDelete'], $module);
        $router->post('/me/events', [MeController::class, 'eventCreate'], $module);
        $router->post('/me/events/{id}/delete', [MeController::class, 'eventDelete'], $module);
        $router->get('/me/notes', [MeController::class, 'notes'], $module);
        $router->post('/me/notes', [MeController::class, 'noteCreate'], $module);
        $router->get('/me/notes/{id}', [MeController::class, 'noteShow'], $module);
        $router->post('/me/notes/{id}', [MeController::class, 'noteSave'], $module);
        $router->post('/me/notes/{id}/delete', [MeController::class, 'noteDelete'], $module);
        $router->get('/me/projects', [MeController::class, 'projects'], $module);
        $router->post('/me/projects', [MeController::class, 'projectCreate'], $module);
        $router->get('/me/projects/{id}', [MeController::class, 'projectShow'], $module);
        $router->post('/me/projects/{id}', [MeController::class, 'projectSave'], $module);
        $router->post('/me/projects/{id}/delete', [MeController::class, 'projectDelete'], $module);
        $router->post('/me/projects/{id}/needs', [MeController::class, 'needAdd'], $module);
        $router->post('/me/projects/needs/{id}/toggle', [MeController::class, 'needToggle'], $module);
        $router->post('/me/projects/needs/{id}/delete', [MeController::class, 'needDelete'], $module);
        $router->get('/me/shared', [MeController::class, 'shared'], $module);
        $router->get('/me/shared/{type}/{id}', [MeController::class, 'sharedView'], $module);
        $router->post('/me/share/user', [MeController::class, 'shareUser'], $module);
        $router->post('/me/share/link', [MeController::class, 'shareLink'], $module);
        $router->post('/me/share/user/{id}/delete', [MeController::class, 'revokeShare'], $module);
        $router->post('/me/share/link/{id}/delete', [MeController::class, 'revokeLink'], $module);
        $router->get('/me/search', [MeController::class, 'search'], $module);
        $router->post('/me/rollover', [MeController::class, 'rollover'], $module);
        $router->post('/me/filters', [MeController::class, 'filterSave'], $module);
        $router->post('/me/filters/{idx}/delete', [MeController::class, 'filterDelete'], $module);
        $router->get('/me/habits', [MeController::class, 'habits'], $module);
        $router->post('/me/habits', [MeController::class, 'habitCreate'], $module);
        $router->post('/me/habits/{id}/toggle', [MeController::class, 'habitToggle'], $module);
        $router->post('/me/habits/{id}/delete', [MeController::class, 'habitDelete'], $module);
        $router->get('/me/review', [MeController::class, 'review'], $module);
        $router->get('/me/stats', [MeController::class, 'stats'], $module);
        $router->get('/me/board', [MeController::class, 'board'], $module);
        $router->get('/me/matrix', [MeController::class, 'matrix'], $module);
        $router->get('/me/goals', [MeController::class, 'goals'], $module);
        $router->post('/me/goals', [MeController::class, 'goalCreate'], $module);
        $router->get('/me/goals/{id}', [MeController::class, 'goalShow'], $module);
        $router->post('/me/goals/{id}/delete', [MeController::class, 'goalDelete'], $module);
        $router->post('/me/goals/{id}/milestones', [MeController::class, 'milestoneAdd'], $module);
        $router->post('/me/goals/milestones/{id}/toggle', [MeController::class, 'milestoneToggle'], $module);
        $router->post('/me/goals/milestones/{id}/delete', [MeController::class, 'milestoneDelete'], $module);
        $router->post('/me/goals/{id}/tasks', [MeController::class, 'goalLinkTask'], $module);
        $router->post('/me/goals/tasks/{id}/delete', [MeController::class, 'goalUnlinkTask'], $module);
        $router->get('/me/journal', [MeController::class, 'journal'], $module);
        $router->post('/me/journal', [MeController::class, 'journalSave'], $module);
        $router->get('/me/export', [MeController::class, 'export'], $module);
        $router->post('/me/pomo/log', [MeController::class, 'pomoLog'], $module);
        $router->post('/me/task/{kind}/{id}/snooze', [MeController::class, 'taskSnooze'], $module);
        $router->post('/me/todos/{id}/subtasks', [MeController::class, 'subtaskAdd'], $module);
        $router->post('/me/todos/subtasks/{id}/toggle', [MeController::class, 'subtaskToggle'], $module);
        $router->post('/me/todos/subtasks/{id}/delete', [MeController::class, 'subtaskDelete'], $module);
        $router->post('/me/notes/{id}/links', [MeController::class, 'noteLinkAdd'], $module);
        $router->post('/me/notes/links/{id}/delete', [MeController::class, 'noteLinkDelete'], $module);
        $router->post('/me/projects/{id}/duplicate', [MeController::class, 'projectDuplicate'], $module);
        $router->get('/me/share/{token}', [MeController::class, 'publicShare'], $module);
    },
];
