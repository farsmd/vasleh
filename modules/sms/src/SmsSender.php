<?php
declare(strict_types=1);

namespace Modules\Sms;

use Modules\Shop\Customers;
use Modules\Sqlite\Database;

/** ارسال پیامک + کد یک‌بارمصرف ورود. خرابی ارسال هیچ‌وقت جریان فروشگاه را نمی‌شکند:
 *  همه فراخوانی‌ها false برمی‌گردانند و در گزارش ثبت می‌شوند. */
final class SmsSender
{
    public const OTP_TTL = 300;
    public const OTP_RESEND_WAIT = 60;
    public const OTP_MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly Database $db,
        private readonly SmsSettings $settings,
        private readonly SmsClient $client,
    ) {}

    public function migrate(): void
    {
        $pdo = $this->db->pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS sms_otps (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            phone TEXT NOT NULL,
            code_hash TEXT NOT NULL,
            expires_at INTEGER NOT NULL,
            attempts INTEGER NOT NULL DEFAULT 0,
            used INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS sms_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            phone TEXT NOT NULL,
            kind TEXT NOT NULL,
            message TEXT NOT NULL,
            status TEXT NOT NULL,
            provider_message TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL
        )");
    }

    public function log(string $phone, string $kind, string $message, bool $ok, string $providerMessage = ''): void
    {
        $stmt = $this->db->pdo()->prepare('INSERT INTO sms_log (phone, kind, message, status, provider_message, created_at)
            VALUES (:phone, :kind, :message, :status, :provider, :created)');
        $stmt->execute([
            'phone' => $phone, 'kind' => $kind, 'message' => $message,
            'status' => $ok ? 'sent' : 'failed', 'provider' => $providerMessage,
            'created' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /** @return array<int, array> */
    public function logList(int $limit = 100): array
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM sms_log ORDER BY id DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** ارسال متن ساده؛ آماده نباشد یا ارسال ناموفق باشد، false است. */
    public function sendText(string $phone, string $message, string $kind): bool
    {
        if (!$this->settings->ready()) {
            $this->log($phone, $kind, $message, false, 'تنظیم نشده');
            return false;
        }
        $res = $this->client->sendBulk($this->settings->lineNumber(), $message, [$phone]);
        $this->log($phone, $kind, $message, (bool) $res['ok'], (string) $res['message']);
        return (bool) $res['ok'];
    }

    public function sendOrderPlaced(string $code, string $phone): bool
    {
        return $this->sendText($phone, $this->settings->renderOrderText('placed', $code, ''), 'order_placed');
    }

    public function sendOrderStatus(string $code, string $phone, string $statusLabel): bool
    {
        return $this->sendText($phone, $this->settings->renderOrderText('status', $code, $statusLabel), 'order_status');
    }

    /** @return array{0: bool, 1: ?string} موفقیت و پیام خطا */
    public function requestOtp(string $phone): array
    {
        if (!Customers::validPhone($phone)) {
            return [false, 'شماره موبایل معتبر نیست (با 09 شروع شود و ۱۱ رقم باشد).'];
        }
        if (!$this->settings->otpReady()) {
            return [false, 'ارسال پیامک فعال نیست.'];
        }
        $now = time();
        $stmt = $this->db->pdo()->prepare('SELECT created_at FROM sms_otps WHERE phone = :phone ORDER BY id DESC LIMIT 1');
        $stmt->execute(['phone' => $phone]);
        $last = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($last !== false && $now - (int) $last['created_at'] < self::OTP_RESEND_WAIT) {
            return [false, 'کد تازه ارسال شده است؛ برای ارسال دوباره کمی صبر کنید.'];
        }
        $code = (string) random_int(10000, 99999);
        $res = $this->client->verifySend($phone, $this->settings->verifyTemplateId(), $code);
        if (!(bool) $res['ok']) {
            $this->log($phone, 'otp', 'کد تأیید ورود', false, (string) $res['message']);
            return [false, 'ارسال کد ناموفق بود؛ کمی بعد دوباره تلاش کنید.'];
        }
        $stmt = $this->db->pdo()->prepare('INSERT INTO sms_otps (phone, code_hash, expires_at, created_at)
            VALUES (:phone, :hash, :exp, :created)');
        $stmt->execute([
            'phone' => $phone, 'hash' => hash('sha256', $code),
            'exp' => $now + self::OTP_TTL, 'created' => $now,
        ]);
        $this->log($phone, 'otp', 'کد تأیید ورود', true, (string) $res['message']);
        return [true, null];
    }

    /** بررسی کد؛ درست باشد یک‌بار مصرف می‌شود. */
    public function verifyOtp(string $phone, string $code): bool
    {
        $code = trim($code);
        if ($code === '') {
            return false;
        }
        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare('SELECT * FROM sms_otps WHERE phone = :phone AND used = 0 ORDER BY id DESC LIMIT 1');
        $stmt->execute(['phone' => $phone]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return false;
        }
        if (time() > (int) $row['expires_at'] || (int) $row['attempts'] >= self::OTP_MAX_ATTEMPTS) {
            return false;
        }
        $stmt = $pdo->prepare('UPDATE sms_otps SET attempts = attempts + 1 WHERE id = :id');
        $stmt->execute(['id' => (int) $row['id']]);
        if (!hash_equals((string) $row['code_hash'], hash('sha256', $code))) {
            return false;
        }
        $stmt = $pdo->prepare('UPDATE sms_otps SET used = 1 WHERE id = :id');
        $stmt->execute(['id' => (int) $row['id']]);
        return true;
    }
}
