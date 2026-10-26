<?php
declare(strict_types=1);

namespace Modules\Cms;

use Modules\Sqlite\Database;

/** صفحات خطای سفارشی: مدیر از پنل CMS برای هر کد وضعیت، عنوان و بدنه می‌گذارد. */
final class ErrorPages
{
    /** کدهای استاندارد با عنوان پیش‌فرض فارسی. */
    public const STANDARD = [
        400 => 'درخواست نامعتبر',
        401 => 'نیاز به ورود',
        403 => 'دسترسی محدود است',
        404 => 'صفحه یافت نشد',
        405 => 'روش مجاز نیست',
        500 => 'خطای داخلی سرور',
        502 => 'دروازه نامعتبر',
        503 => 'سرویس در دسترس نیست',
    ];

    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $this->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS cms_error_pages (
                code INTEGER PRIMARY KEY,
                title TEXT NOT NULL,
                body TEXT NOT NULL,
                active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
    }

    /** همه کدها: استانداردها + کدهای دلخواهِ ذخیره‌شده؛ ردیف سفارشی روی پیش‌فرض می‌نشیند. */
    public function all(): array
    {
        $rows = [];
        $stmt = $this->pdo()->query('SELECT * FROM cms_error_pages ORDER BY code');
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $rows[(int) $row['code']] = $row;
        }
        $out = [];
        foreach (self::STANDARD as $code => $title) {
            $row = $rows[$code] ?? null;
            unset($rows[$code]);
            $out[] = [
                'code' => $code,
                'title' => $row['title'] ?? $title,
                'body' => $row['body'] ?? '',
                'active' => $row !== null && (int) $row['active'] === 1,
                'custom' => $row !== null,
            ];
        }
        foreach ($rows as $code => $row) {
            $out[] = [
                'code' => $code,
                'title' => $row['title'],
                'body' => $row['body'],
                'active' => (int) $row['active'] === 1,
                'custom' => true,
            ];
        }
        return $out;
    }

    public function get(int $code): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM cms_error_pages WHERE code = :c');
        $stmt->execute(['c' => $code]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function save(int $code, string $title, string $body, bool $active): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo()->prepare(
            'INSERT INTO cms_error_pages (code, title, body, active, created_at, updated_at) VALUES (:c, :t, :b, :a, :n, :n)
             ON CONFLICT(code) DO UPDATE SET title = :t, body = :b, active = :a, updated_at = :n'
        );
        $stmt->execute(['c' => $code, 't' => $title, 'b' => $body, 'a' => $active ? 1 : 0, 'n' => $now]);
    }

    /** حذف ردیف سفارشی = بازگشت به صفحه پیش‌فرض همان کد. */
    public function delete(int $code): void
    {
        $this->pdo()->prepare('DELETE FROM cms_error_pages WHERE code = :c')->execute(['c' => $code]);
    }

    /** بدنه پیشنهادی فرم ویرایش برای کدی که هنوز سفارشی نشده است. */
    public static function defaultBody(int $code): string
    {
        $title = self::STANDARD[$code] ?? 'خطا';
        return '<h1>' . $code . '</h1><p>' . $title . ' — متأسفیم، مشکلی پیش آمده است.</p><p><a href="/">بازگشت به خانه</a></p>';
    }

    /**
     * پوسته مستقل صفحه خطا: بدون وابستگی به قالب سایت (حتی در خطای ۵۰۰
     * هم باید رندر شود)؛ بدنه، HTML authored توسط مدیر است، مثل بدنه نوشته‌ها.
     */
    public static function renderShell(int $code, string $title, string $body): string
    {
        $t = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        return '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . $code . ' — ' . $t . '</title>'
            . '<style>body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0f172a;color:#e2e8f0;font-family:Tahoma,system-ui,sans-serif;padding:24px}main{max-width:560px;text-align:center}main h1{font-size:72px;margin:0 0 8px;color:#f87171}main h2{font-size:20px;margin:0 0 16px}main p{line-height:2;color:#cbd5e1}main a{color:#60a5fa}</style>'
            . '</head><body><main><h1>' . $code . '</h1><h2>' . $t . '</h2>' . $body . '</main></body></html>';
    }
}
