<?php
declare(strict_types=1);

namespace Modules\Cms;

use Modules\Sqlite\Database;

/** تنظیمات ماژول محتوا (کلید/مقدار) — جدا از تنظیمات سراسری سیستم. */
final class Settings
{
    /** استایل پایه پیش‌فرض فرانت — از پنل قابل ویرایش است و در /cms.css سرو می‌شود. */
    public const DEFAULT_CSS = <<<'CSS'
* { box-sizing: border-box; }
body { font-family: Tahoma, system-ui, sans-serif; margin: 0; background: #f6f7f9; color: #1f2937; line-height: 1.9; }
.wrap { max-width: 860px; margin: 0 auto; padding: 0 16px; }
.wrap.wide { max-width: 1180px; }
.site-header { background: #1d2327; color: #fff; }
.site-header .wrap { display: flex; align-items: center; gap: 22px; padding-top: 12px; padding-bottom: 12px; flex-wrap: wrap; }
.site-header .brand { font-weight: bold; font-size: 1.15rem; color: #fff; text-decoration: none; }
.cms-menu { list-style: none; display: flex; gap: 4px; margin: 0; padding: 0; flex-wrap: wrap; }
.cms-menu > li { position: relative; }
.cms-menu a { color: #e5e7eb; text-decoration: none; padding: 5px 10px; border-radius: 8px; display: block; font-size: .95rem; }
.cms-menu a:hover { background: #2c3338; color: #fff; }
.cms-submenu { list-style: none; margin: 0; padding: 6px; position: absolute; top: 100%; inset-inline-start: 0; background: #2c3338; border-radius: 10px; min-width: 180px; display: none; z-index: 20; }
.cms-menu > li:hover .cms-submenu { display: block; }
main .post-card, main article.post { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 22px; margin: 22px 0; }
.post-card h2 { margin: 0 0 6px; font-size: 1.2rem; }
.post-card h2 a { color: #1f2937; text-decoration: none; }
.post-card p { margin: 0; color: #4b5563; }
.post-card time, article.post time { color: #9ca3af; font-size: .85rem; }
article.post h1 { margin-top: 0; font-size: 1.6rem; }
.site-footer { background: #1d2327; color: #c3c4c7; margin-top: 30px; }
.site-footer .wrap { padding-top: 18px; padding-bottom: 18px; text-align: center; }
.site-footer .cms-menu { justify-content: center; }
.site-footer a { color: #e5e7eb; }
CSS;

    public function __construct(private readonly Database $db) {}

    public function migrate(): void
    {
        $this->db->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS cms_settings (key TEXT PRIMARY KEY, value TEXT NOT NULL DEFAULT \'\')'
        );
    }

    public function has(string $key): bool
    {
        $stmt = $this->db->pdo()->prepare('SELECT COUNT(*) FROM cms_settings WHERE key = :key');
        $stmt->execute(['key' => $key]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** CSS کلی پیش‌فرض فقط وقتی هنوز هیچ‌وقت تنظیم نشده است. */
    public function seedDefaults(): void
    {
        if (!$this->has('site_css')) {
            $this->set('site_css', self::DEFAULT_CSS);
        }
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
