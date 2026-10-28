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
        // لایهٔ انتقال: اول cURL (روی هاست‌هایی که allow_url_fopen خاموش است
        // هم کار می‌کند)، و اگر cURL نبود یا خطا داد، همان stream قبلی.
        if (function_exists('curl_init')) {
            $raw = $this->requestViaCurl($url, $headers, $body);
            if ($raw !== false) {
                return $this->parseResponse($raw);
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
        return $this->parseResponse($raw);
    }

    /** ارسال با cURL؛ در صورت خطای شبکه false تا stream هم شانسش را امتحان کند. */
    private function requestViaCurl(string $url, array $headers, ?string $body): string|false
    {
        $ch = curl_init($url);
        if ($ch === false) {
            return false;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        $raw = curl_exec($ch);
        curl_close($ch);
        return is_string($raw) ? $raw : false;
    }

    /** تفسیر پاسخ JSON تلگرام؛ شکل خروجی با قبل یکسان است. */
    private function parseResponse(string $raw): array
    {
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
