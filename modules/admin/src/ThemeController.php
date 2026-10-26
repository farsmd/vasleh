<?php
declare(strict_types=1);

namespace Modules\Admin;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

final class ThemeController
{
    public function __construct(private readonly Container $container) {}

    public function index(Request $request): Response
    {
        $loader = $this->container->get('loader');
        $session = $this->container->has('session') ? $this->container->get('session') : null;
        $content = View::render('admin', 'admin', 'theme', [
            'theme' => AdminTheme::load($this->container->get('base_path')),
            'csrf' => $session?->csrfToken() ?? '',
            'flash' => $request->input('msg'),
        ]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title' => 'قالب ادمین',
            'content' => $content,
            'menu' => $loader->adminMenu(),
            'user' => $session?->user(),
            'csrf' => $session?->csrfToken() ?? '',
        ]));
    }

    public function save(Request $request): Response
    {
        $base = $this->container->get('base_path');
        if ((string) $request->input('action', '') === 'reset') {
            AdminTheme::reset($base);
            return Response::redirect('/admin/theme?msg=' . urlencode('قالب به پیش‌فرض برگشت'));
        }
        AdminTheme::save($base, [
            'accent' => $request->input('accent'),
            'panel_dark' => $request->input('panel_dark'),
            'panel_light' => $request->input('panel_light'),
            'main_dark' => $request->input('main_dark'),
            'main_light' => $request->input('main_light'),
            'radius' => $request->input('radius'),
            'font_size' => $request->input('font_size'),
            'default_theme' => $request->input('default_theme'),
        ]);
        return Response::redirect('/admin/theme?msg=' . urlencode('تنظیمات قالب ذخیره شد'));
    }
}
