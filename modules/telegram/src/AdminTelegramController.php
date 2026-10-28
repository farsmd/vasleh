<?php
declare(strict_types=1);

namespace Modules\Telegram;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل تلگرام: تنظیمات ربات و فعال‌سازی وبهوک (فقط مالک). */
final class AdminTelegramController
{
    /** آدرس وبهوک روی هاست محصول (نام دامنه ثابت محصول است). */
    public const WEBHOOK_URL = 'https://atroweb.ir/tg/webhook';

    public function __construct(private readonly Container $container) {}

    private function settings(): TelegramSettings
    {
        return $this->container->get('telegram.settings');
    }

    private function ownerOnly(): ?Response
    {
        $user = $this->container->has('session') ? $this->container->get('session')->user() : null;
        if (($user['role'] ?? '') !== 'owner') {
            return Response::forbidden('این کار فقط برای مالک است');
        }
        return null;
    }

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('telegram', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
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
        $actor = (string) ($this->container->get('session')->user()['username'] ?? '');
        if ($actor !== '') {
            $this->container->get('logger')->user($actor, $action, $target);
        }
    }

    private function back(string $msg = '', string $err = ''): Response
    {
        $q = [];
        if ($msg !== '') {
            $q['msg'] = $msg;
        }
        if ($err !== '') {
            $q['err'] = $err;
        }
        return Response::redirect('/admin/telegram' . ($q === [] ? '' : '?' . http_build_query($q)));
    }

    public function index(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $s = $this->settings();
        $me = null;
        $meError = null;
        $hook = null;
        $hookError = null;
        if ($s->token() !== '') {
            // وضعیت اتصال: خرابی شبکه فقط «نامشخص» نشان می‌دهد، صفحه نمی‌شکند.
            try {
                $client = TelegramClient::fromEnv($s->token());
                $res = $client->getMe();
                if ($res['ok'] && is_array($res['data'])) {
                    $me = $res['data'];
                } else {
                    $meError = $res['message'] !== '' ? $res['message'] : 'دریافت مشخصات ربات ناموفق بود.';
                }
            } catch (\Throwable) {
                $meError = 'خطای اتصال به تلگرام.';
            }
            try {
                $res = TelegramClient::fromEnv($s->token())->getWebhookInfo();
                if ($res['ok'] && is_array($res['data'])) {
                    $hook = $res['data'];
                } else {
                    $hookError = $res['message'] !== '' ? $res['message'] : 'دریافت وضعیت وبهوک ناموفق بود.';
                }
            } catch (\Throwable) {
                $hookError = 'خطای اتصال به تلگرام.';
            }
        }
        return $this->page('settings', [
            'settings' => $s,
            'me' => $me,
            'meError' => $meError,
            'hook' => $hook,
            'hookError' => $hookError,
            'webhookUrl' => self::WEBHOOK_URL,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'تلگرام');
    }

    public function settingsSave(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $s = $this->settings();
        // توکن خالی یعنی «بدون تغییر» تا سهواً پاک نشود؛ مقدار ذخیره‌شده هرگز نمایش داده نمی‌شود.
        $token = trim((string) $request->input('bot_token', ''));
        if ($token !== '') {
            $s->set('bot_token', $token);
        }
        $s->set('bot_username', trim((string) $request->input('bot_username', '')));
        $s->set('welcome_text', trim((string) $request->input('welcome_text', '')));
        $s->ensureSecret();
        $this->log('ذخیره تنظیمات تلگرام', 'telegram');
        return $this->back('تنظیمات تلگرام ذخیره شد.');
    }

    public function webhookActivate(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $s = $this->settings();
        if ($s->token() === '') {
            return $this->back('', 'اول توکن ربات را ذخیره کنید.');
        }
        $secret = $s->ensureSecret();
        try {
            $res = TelegramClient::fromEnv($s->token())->setWebhook(self::WEBHOOK_URL, $secret);
        } catch (\Throwable) {
            return $this->back('', 'خطای اتصال به تلگرام؛ وبهوک فعال نشد.');
        }
        if (!$res['ok']) {
            return $this->back('', 'فعال‌سازی وبهوک ناموفق بود' . ($res['message'] !== '' ? ' (' . $res['message'] . ')' : '') . '.');
        }
        $this->log('فعال‌سازی وبهوک تلگرام', 'telegram');
        return $this->back('وبهوک تلگرام فعال شد.');
    }
}
