<?php
declare(strict_types=1);

namespace Modules\Sms;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل پیامک: تنظیمات اتصال و گزارش ارسال. */
final class AdminSmsController
{
    public function __construct(private readonly Container $container) {}

    private function smsSettings(): SmsSettings
    {
        return $this->container->get('sms.settings');
    }

    private function sender(): SmsSender
    {
        return $this->container->get('sms.sender');
    }

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('sms', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
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
        $s = $this->smsSettings();
        $credit = null;
        $creditError = null;
        if ($s->apiKey() !== '') {
            try {
                $res = $this->container->get('sms.client')->getCredit();
                if ((bool) $res['ok']) {
                    $credit = is_scalar($res['data']) ? (string) $res['data'] : 'متصل';
                } else {
                    $creditError = (string) $res['message'] !== '' ? (string) $res['message'] : 'دریافت اعتبار ناموفق بود.';
                }
            } catch (\Throwable) {
                $creditError = 'خطای اتصال به سامانه پیامکی.';
            }
        }
        return $this->page('settings', [
            'settings' => $s,
            'credit' => $credit,
            'creditError' => $creditError,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'تنظیمات پیامک');
    }

    public function settingsSave(Request $request): Response
    {
        $s = $this->smsSettings();
        // کلید خالی یعنی «بدون تغییر» تا سهواً پاک نشود.
        $key = trim((string) $request->input('api_key', ''));
        if ($key !== '') {
            $s->set('api_key', $key);
        }
        $s->set('line_number', trim((string) $request->input('line_number', '')));
        $tpl = trim((string) $request->input('verify_template_id', ''));
        $s->set('verify_template_id', $tpl !== '' && ctype_digit($tpl) ? $tpl : '');
        $s->set('order_placed_text', trim((string) $request->input('order_placed_text', '')));
        $s->set('order_status_text', trim((string) $request->input('order_status_text', '')));
        $this->log('ذخیره تنظیمات پیامک', 'sms');
        return $this->back('/admin/sms', 'تنظیمات پیامک ذخیره شد.');
    }

    public function logPage(Request $request): Response
    {
        return $this->page('log', [
            'rows' => $this->sender()->logList(100),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'گزارش ارسال پیامک');
    }
}
