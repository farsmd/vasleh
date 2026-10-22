<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Cms\AdminCmsController;
use Modules\Cms\AdminLayoutsController;
use Modules\Cms\AdminMenusController;
use Modules\Cms\CmsController;
use Modules\Cms\Layouts;
use Modules\Cms\Menus;
use Modules\Cms\Posts;
use Modules\Cms\Renderer;
use Modules\Cms\Settings;

/*
 * ماژول cms — مدیریت محتوا:
 * نوشته‌های وبلاگ (/blog و /blog/{slug}) و برگه‌های ثابت (/page/{slug})
 * با پنل مدیریت کامل (ساخت/ویرایش/حذف، پیش‌نویس و انتشار، نامک فارسی).
 */

return [
    'slug'        => 'cms',
    'title'       => 'مدیریت محتوا',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>',
    'description' => 'مدیریت محتوای کامل سایت: نوشته و برگه، هدر/فوتر/قالب‌های قابل ساخت، منوها، تنظیمات و پوشش صفحه اصلی',
    'version'     => '2.0.0',
    'system'      => false,
    'requires'    => ['sqlite', 'session'],

    'services' => [
        'cms.posts'    => static fn (Container $c): Posts => new Posts($c->get('db')),
        'cms.layouts'  => static fn (Container $c): Layouts => new Layouts($c->get('db')),
        'cms.menus'    => static fn (Container $c): Menus => new Menus($c->get('db')),
        'cms.settings' => static fn (Container $c): Settings => new Settings($c->get('db')),
        'cms.render'   => static fn (Container $c): Renderer => new Renderer(
            $c->get('cms.layouts'),
            $c->get('cms.menus'),
            $c->get('cms.settings'),
        ),
    ],

    'permissions' => [
        'cms.manage' => 'مدیریت محتوا',
    ],

    'migrate' => static function (Container $c): void {
        $posts = $c->get('cms.posts');
        $posts->migrate();
        $posts->seedIfEmpty();
        $layouts = $c->get('cms.layouts');
        $layouts->migrate();
        $layouts->seedIfEmpty();
        $menus = $c->get('cms.menus');
        $menus->migrate();
        $menus->seedIfEmpty();
        $c->get('cms.settings')->migrate();
    },

    'admin_menu' => [
        ['label' => 'محتواها', 'path' => '/admin/cms', 'permission' => 'cms.manage'],
        ['label' => 'محتوای تازه', 'path' => '/admin/cms/create', 'permission' => 'cms.manage'],
        ['label' => 'هدر، فوتر و قالب‌ها', 'path' => '/admin/cms/layouts', 'permission' => 'cms.manage'],
        ['label' => 'منوها', 'path' => '/admin/cms/menus', 'permission' => 'cms.manage'],
        ['label' => 'تنظیمات محتوا', 'path' => '/admin/cms/settings', 'permission' => 'cms.manage'],
    ],

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/blog', [CmsController::class, 'blogIndex'], $module);
        $router->get('/blog/{slug}', [CmsController::class, 'blogShow'], $module);
        $router->get('/page/{slug}', [CmsController::class, 'pageShow'], $module);

        // پوشش ریشه: فقط وقتی در تنظیمات، صفحه اصلی تعیین شده باشد.
        // cms در ترتیب الفبایی قبل از front ثبت می‌شود و روتر اولین منطبق را برمی‌گرداند.
        try {
            if ($c->get('cms.settings')->get('homepage', '') !== '') {
                $router->get('/', [CmsController::class, 'home'], $module);
            }
        } catch (\Throwable) {
            // دیتابیس آماده نیست: ریشه به ماژول فرانت می‌ماند.
        }

        $router->get('/admin/cms', [AdminCmsController::class, 'index'], $module);
        $router->get('/admin/cms/create', [AdminCmsController::class, 'createForm'], $module);
        $router->post('/admin/cms/create', [AdminCmsController::class, 'create'], $module);
        $router->get('/admin/cms/edit/{id}', [AdminCmsController::class, 'editForm'], $module);
        $router->post('/admin/cms/edit/{id}', [AdminCmsController::class, 'update'], $module);
        $router->post('/admin/cms/delete/{id}', [AdminCmsController::class, 'delete'], $module);

        $router->get('/admin/cms/settings', [AdminCmsController::class, 'settingsForm'], $module);
        $router->post('/admin/cms/settings', [AdminCmsController::class, 'settingsSave'], $module);

        $router->get('/admin/cms/layouts', [AdminLayoutsController::class, 'index'], $module);
        $router->get('/admin/cms/layouts/create', [AdminLayoutsController::class, 'createForm'], $module);
        $router->post('/admin/cms/layouts/create', [AdminLayoutsController::class, 'create'], $module);
        $router->get('/admin/cms/layouts/edit/{id}', [AdminLayoutsController::class, 'editForm'], $module);
        $router->post('/admin/cms/layouts/edit/{id}', [AdminLayoutsController::class, 'update'], $module);
        $router->post('/admin/cms/layouts/default/{id}', [AdminLayoutsController::class, 'setDefault'], $module);
        $router->post('/admin/cms/layouts/delete/{id}', [AdminLayoutsController::class, 'delete'], $module);

        $router->get('/admin/cms/menus', [AdminMenusController::class, 'index'], $module);
        $router->post('/admin/cms/menus/create', [AdminMenusController::class, 'create'], $module);
        $router->post('/admin/cms/menus/delete/{id}', [AdminMenusController::class, 'delete'], $module);
        $router->get('/admin/cms/menus/{id}', [AdminMenusController::class, 'items'], $module);
        $router->post('/admin/cms/menus/{id}/items', [AdminMenusController::class, 'addItem'], $module);
        $router->post('/admin/cms/menus/{id}/items/update', [AdminMenusController::class, 'updateItem'], $module);
        $router->post('/admin/cms/menus/{id}/items/delete', [AdminMenusController::class, 'deleteItem'], $module);
    },
];
