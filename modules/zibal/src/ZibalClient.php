<?php

declare(strict_types=1);

namespace Modules\Zibal;

/** کلاینت HTTP درگاه زیبال (v1) — بدون وابستگی به curl؛ با stream و timeout. */
final class ZibalClient
{
    private const REQUEST_URL = 'https://gateway.zibal.ir/v1/request';
    private const VERIFY_URL = 'https://gateway.zibal.ir/v1/verify';
    private const START_URL = 'https://gateway.zibal.ir/start/';

    /** مرچنت تست رسمی زیبال (بدون پول واقعی). */
    public const TEST_MERCHANT = 'zibal';

    public static function startUrl(string $trackId): string
    {
        return self::START_URL . rawurlencode($trackId);
    }

    /** درخواست پرداخت تازه. موفق: ['ok'=>true,'trackId'=>...]؛ ناموفق: ['ok'=>false,'message'=>...]. */
    public function request(string $merchant, int $amountRial, string $callbackUrl, string $description, string $orderId): array
    {
        $res = $this->post(self::REQUEST_URL, [
            'merchant' => $merchant,
            'amount' => $amountRial,
            'callbackUrl' => $callbackUrl,
            'description' => $description,
            'orderId' => $orderId,
        ]);
        if (isset($res['_error'])) {
            return ['ok' => false, 'message' => 'خطا در تماس با زیبال: ' . $res['_error']];
        }
        if ((int) ($res['result'] ?? 0) === 100 && ($res['trackId'] ?? '') !== '') {
            return ['ok' => true, 'trackId' => (string) $res['trackId']];
        }
        return ['ok' => false, 'message' => self::requestMessage((int) ($res['result'] ?? 0))];
    }

    /** تأیید پرداخت. خروجی خام زیبال + پرچم ok (فقط وقتی result=100). */
    public function verify(string $merchant, string $trackId): array
    {
        $res = $this->post(self::VERIFY_URL, [
            'merchant' => $merchant,
            'trackId' => $trackId,
        ]);
        if (isset($res['_error'])) {
            return ['ok' => false, 'message' => 'خطا در تماس با زیبال: ' . $res['_error']];
        }
        $res['ok'] = (int) ($res['result'] ?? 0) === 100;
        return $res;
    }

    private function post(string $url, array $payload): array
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if ($body === false) {
            return ['_error' => 'خطا در ساخت درخواست'];
        }
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nContent-Length: " . strlen($body) . "\r\n",
                'content' => $body,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return ['_error' => 'درگاه در دسترس نیست'];
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return ['_error' => 'پاسخ نامعتبر از درگاه'];
        }
        return $decoded;
    }

    private static function requestMessage(int $code): string
    {
        return match ($code) {
            102 => 'مرچنت یافت نشد.',
            103 => 'مرچنت فعال نیست.',
            104 => 'مرچنت معتبر نیست.',
            105 => 'مبلغ کمتر از حد مجاز است.',
            106 => 'آدرس بازگشت معتبر نیست.',
            113 => 'مبلغ بیش از حد مجاز است.',
            default => 'خطای درگاه زیبال (کد ' . $code . ').',
        };
    }
}
