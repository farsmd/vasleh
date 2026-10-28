<?php
declare(strict_types=1);

namespace Modules\Telegram;

use Modules\Sqlite\Database;

/** تنظیمات تلگرام: جدول نام/مقدار، مثل تنظیمات پیامک. توکن و راز وبهوک هرگز رندر نمی‌شوند. */
final class TelegramSettings
{
    public function __construct(private readonly Database $db) {}

    public function migrate(): void
    {
        $pdo = $this->db->pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS telegram_settings (
            name TEXT PRIMARY KEY,
            value TEXT NOT NULL DEFAULT ''
        )");
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO telegram_settings (name, value) VALUES (:name, :value)');
        foreach ([
            'bot_token' => '',
            'bot_username' => '',
            'welcome_text' => 'به فروشگاه وصله خوش آمدید! برای خرید، دکمه «باز کردن فروشگاه» را بزنید.',
            'webhook_secret' => '',
        ] as $name => $value) {
            $stmt->execute(['name' => $name, 'value' => $value]);
        }
    }

    public function get(string $name, string $default = ''): string
    {
        $stmt = $this->db->pdo()->prepare('SELECT value FROM telegram_settings WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? (string) $row['value'] : $default;
    }

    public function set(string $name, string $value): void
    {
        $stmt = $this->db->pdo()->prepare('INSERT INTO telegram_settings (name, value) VALUES (:name, :value)
            ON CONFLICT(name) DO UPDATE SET value = excluded.value');
        $stmt->execute(['name' => $name, 'value' => $value]);
    }

    public function token(): string
    {
        return $this->get('bot_token', '');
    }

    public function username(): string
    {
        return $this->get('bot_username', '');
    }

    public function welcomeText(): string
    {
        $v = trim($this->get('welcome_text', ''));
        return $v !== '' ? $v : 'به فروشگاه وصله خوش آمدید!';
    }

    public function webhookSecret(): string
    {
        return $this->get('webhook_secret', '');
    }

    /** راز وبهوک را اگر نیست می‌سازد و برمی‌گرداند؛ مقدارش هیچ‌جا نمایش داده نمی‌شود. */
    public function ensureSecret(): string
    {
        $secret = $this->webhookSecret();
        if ($secret === '') {
            $secret = bin2hex(random_bytes(32));
            $this->set('webhook_secret', $secret);
        }
        return $secret;
    }

    /** آماده اتصال: توکن ربات ثبت شده باشد. */
    public function ready(): bool
    {
        return $this->token() !== '';
    }
}
