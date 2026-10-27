<?php
declare(strict_types=1);

namespace Modules\Sms;

/** کلاینت بومی SMS.ir — بدون Composer (وصله فایل‌محور است): همان API پکیج smsir-php
 *  با HTTP داخلی. پاسخ سامانه: JSON با status/message/data؛ موفقیت یعنی HTTP 200 و status برابر ۱. */
final class SmsClient
{
    public const BASE = 'https://api.sms.ir/v1';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $base = self::BASE,
    ) {}

    /** @return array{ok: bool, status: int, message: string, data: mixed} */
    private function request(string $method, string $path, ?array $json = null): array
    {
        $url = rtrim($this->base, '/') . '/' . ltrim($path, '/');
        $headers = [
            'X-API-KEY: ' . $this->apiKey,
            'Accept: application/json',
        ];
        $body = null;
        if ($json !== null) {
            $headers[] = 'Content-Type: application/json';
            $body = json_encode($json, JSON_UNESCAPED_UNICODE);
        }
        $ctx = stream_context_create(['http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'content' => $body,
            'timeout' => 15,
            'ignore_errors' => true,
        ], 'ssl' => ['verify_peer' => true, 'verify_peer_name' => true]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return ['ok' => false, 'status' => 0, 'message' => 'خطای اتصال به سامانه پیامکی', 'data' => null];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'status' => 0, 'message' => 'پاسخ نامعتبر از سامانه پیامکی', 'data' => null];
        }
        $status = (int) ($decoded['status'] ?? 0);
        $http = 0;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $http = (int) $m[1];
        }
        return [
            'ok' => $http === 200 && $status === 1,
            'status' => $status,
            'message' => (string) ($decoded['message'] ?? ''),
            'data' => $decoded['data'] ?? null,
        ];
    }

    /** ارسال کد تأیید با قالب ازپیش‌تعریف‌شده (پارامتر Code). */
    public function verifySend(string $mobile, int $templateId, string $code): array
    {
        return $this->request('POST', 'send/verify', [
            'mobile' => $mobile,
            'templateId' => $templateId,
            'parameters' => [['name' => 'Code', 'value' => $code]],
        ]);
    }

    /** ارسال متن به چند شماره با یک خط فرستنده. */
    public function sendBulk(string $lineNumber, string $message, array $mobiles): array
    {
        return $this->request('POST', 'send/bulk', [
            'lineNumber' => $lineNumber,
            'messageText' => $message,
            'mobiles' => array_values($mobiles),
            'sendDateTime' => null,
        ]);
    }

    public function getCredit(): array
    {
        return $this->request('GET', 'credit');
    }

    public function getLines(): array
    {
        return $this->request('GET', 'line');
    }
}
