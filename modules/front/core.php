<?php
declare(strict_types=1);

use Core\Container;
use Core\Router;
use Modules\Front\FrontController;

/*
 * ماژول front — فرانت‌اند سایت هم یک ماژول است:
 * پوسته و صفحه اصلی عمومی را ارائه می‌کند.
 */

return [
    'slug'        => 'front',
    'title'       => 'فرانت‌اند',
    'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    'description' => 'نمای عمومی سایت',
    'version'     => '0.1.0',
    'system'      => true,

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/', [FrontController::class, 'home'], $module);
    },
];
