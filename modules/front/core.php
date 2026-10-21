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
    'description' => 'نمای عمومی سایت',
    'version'     => '0.1.0',
    'system'      => true,

    'routes' => static function (Router $router, Container $c, string $module): void {
        $router->get('/', [FrontController::class, 'home'], $module);
    },
];
