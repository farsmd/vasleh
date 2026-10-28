<?php
declare(strict_types=1);

namespace Modules\Telegram;

/**
 * کلاینت بومی Bot API تلگرام — بدون Composer و بدون curl، با stream داخلی.
 * پایهٔ API از متغیر محیطی TELEGRAM_API_BASE قابل بازنویسی است تا تست محلی
 * بتواند به‌جای api.telegram.org به یک سرور ساختگی وصل شود؛ در محصول همان
 * مقدار پیش‌فرض استفاده می‌شود. خرابی شبکه همیشه آرایهٔ ok=false می‌دهد،
 * هرگز استثنا پرتاب نمی‌کند تا وبهوک و پنل نشکنند.
 */
final class TelegramClient
{
    public const DEFAULT_BASE = 'https://api.telegram.org';

    public function __construct(
        private readonly string $token,
        private readonly string $base = self::DEFAULT_BASE,
    ) {}

    /** ساخت از روی توکن با پایهٔ قابل بازنویسی برای تست (پیش‌فرض: api.telegram.org). */
    public static function fromEnv(string $token): self
    {
        $base = trim((string) getenv('TELEGRAM_API_BASE'));
        return new self($token, $base !== '' ? $base : self::DEFAULT_BASE);
    }

    /** @return array{ok: bool, message: string, data: mixed} */
    private function request(string $method, ?array $payload = null): array
    {
        $url = rtrim($this->base, '/') . '/bot' . $this->token . '/' . $method;
        $headers = ['Accept: application/json'];
        $body = null;
        if ($payload !== null) {
            $headers[] = 'Content-Type: application/json';
            $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
            if ($body === false) {
                return ['ok' => false, 'message' => 'خطا در ساخت درخواست', 'data' => null];
            }
        }
        $ctx = stream_context_create(['http' => [
            'method' => $payload !== null ? 'POST' : 'GET',
            'header' => implode("\r\n", $headers),
            'content' => $body,
            'timeout' => 15,
            'ignore_errors' => true,
        ], 'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return ['ok' => false, 'message' => 'خطای اتصال به تلگرام', 'data' => null];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'message' => 'پاسخ نامعتبر از تلگرام', 'data' => null];
        }
        return [
            'ok' => (bool) ($decoded['ok'] ?? false),
            'message' => (string) ($decoded['description'] ?? ''),
            'data' => $decoded['result'] ?? null,
        ];
    }

    /** مشخصات ربات (برای نمایش وضعیت اتصال در پنل). */
    public function getMe(): array
    {
        return $this->request('getMe');
    }

    /** وضعیت وبهوک فعلی ربات. */
    public function getWebhookInfo(): array
    {
        return $this->request('getWebhookInfo');
    }

    /** ثبت وبهوک با راز محرمانه (تلگرام همان راز را در هدر درخواست‌ها می‌فرستد). */
    public function setWebhook(string $url, string $secretToken): array
    {
        return $this->request('setWebhook', [
            'url' => $url,
            'secret_token' => $secretToken,
            'allowed_updates' => ['message'],
        ]);
    }

    /** ارسال پیام به یک گفت‌وگو، با صفحه‌کلید شیشه‌ای اختیاری. */
    public function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): array
    {
        $payload = ['chat_id' => $chatId, 'text' => $text];
        if ($replyMarkup !== null) {
            $payload['reply_markup'] = $replyMarkup;
        }
        return $this->request('sendMessage', $payload);
    }
}
