<?php
declare(strict_types=1);

namespace Modules\Front;

use Modules\Sqlite\Database;

/**
 * لندینگ فروش وصله + سرنخ‌های درخواست تست.
 * قیمت‌ها از تنظیمات خوانده می‌شوند (پیش‌فرض‌ها = قیمت‌های تأییدشده فرشاد،
 * 2026-10-26) و از صفحه مدیریت لندینگ قابل ویرایش‌اند — هیچ قیمتی در
 * قالب هاردکد نشده است. جمع فاکتور همیشه سمت سرور محاسبه می‌شود.
 */
final class Landing
{
    /** ماژول‌های قابل فروش (ترتیب نمایش در لندینگ). */
    public const MODULES = [
        'accounting' => ['title' => 'حسابداری و مالی', 'desc' => 'تراکنش، دفاتر حساب، گزارش سود و زیان و فاکتور'],
        'shop' => ['title' => 'فروشگاه آنلاین', 'desc' => 'درگاه پرداخت، کاتالوگ واریانت‌دار، سبد خرید و رهگیری'],
        'inventory' => ['title' => 'انبارداری چندگانه', 'desc' => 'کاردکس کالا، هشدار کسری، حواله ورود و خروج'],
        'production' => ['title' => 'فرمول تولید (BOM)', 'desc' => 'دستور ساخت، مصرف خودکار مواد اولیه و محاسبه بهای تمام‌شده'],
        'priceextract' => ['title' => 'استخراج و مانیتورینگ قیمت', 'desc' => 'خواندن قیمت رقبا از API و جداول وب'],
    ];

    /** قیمت‌های پیش‌فرض (تومان/ماه) — تأیید فرشاد 2026-10-26. */
    public const DEFAULTS = [
        'base' => 199000,
        'accounting' => 399000,
        'shop' => 299000,
        'inventory' => 249000,
        'production' => 349000,
        'priceextract' => 189000,
    ];

    public function __construct(private readonly Database $db) {}

    public function migrate(): void
    {
        $pdo = $this->db->pdo();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS front_settings (key TEXT PRIMARY KEY, value TEXT NOT NULL DEFAULT \'\')'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS front_leads (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                phone TEXT NOT NULL,
                modules TEXT NOT NULL DEFAULT \'[]\',
                total INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )'
        );
        $seed = $pdo->prepare('INSERT OR IGNORE INTO front_settings (key, value) VALUES (:k, :v)');
        foreach (self::DEFAULTS as $key => $price) {
            $seed->execute(['k' => 'price_' . $key, 'v' => (string) $price]);
        }
    }

    /** قیمت‌های جاری (تنظیمات، وگرنه پیش‌فرض). */
    public function prices(): array
    {
        $rows = $this->db->pdo()->query('SELECT key, value FROM front_settings')->fetchAll(\PDO::FETCH_KEY_PAIR);
        $out = [];
        foreach (self::DEFAULTS as $key => $default) {
            $raw = $rows['price_' . $key] ?? null;
            $out[$key] = is_numeric($raw) && (int) $raw >= 0 ? (int) $raw : $default;
        }
        return $out;
    }

    /** ذخیره قیمت‌ها (فقط کلیدهای شناخته‌شده، عدد صحیح نامنفی). */
    public function setPrices(array $input): void
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO front_settings (key, value) VALUES (:k, :v)
             ON CONFLICT(key) DO UPDATE SET value = excluded.value'
        );
        foreach (self::DEFAULTS as $key => $default) {
            if (!isset($input[$key])) {
                continue;
            }
            $value = (int) $input[$key];
            $stmt->execute(['k' => 'price_' . $key, 'v' => (string) max(0, $value)]);
        }
    }

    /** عدد با ارقام فارسی و جداکننده هزارگان: ۱۹۹٬۰۰۰ */
    public static function faNum(int $n): string
    {
        $digits = ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', ',' => '٬'];
        return strtr(number_format($n), $digits);
    }

    /** نسخه با ارقام فارسی: ۵٫۹٫۱۹ */
    public static function faVersion(string $version): string
    {
        $map = ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', '.' => '٫'];
        return strtr($version, $map);
    }

    // ---------- سرنخ‌ها ----------

    public function leads(): array
    {
        return $this->db->pdo()
            ->query('SELECT * FROM front_leads ORDER BY id DESC')
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function leadCreate(string $name, string $phone, array $modules, int $total): int
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO front_leads (name, phone, modules, total, created_at)
             VALUES (:n, :p, :m, :t, :now)'
        );
        $stmt->execute([
            'n' => $name, 'p' => $phone, 'm' => json_encode(array_values($modules), JSON_UNESCAPED_UNICODE),
            't' => $total, 'now' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->db->pdo()->lastInsertId();
    }

    public function leadDelete(int $id): void
    {
        $this->db->pdo()->prepare('DELETE FROM front_leads WHERE id = :id')->execute(['id' => $id]);
    }
}
