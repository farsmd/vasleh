<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Cms\AdminCmsController;
use Modules\Cms\CmsController;
use Modules\Cms\Posts;

/*
 * ماژول cms — مدیریت محتوا:
 * نوشته‌های وبلاگ (/blog و /blog/{slug}) و برگه‌های ثابت (/page/{slug})
 * با پنل مدیریت کامل (ساخت/ویرایش/حذف، پیش‌نویس و انتشار، نامک فارسی).
 */

return [
    'slug'        => 'cms',
    'title'       => 'مدیریت محتوا',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
    'description' => 'نوشته‌های وبلاگ و برگه‌های ثابت سایت + مدیریت آن‌ها از پنل',
    'version'     => '1.0.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'cms.posts' => static fn (Container $c): Posts => new Posts($c->get('db')),
    ],

    'permissions' => [
        'cms.manage' => 'مدیریت محتوا',
    ],

    'migrate' => static function (Container $c): void {
        $posts = $c->get('cms.posts');
        $posts->migrate();
        $posts->seedIfEmpty();
    },

    'admin_menu' => [
        ['label' => 'محتواها', 'path' => '/admin/cms', 'permission' => 'cms.manage'],
        ['label' => 'محتوای تازه', 'path' => '/admin/cms/create', 'permission' => 'cms.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/blog', [CmsController::class, 'blogIndex'], $module);
        $router->get('/blog/{slug}', [CmsController::class, 'blogShow'], $module);
        $router->get('/page/{slug}', [CmsController::class, 'pageShow'], $module);

        $router->get('/admin/cms', [AdminCmsController::class, 'index'], $module);
        $router->get('/admin/cms/create', [AdminCmsController::class, 'createForm'], $module);
        $router->post('/admin/cms/create', [AdminCmsController::class, 'create'], $module);
        $router->get('/admin/cms/edit/{id}', [AdminCmsController::class, 'editForm'], $module);
        $router->post('/admin/cms/edit/{id}', [AdminCmsController::class, 'update'], $module);
        $router->post('/admin/cms/delete/{id}', [AdminCmsController::class, 'delete'], $module);
    },
];
