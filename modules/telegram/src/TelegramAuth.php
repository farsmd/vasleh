<?php
declare(strict_types=1);

namespace Modules\Telegram;

/**
 * اعتبارسنجی initData مینی‌اپ تلگرام (سند Bot API):
 * رشتهٔ بررسی از همهٔ جفت‌های initData به‌جز hash ساخته می‌شود (مرتب‌شده
 * بر اساس کلید، با \n)، کلید محرمانه HMAC_SHA256 با کلید «WebAppData» روی
 * توکن ربات است، و امضا با hash_equals مقایسه می‌شود. بدون توکن ربات
 * هیچ initDataای معتبر نیست؛ تازگی امضا حداکثر ۲۴ ساعت است.
 */
final class TelegramAuth
{
    /** @return array{ok: bool, user: ?array} */
    public static function validateInitData(string $initData, string $botToken): array
    {
        if ($initData === '' || $botToken === '') {
            return ['ok' => false, 'user' => null];
        }
        $pairs = [];
        foreach (explode('&', $initData) as $part) {
            if ($part === '') {
                continue;
            }
            $kv = explode('=', $part, 2);
            if (count($kv) === 2) {
                $pairs[urldecode($kv[0])] = urldecode($kv[1]);
            }
        }
        $hash = $pairs['hash'] ?? '';
        if ($hash === '') {
            return ['ok' => false, 'user' => null];
        }
        unset($pairs['hash']);
        ksort($pairs);
        $checkParts = [];
        foreach ($pairs as $key => $value) {
            $checkParts[] = $key . '=' . $value;
        }
        $dataCheckString = implode("\n", $checkParts);
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $expected = hash_hmac('sha256', $dataCheckString, $secretKey);
        if (!hash_equals($expected, strtolower($hash))) {
            return ['ok' => false, 'user' => null];
        }
        $user = null;
        if (isset($pairs['user'])) {
            $decoded = json_decode($pairs['user'], true);
            $user = is_array($decoded) ? $decoded : null;
        }
        // تازگی: امضای قدیمی‌تر از ۲۴ ساعت پذیرفته نمی‌شود.
        if (isset($pairs['auth_date']) && ctype_digit((string) $pairs['auth_date'])) {
            if (time() - (int) $pairs['auth_date'] > 86400) {
                return ['ok' => false, 'user' => null];
            }
        }
        return ['ok' => true, 'user' => $user];
    }
}
