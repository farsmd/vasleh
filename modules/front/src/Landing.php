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

    /**
     * فیلدهای محتوای لندینگ (نام توکن => [عنوان نمایشی در پنل، محتوای پیش‌فرض]).
     * قالب با {{نام}} به این فیلدها ارجاع می‌دهد؛ محتوا خام رندر می‌شود
     * (HTML هم مجاز است) چون فقط مالک به مدیریت فیلدها دسترسی دارد.
     */
    public const SEED_FIELDS = [
        'hero_title' => ['عنوان اصلی', 'مدیریت کامل کسب‌وکار،'],
        'hero_highlight' => ['بخش رنگی عنوان', 'به اندازه نیاز شما'],
        'hero_subtitle' => ['زیرعنوان', 'بدون نیاز به سرور گران‌قیمت یا دانش فنی. اشتراک پایه را بردارید و فقط هزینه ماژول‌هایی را بپردازید که به آن‌ها نیاز دارید.'],
        'trial_badge' => ['نشان تست', 'درخواست ۱۵ روز تست رایگان'],
        'base_title' => ['عنوان پلن پایه', 'اشتراک پایه سیستم'],
        'base_desc' => ['توضیح پلن پایه', 'زیرساخت اختصاصی، هسته سبک و ابزارهای روزمره کسب‌وکار'],
        'feat_1' => ['ویژگی پایه ۱', 'اجرا روی هاست اشتراکی، بدون نیاز به سرور اختصاصی'],
        'feat_2' => ['ویژگی پایه ۲', 'ماژول مدیریت شخصی و وظایف (Personal)'],
        'feat_3' => ['ویژگی پایه ۳', 'پنل ادمین فارسی با تم شیشه‌ای'],
        'feat_4' => ['ویژگی پایه ۴', 'ماژول پشتیبان‌گیری خودکار و بازیابی ایمن'],
        'feat_5' => ['ویژگی پایه ۵', 'ماژول گزارش‌ها و آمار بازدیدکنندگان'],
        'feat_6' => ['ویژگی پایه ۶', 'وبلاگ و مدیریت محتوا (CMS پایه)'],
        'trial_note' => ['یادداشت تست', 'در دوره آزمایشی مبلغی پرداخت نمی‌کنید؛ فعال‌سازی بعد از بررسی درخواست انجام می‌شود.'],
        'modules_title' => ['عنوان ماژول‌ها', 'ماژول‌های مورد نیاز را انتخاب کنید'],
        'modules_desc' => ['توضیح ماژول‌ها', 'قابلیت‌ها را به دلخواه روشن یا خاموش کنید؛ هزینه بر اساس مصرف شماست.'],
        'cta_button' => ['متن دکمه', 'ثبت درخواست تست رایگان'],
        'trial_notice' => ['یادداشت زیر دکمه', 'بدون نیاز به کارت بانکی برای تست • لغو در هر زمان'],
        'success_msg' => ['پیام موفقیت', 'درخواست شما ثبت شد؛ برای فعال‌سازی نسخه آزمایشی به‌زودی با شما تماس می‌گیریم.'],
        'err_phone' => ['خطای موبایل', 'شماره تماس معتبر نیست؛ مثل 09123456789 وارد کنید.'],
        'err_name' => ['خطای نام', 'نام را بنویسید.'],
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
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS front_fields (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                title TEXT NOT NULL,
                content TEXT NOT NULL DEFAULT \'\'
            )'
        );
        $seedField = $pdo->prepare('INSERT OR IGNORE INTO front_fields (name, title, content) VALUES (:n, :t, :c)');
        foreach (self::SEED_FIELDS as $name => [$title, $content]) {
            $seedField->execute(['n' => $name, 't' => $title, 'c' => $content]);
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

    // ---------- فیلدهای محتوای لندینگ ----------

    /** نام معتبر برای توکن: حروف (فارسی/لاتین) + رقم + زیرخط، با حرف شروع می‌شود. */
    public static function validFieldName(string $name): bool
    {
        return (bool) preg_match('/^\p{L}[\p{L}0-9_]*$/u', $name) && strlen($name) <= 50;
    }

    public function fields(): array
    {
        return $this->db->pdo()
            ->query('SELECT * FROM front_fields ORDER BY id')
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fieldById(int $id): ?array
    {
        $stmt = $this->db->pdo()->prepare('SELECT * FROM front_fields WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    /** نقشه نام => محتوا برای جایگزینی توکن‌ها. */
    public function fieldMap(): array
    {
        $map = [];
        foreach ($this->fields() as $f) {
            $map[(string) $f['name']] = (string) $f['content'];
        }
        return $map;
    }

    /** ساخت فیلد؛ نام تکراری false برمی‌گرداند. */
    public function fieldCreate(string $name, string $title, string $content): bool
    {
        try {
            $this->db->pdo()->prepare(
                'INSERT INTO front_fields (name, title, content) VALUES (:n, :t, :c)'
            )->execute(['n' => $name, 't' => $title, 'c' => $content]);
        } catch (\PDOException) {
            return false;
        }
        return true;
    }

    /** ویرایش فیلد؛ برخورد نام با فیلد دیگر false برمی‌گرداند. */
    public function fieldUpdate(int $id, string $name, string $title, string $content): bool
    {
        try {
            $this->db->pdo()->prepare(
                'UPDATE front_fields SET name = :n, title = :t, content = :c WHERE id = :id'
            )->execute(['n' => $name, 't' => $title, 'c' => $content, 'id' => $id]);
        } catch (\PDOException) {
            return false;
        }
        return true;
    }

    public function fieldDelete(int $id): void
    {
        $this->db->pdo()->prepare('DELETE FROM front_fields WHERE id = :id')->execute(['id' => $id]);
    }

    /**
     * جایگزینی توکن‌های {{نام}} با محتوای فیلدها — یک مرحله، بدون بازگشت.
     * توکن ناشناخته دست‌نخورده می‌ماند تا خطای تایپی دیده شود.
     */
    public function applyTokens(string $html): string
    {
        $map = $this->fieldMap();
        return (string) preg_replace_callback(
            '/\{\{(\p{L}[\p{L}0-9_]*)\}\}/u',
            static fn(array $m): string => $map[$m[1]] ?? $m[0],
            $html
        );
    }
}
