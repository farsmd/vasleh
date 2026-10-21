<?php
declare(strict_types=1);

namespace Modules\Front;

use Core\Container;
use Core\Response;
use Core\View;

final class FrontController
{
    public function __construct(private readonly Container $container) {}

    public function home(): Response
    {
        $loader = $this->container->get('loader');
        $content = View::render('front', 'front', 'home', ['modules' => $loader->all(), 'loader' => $loader]);
        return Response::html(View::render('front', 'front', 'layout', [
            'title'   => 'وصله — صفحه اصلی',
            'content' => $content,
        ]));
    }
}
