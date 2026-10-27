<?php
declare(strict_types=1);

namespace Modules\Sms;

use Modules\Sqlite\Database;

/** تنظیمات پیامک: جدول نام/مقدار، مثل تنظیمات زیبال. */
final class SmsSettings
{
    public function __construct(private readonly Database $db) {}

    public function migrate(): void
    {
        $pdo = $this->db->pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS sms_settings (
            name TEXT PRIMARY KEY,
            value TEXT NOT NULL DEFAULT ''
        )");
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO sms_settings (name, value) VALUES (:name, :value)');
        foreach ([
            'api_key' => '',
            'line_number' => '',
            'verify_template_id' => '',
            'order_template_id' => '',
            'order_template_param' => 'Code',
            'order_placed_text' => 'سفارش شما با کد {{code}} ثبت شد.',
            'order_status_text' => 'وضعیت سفارش {{code}} به {{status}} تغییر کرد.',
        ] as $name => $value) {
            $stmt->execute(['name' => $name, 'value' => $value]);
        }
    }

    public function get(string $name, string $default = ''): string
    {
        $stmt = $this->db->pdo()->prepare('SELECT value FROM sms_settings WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? (string) $row['value'] : $default;
    }

    public function set(string $name, string $value): void
    {
        $stmt = $this->db->pdo()->prepare('INSERT INTO sms_settings (name, value) VALUES (:name, :value)
            ON CONFLICT(name) DO UPDATE SET value = excluded.value');
        $stmt->execute(['name' => $name, 'value' => $value]);
    }

    public function apiKey(): string
    {
        return $this->get('api_key', '');
    }

    public function lineNumber(): string
    {
        return $this->get('line_number', '');
    }

    public function verifyTemplateId(): int
    {
        $v = $this->get('verify_template_id', '');
        return $v !== '' && ctype_digit($v) ? (int) $v : 0;
    }

    /** شناسه قالب پیامک ثبت سفارش؛ صفر یعنی ارسال با متن ساده. */
    public function orderTemplateId(): int
    {
        $v = $this->get('order_template_id', '');
        return $v !== '' && ctype_digit($v) ? (int) $v : 0;
    }

    /** نام پارامتر کد سفارش در قالب؛ خالی یعنی Code. */
    public function orderTemplateParam(): string
    {
        $v = trim($this->get('order_template_param', ''));
        return $v !== '' ? $v : 'Code';
    }

    /** آماده ارسال متن: کلید + خط هر دو لازم‌اند. */
    public function ready(): bool
    {
        return $this->apiKey() !== '' && $this->lineNumber() !== '';
    }

    /** آماده ارسال کد تأیید: علاوه بر آماده‌بودن، شناسه قالب هم لازم است. */
    public function otpReady(): bool
    {
        return $this->ready() && $this->verifyTemplateId() > 0;
    }

    public function orderPlacedText(): string
    {
        return $this->get('order_placed_text', 'سفارش شما با کد {{code}} ثبت شد.');
    }

    public function orderStatusText(): string
    {
        return $this->get('order_status_text', 'وضعیت سفارش {{code}} به {{status}} تغییر کرد.');
    }

    public function renderOrderText(string $kind, string $code, string $statusLabel): string
    {
        $tpl = $kind === 'status' ? $this->orderStatusText() : $this->orderPlacedText();
        return str_replace(['{{code}}', '{{status}}'], [$code, $statusLabel], $tpl);
    }
}
