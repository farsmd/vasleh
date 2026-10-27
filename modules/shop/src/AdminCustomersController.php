<?php
declare(strict_types=1);

namespace Modules\Shop;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل مشتریان فروشگاه: فهرست مشتریان ثبت‌شده با تعداد سفارش و جمع خرید. */
final class AdminCustomersController
{
    public function __construct(private readonly Container $container) {}

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('shop', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => $title,
            'content' => $content,
            'menu'    => $this->container->get('loader')->adminMenu(),
            'user'    => $session->user(),
            'csrf'    => $session->csrfToken(),
        ]));
    }

    public function index(Request $request): Response
    {
        $q = trim((string) $request->input('q', ''));
        return $this->page('customers', [
            'customers' => $this->container->get('shop.customers')->allWithStats($q === '' ? null : $q),
            'q' => $q,
            'currency' => $this->container->get('shop.settings')->get('currency', 'تومان'),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'مشتریان فروشگاه');
    }
}
