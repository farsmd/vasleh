<?php
declare(strict_types=1);

namespace Modules\Priceextract;

use Modules\Sqlite\Database;

/**
 * استخراج قیمت: منابع (URL + نوع آداپتور)، آیتم‌های استخراج‌شده
 * و اسنپ‌شات هر اجرا. آداپتورها ورودی خام (JSON یا HTML) را به ردیف‌های
 * یک‌شکل تبدیل می‌کنند: title, code, price (تومان، عدد صحیح), category.
 */
final class Extractor
{
    public const ADAPTERS = [
        'json'  => 'JSON API (نگاشت فیلدها)',
        'table' => 'جدول HTML (ستون‌های نام/کد/قیمت)',
    ];

    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $pdo = $this->pdo();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS px_sources (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                url TEXT NOT NULL,
                adapter TEXT NOT NULL DEFAULT \'json\',
                mapping TEXT NOT NULL DEFAULT \'\',
                active INTEGER NOT NULL DEFAULT 1,
                last_run TEXT NOT NULL DEFAULT \'\',
                last_count INTEGER NOT NULL DEFAULT 0,
                last_error TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS px_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                source_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                code TEXT NOT NULL DEFAULT \'\',
                price INTEGER NOT NULL DEFAULT 0,
                category TEXT NOT NULL DEFAULT \'\',
                fetched_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS px_runs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                source_id INTEGER NOT NULL,
                items INTEGER NOT NULL DEFAULT 0,
                error TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );
    }

    // ---------- منابع ----------

    public function sources(): array
    {
        return $this->pdo()->query('SELECT * FROM px_sources ORDER BY id')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function source(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM px_sources WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function sourceSave(array $data, int $id = 0): int
    {
        if ($id > 0) {
            $stmt = $this->pdo()->prepare('UPDATE px_sources SET title = :t, url = :u, adapter = :a, mapping = :m, active = :act WHERE id = :id');
            $stmt->execute(['t' => $data['title'], 'u' => $data['url'], 'a' => $data['adapter'], 'm' => $data['mapping'], 'act' => $data['active'], 'id' => $id]);
            return $id;
        }
        $stmt = $this->pdo()->prepare('INSERT INTO px_sources (title, url, adapter, mapping, active, created_at) VALUES (:t, :u, :a, :m, :act, :now)');
        $stmt->execute(['t' => $data['title'], 'u' => $data['url'], 'a' => $data['adapter'], 'm' => $data['mapping'], 'act' => $data['active'], 'now' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function sourceDelete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM px_items WHERE source_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM px_runs WHERE source_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM px_sources WHERE id = :id')->execute(['id' => $id]);
    }

    // ---------- دریافت ----------

    /** دریافت خام یک URL با تایم‌اوت؛ برمی‌گرداند [body|null, error|null]. */
    public function fetchUrl(string $url): array
    {
        if (!preg_match('#^https?://#i', $url)) {
            return [null, 'آدرس باید با http:// یا https:// شروع شود.'];
        }
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 60,
                'header' => "User-Agent: VaslehPriceExtract/1.0\r\nAccept: */*\r\n",
                'ignore_errors' => true,
            ],
            'ssl' => ['verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            return [null, 'دریافت آدرس ممکن نشد (شبکه یا تایم‌اوت).'];
        }
        return [$body, null];
    }

    /**
     * اجرای استخراج برای یک منبع: دریافت + آداپتور + جایگزینی آیتم‌ها + ثبت اجرا.
     * @return array{ok: bool, count: int, error: ?string}
     */
    public function run(int $sourceId): array
    {
        $source = $this->source($sourceId);
        if ($source === null) {
            return ['ok' => false, 'count' => 0, 'error' => 'منبع یافت نشد.'];
        }
        [$body, $fetchError] = $this->fetchUrl((string) $source['url']);
        $now = date('Y-m-d H:i:s');
        if ($body === null) {
            $this->recordRun($sourceId, 0, (string) $fetchError);
            $this->pdo()->prepare('UPDATE px_sources SET last_run = :n, last_error = :e WHERE id = :id')
                ->execute(['n' => $now, 'e' => (string) $fetchError, 'id' => $sourceId]);
            return ['ok' => false, 'count' => 0, 'error' => (string) $fetchError];
        }
        $mapping = [];
        if (trim((string) $source['mapping']) !== '') {
            $decoded = json_decode((string) $source['mapping'], true);
            if (is_array($decoded)) {
                $mapping = $decoded;
            }
        }
        $items = $source['adapter'] === 'table'
            ? self::extractFromTables($body)
            : self::extractFromJson($body, $mapping);
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM px_items WHERE source_id = :id')->execute(['id' => $sourceId]);
        $stmt = $pdo->prepare('INSERT INTO px_items (source_id, title, code, price, category, fetched_at) VALUES (:s, :t, :c, :p, :cat, :now)');
        foreach ($items as $item) {
            $stmt->execute([
                's' => $sourceId, 't' => $item['title'], 'c' => $item['code'],
                'p' => $item['price'], 'cat' => $item['category'], 'now' => $now,
            ]);
        }
        $this->recordRun($sourceId, count($items), '');
        $this->pdo()->prepare('UPDATE px_sources SET last_run = :n, last_count = :cnt, last_error = \'\' WHERE id = :id')
            ->execute(['n' => $now, 'cnt' => count($items), 'id' => $sourceId]);
        return ['ok' => true, 'count' => count($items), 'error' => null];
    }

    private function recordRun(int $sourceId, int $count, string $error): void
    {
        $this->pdo()->prepare('INSERT INTO px_runs (source_id, items, error, created_at) VALUES (:s, :c, :e, :now)')
            ->execute(['s' => $sourceId, 'c' => $count, 'e' => $error, 'now' => date('Y-m-d H:i:s')]);
    }

    // ---------- آیتم‌ها ----------

    /** فهرست آیتم‌ها با فیلتر اختیاری: source_id, category, q (جست‌وجو در عنوان/کد). */
    public function items(array $filters = [], int $limit = 1000): array
    {
        $sql = 'SELECT i.*, s.title AS source_title FROM px_items i JOIN px_sources s ON s.id = i.source_id';
        $where = [];
        $args = [];
        if (!empty($filters['source_id'])) {
            $where[] = 'i.source_id = :s';
            $args['s'] = (int) $filters['source_id'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'i.category = :cat';
            $args['cat'] = $filters['category'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(i.title LIKE :q OR i.code LIKE :q)';
            $args['q'] = '%' . $filters['q'] . '%';
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY i.category, i.title, i.id LIMIT ' . max(1, $limit);
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function categories(int $sourceId): array
    {
        $stmt = $this->pdo()->prepare('SELECT DISTINCT category FROM px_items WHERE source_id = :s AND category != \'\' ORDER BY category');
        $stmt->execute(['s' => $sourceId]);
        return array_column($stmt->fetchAll(\PDO::FETCH_ASSOC), 'category');
    }

    public function runs(int $sourceId, int $limit = 20): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM px_runs WHERE source_id = :s ORDER BY id DESC LIMIT ' . max(1, $limit));
        $stmt->execute(['s' => $sourceId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * وارد کردن آیتم‌ها از CSV: هر سطر «دسته،نام،کد،قیمت» (سطر سرستون خودکار رد می‌شود).
     * آیتم‌های قبلی همان منبع جایگزین می‌شوند. برمی‌گرداند تعداد واردشده.
     */
    public function importCsv(string $csv, int $sourceId): int
    {
        $lines = preg_split('/\r\n|\r|\n/', $csv) ?: [];
        $count = 0;
        $now = date('Y-m-d H:i:s');
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM px_items WHERE source_id = :id')->execute(['id' => $sourceId]);
        $stmt = $pdo->prepare('INSERT INTO px_items (source_id, title, code, price, category, fetched_at) VALUES (:s, :t, :c, :p, :cat, :now)');
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $fields = str_getcsv($line);
            if (count($fields) < 4) {
                continue;
            }
            [$category, $title, $code, $priceRaw] = array_map('trim', $fields);
            $price = self::toPrice($priceRaw);
            if ($title === '' || ($price <= 0 && $priceRaw !== '0')) {
                continue; // سطر سرستون یا خراب
            }
            $stmt->execute(['s' => $sourceId, 't' => $title, 'c' => $code, 'p' => $price, 'cat' => $category, 'now' => $now]);
            $count++;
        }
        $pdo->prepare('UPDATE px_sources SET last_run = :n, last_count = :cnt, last_error = \'\' WHERE id = :id')
            ->execute(['n' => $now, 'cnt' => $count, 'id' => $sourceId]);
        $this->recordRun($sourceId, $count, 'وارد شده از CSV');
        return $count;
    }

    public function stats(): array
    {
        $pdo = $this->pdo();
        return [
            'sources' => (int) $pdo->query('SELECT COUNT(*) FROM px_sources')->fetchColumn(),
            'items' => (int) $pdo->query('SELECT COUNT(*) FROM px_items')->fetchColumn(),
            'runs' => (int) $pdo->query('SELECT COUNT(*) FROM px_runs')->fetchColumn(),
        ];
    }

    // ---------- آداپتورها (خالص و تست‌پذیر) ----------

    /**
     * استخراج از JSON: mapping = ['rows' => 'data.items' (مسیر نقطه‌ای تا آرایه ردیف‌ها),
     * 'title' => 'name', 'code' => 'code', 'price' => 'price', 'category' => 'category'].
     * قیمت‌ها به عدد صحیح تومان تبدیل می‌شوند (جداکننده‌ها و «تومان» حذف می‌شود).
     * @return array<int, array{title: string, code: string, price: int, category: string}>
     */
    public static function extractFromJson(string $body, array $mapping): array
    {
        $data = json_decode($body, true);
        if (!is_array($data)) {
            return [];
        }
        $rows = self::dig($data, (string) ($mapping['rows'] ?? ''));
        if (!is_array($rows)) {
            // اگر ریشه خودش آرایه ردیف‌هاست
            $rows = array_is_list($data) ? $data : [];
        }
        $out = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = trim((string) self::dig($row, (string) ($mapping['title'] ?? 'title')));
            if ($title === '') {
                continue;
            }
            $out[] = [
                'title' => $title,
                'code' => trim((string) self::dig($row, (string) ($mapping['code'] ?? 'code'))),
                'price' => self::toPrice(self::dig($row, (string) ($mapping['price'] ?? 'price'))),
                'category' => trim((string) self::dig($row, (string) ($mapping['category'] ?? 'category'))),
            ];
        }
        return $out;
    }

    /**
     * استخراج از جدول‌های HTML: هر سطر جدول که دست‌کم ۳ سلول داشته باشد و
     * سلول آخرش عدد قیمت باشد، یک آیتم است (نام، کد، قیمت).
     * عمداً بدون افزونه DOM نوشته شده تا روی هاست اشتراکی هم کار کند
     * (جدول‌های قیمت معمولاً تخت‌اند؛ جدول تودرتو پشتیبانی نمی‌شود).
     * @return array<int, array{title: string, code: string, price: int, category: string}>
     */
    public static function extractFromTables(string $html): array
    {
        $out = [];
        $html = (string) preg_replace('#<(script|style)[^>]*>.*?</\1>#is', '', $html);
        if (!preg_match_all('#<tr[^>]*>(.*?)</tr>#is', $html, $rows)) {
            return $out;
        }
        foreach ($rows[1] as $rowHtml) {
            if (!preg_match_all('#<t[dh][^>]*>(.*?)</t[dh]>#is', $rowHtml, $cells)) {
                continue;
            }
            $cells = array_map(static function (string $cell): string {
                $cell = (string) preg_replace('#<[^>]+>#', ' ', $cell);
                return trim(html_entity_decode($cell, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }, $cells[1]);
            if (count($cells) < 3) {
                continue;
            }
            $last = end($cells);
            $price = self::toPrice($last);
            if ($price <= 0 && $last !== '0') {
                continue; // سطر سرستون یا بدون قیمت
            }
            $out[] = [
                'title' => $cells[0],
                'code' => $cells[1],
                'price' => $price,
                'category' => '',
            ];
        }
        return $out;
    }

    /** خواندن مسیر نقطه‌ای از آرایه (مثل data.items.0.name)؛ مسیر خالی = کل ورودی. */
    private static function dig(mixed $data, string $path): mixed
    {
        if ($path === '') {
            return $data;
        }
        $node = $data;
        foreach (explode('.', $path) as $seg) {
            if (is_array($node) && array_key_exists($seg, $node)) {
                $node = $node[$seg];
            } else {
                return null;
            }
        }
        return $node;
    }

    /** تبدیل «18,000,000 تومان» / «۱۸۰۰۰۰۰۰» / 18000000 به عدد صحیح. ارقام فارسی هم پشتیبانی می‌شوند. */
    public static function toPrice(mixed $value): int
    {
        if (is_int($value) || is_float($value)) {
            return (int) $value;
        }
        $s = (string) $value;
        $fa = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $en = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $s = str_replace($fa, $en, $s);
        $s = str_replace([',', '٬', '٫', ' '], '', $s);
        $s = preg_replace('/[^\d]/', '', $s) ?? '';
        return $s === '' ? 0 : (int) $s;
    }
}
