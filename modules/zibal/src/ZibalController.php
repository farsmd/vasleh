<?php

declare(strict_types=1);

namespace Modules\Zibal;

use Core\Container;
use Core\Request;
use Core\Response;

/**
 * بازگشت از درگاه زیبال (مسیر عمومی — بدون CSRF، چون زیبال مرورگر را برمی‌گرداند).
 * پارامترهای برگشتی به‌تنهایی قبول نمی‌شوند: همیشه verify صدا زده می‌شود و مبلغ
 * با سفارش مقایسه می‌شود؛ تأیید تکراری هم پرداخت دوباره ثبت نمی‌کند.
 */
final class ZibalController
{
    public function __construct(private readonly Container $container) {}

    public function callback(Request $request): Response
    {
        $trackId = trim((string) $request->input('trackId', ''));
        $payments = $this->container->get('zibal.payments');
        $payment = $payments->byTrack($trackId);
        if ($payment === null) {
            return $this->toCart('پرداخت یافت نشد.');
        }
        $code = (string) $payment['order_code'];
        if ($payment['status'] === 'paid') {
            return Response::redirect('/shop/success/' . $code);
        }
        $client = $this->container->get('zibal.client');
        $verify = $client->verify($payments->merchant(), $trackId);
        $amountOk = (int) ($verify['amount'] ?? -1) === (int) $payment['amount_rial'];
        if (($verify['ok'] ?? false) && (int) ($verify['status'] ?? 0) === 1 && $amountOk) {
            $payments->markPaid((int) $payment['id'], (string) ($verify['refNumber'] ?? ''), (string) ($verify['cardNumber'] ?? ''));
            if ($this->container->has('shop.orders')) {
                $orders = $this->container->get('shop.orders');
                $order = $orders->findByCode($code);
                if ($order !== null) {
                    $orders->setPayment((int) $order['id'], 'paid');
                }
            }
            if ($this->container->has('logger')) {
                $this->container->get('logger')->system('پرداخت زیبال تأیید شد', $code, '');
            }
            return Response::redirect('/shop/success/' . $code);
        }
        $cancelled = trim((string) $request->input('status', '')) === '3'
            || trim((string) $request->input('success', '')) === '0';
        $payments->markFailed((int) $payment['id'], $cancelled ? 'cancelled' : 'failed',
            'verify result=' . (string) ($verify['result'] ?? '?') . ' status=' . (string) ($verify['status'] ?? '?'));
        return $this->toCart('پرداخت تأیید نشد. سفارش ' . $code . ' ثبت شده و پرداخت نشده است.');
    }

    private function toCart(string $err): Response
    {
        return Response::redirect('/shop/cart?' . http_build_query(['err' => $err]));
    }
}
