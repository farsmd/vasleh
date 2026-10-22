<?php
declare(strict_types=1);

namespace Modules\Cms;

use Modules\Sqlite\Database;

/** تنظیمات ماژول محتوا (کلید/مقدار) — جدا از تنظیمات سراسری سیستم. */
final class Settings
{
    public function __construct(private readonly Database $db) {}

    public function migrate(): void
    {
        $this->db->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS cms_settings (key TEXT PRIMARY KEY, value TEXT NOT NULL DEFAULT \'\')'
        );
    }

    public function get(string $key, string $default = ''): string
    {
        $stmt = $this->db->pdo()->prepare('SELECT value FROM cms_settings WHERE key = :key');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : (string) $value;
    }

    public function set(string $key, string $value): void
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO cms_settings (key, value) VALUES (:key, :value)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value'
        );
        $stmt->execute(['key' => $key, 'value' => $value]);
    }
}
