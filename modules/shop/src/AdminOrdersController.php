<?php
declare(strict_types=1);

namespace Modules\Shop;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;
use Modules\Sms\SmsSender;

/** پنل سفارش‌ها: فهرست، جزئیات، تغییر وضعیت و فاکتور فروش چاپی. */
final class AdminOrdersController
{
    public function __construct(private readonly Container $container) {}

    private function orders(): Orders
    {
        return $this->container->get('shop.orders');
    }

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

    private function log(string $action, string $target): void
    {
        if (!$this->container->has('logger') || !$this->container->has('session')) {
            return;
        }
        $actor = (string) ($this->container->get('session')->user()['username'] ?? '');
        if ($actor !== '') {
            $this->container->get('logger')->user($actor, $action, $target);
        }
    }

    public function index(Request $request): Response
    {
        $status = $request->input('status');
        return $this->page('orders', [
            'orders' => $this->orders()->all(is_string($status) ? $status : null),
            'stats' => $this->orders()->stats(),
            'statuses' => Orders::STATUSES,
            'activeStatus' => is_string($status) ? $status : '',
            'currency' => $this->container->get('shop.settings')->get('currency', 'تومان'),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'سفارش‌های فروشگاه');
    }

    public function detail(Request $request, array $params): Response
    {
        $order = $this->orders()->find((int) ($params['id'] ?? 0));
        if ($order === null) {
            return Response::redirect('/admin/shop/orders?err=' . urlencode('سفارش یافت نشد.'));
        }
        return $this->page('order', [
            'order' => $order,
            'items' => $this->orders()->items((int) $order['id']),
            'statuses' => Orders::STATUSES,
            'currency' => $this->container->get('shop.settings')->get('currency', 'تومان'),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'سفارش ' . $order['code']);
    }

    public function statusSave(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $order = $this->orders()->find($id);
        if ($order !== null) {
            $oldStatus = (string) $order['status'];
            $newStatus = (string) $request->input('status', 'new');
            $this->orders()->setStatus($id, $newStatus);
            $this->orders()->setPayment($id, (string) $request->input('payment_status', 'unpaid'));
            $this->log('تغییر وضعیت سفارش', (string) $order['code']);
            if ($newStatus !== $oldStatus && isset(Orders::STATUSES[$newStatus]) && $this->container->has('sms.sender')) {
                try {
                    $sender = $this->container->get('sms.sender');
                    assert($sender instanceof SmsSender);
                    $sender->sendOrderStatus((string) $order['code'], (string) $order['phone'], (string) Orders::STATUSES[$newStatus]);
                } catch (\Throwable) {
                }
            }
        }
        return Response::redirect('/admin/shop/orders/' . $id . '?msg=' . urlencode('وضعیت سفارش به‌روز شد.'));
    }

    public function delete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $order = $this->orders()->find($id);
        if ($order !== null) {
            $this->orders()->delete($id);
            $this->log('حذف سفارش', (string) $order['code']);
        }
        return Response::redirect('/admin/shop/orders?msg=' . urlencode('سفارش حذف شد.'));
    }

    /** فاکتور فروش چاپی — صفحه مستقل بدون قالب پنل. */
    public function invoice(Request $request, array $params): Response
    {
        $order = $this->orders()->find((int) ($params['id'] ?? 0));
        if ($order === null) {
            return Response::notFound('سفارش یافت نشد.');
        }
        $settings = $this->container->get('shop.settings');
        return Response::html(View::render('shop', 'admin', 'invoice', [
            'order' => $order,
            'items' => $this->orders()->items((int) $order['id']),
            'statuses' => Orders::STATUSES,
            'shopTitle' => $settings->get('shop_title', 'فروشگاه'),
            'currency' => $settings->get('currency', 'تومان'),
        ]));
    }
}
