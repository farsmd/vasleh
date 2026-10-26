<?php

declare(strict_types=1);

namespace Modules\Zibal;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل درگاه زیبال: تنظیمات مرچنت + فهرست پرداخت‌ها. */
final class AdminZibalController
{
    public function __construct(private readonly Container $container) {}

    private function payments(): ZibalPayments
    {
        return $this->container->get('zibal.payments');
    }

    private function actor(): string
    {
        return (string) ($this->container->get('session')->user()['username'] ?? '');
    }

    private function currency(): string
    {
        if ($this->container->has('shop.settings')) {
            $unit = $this->container->get('shop.settings')->get('currency', '');
            if ($unit !== '') {
                return $unit;
            }
        }
        return 'تومان';
    }

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('zibal', 'admin', $view, $data + [
            'csrf' => $session->csrfToken(),
            'currency' => $this->currency(),
        ]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title' => $title,
            'content' => $content,
            'menu' => $this->container->get('loader')->adminMenu(),
            'user' => $session->user(),
            'csrf' => $session->csrfToken(),
        ]));
    }

    private function log(string $action, string $target): void
    {
        if (!$this->container->has('logger') || !$this->container->has('session')) {
            return;
        }
        $actor = $this->actor();
        if ($actor !== '') {
            $this->container->get('logger')->user($actor, $action, $target);
        }
    }

    private function back(string $path, string $msg = '', string $err = ''): Response
    {
        $q = [];
        if ($msg !== '') {
            $q['msg'] = $msg;
        }
        if ($err !== '') {
            $q['err'] = $err;
        }
        return Response::redirect($path . ($q === [] ? '' : '?' . http_build_query($q)));
    }

    public function settings(Request $request): Response
    {
        $p = $this->payments();
        return $this->page('settings', [
            'merchant' => $p->get('merchant', ''),
            'testMode' => $p->testMode(),
            'effective' => $p->merchant(),
            'ready' => $p->ready(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'درگاه زیبال');
    }

    public function settingsSave(Request $request): Response
    {
        $p = $this->payments();
        $p->set('merchant', trim((string) $request->input('merchant', '')));
        $p->set('test_mode', $request->input('test_mode') ? '1' : '0');
        $this->log('تنظیم درگاه زیبال', $p->testMode() ? 'حالت تست' : 'حالت واقعی');
        return $this->back('/admin/zibal', 'تنظیمات زیبال ذخیره شد.');
    }

    public function paymentsPage(Request $request): Response
    {
        return $this->page('payments', [
            'rows' => $this->payments()->all(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'پرداخت‌های زیبال');
    }
}
