<?php
declare(strict_types=1);

namespace Modules\Admin;

use Core\Container;
use Core\Response;
use Core\View;

final class AdminController
{
    public function __construct(private readonly Container $container) {}

    /** چیدن صفحه‌ای از بخش مدیریت داخل لی‌اوت مشترک. */
    public function page(string $view, array $data, string $title): Response
    {
        $loader = $this->container->get('loader');
        $session = $this->container->has('session') ? $this->container->get('session') : null;
        $content = View::render('admin', 'admin', $view, $data);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => $title,
            'content' => $content,
            'menu'    => $loader->adminMenu(),
            'user'    => $session?->user(),
            'csrf'    => $session?->csrfToken() ?? '',
        ]));
    }

    public function index(): Response
    {
        $loader = $this->container->get('loader');
        return $this->page('dashboard', [
            'modules' => $loader->all(),
            'errors'  => $loader->errors,
            'loader'  => $loader,
        ], 'داشبورد مدیریت');
    }
}
