<?php
declare(strict_types=1);

namespace Modules\Shop;

use Modules\Sqlite\Database;

/**
 * کاتالوگ فروشگاه: دسته‌بندی‌ها، محصولات، فیلدهای سفارشی محصول،
 * ویژگی‌ها (رنگ، سایز…) و واریانت‌ها (ترکیب ویژگی‌ها با قیمت/موجودی جدا).
 */
final class Catalog
{
    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $pdo = $this->pdo();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS shop_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                parent_id INTEGER NOT NULL DEFAULT 0,
                title TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                description TEXT NOT NULL DEFAULT \'\',
                sort INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS shop_products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER NOT NULL DEFAULT 0,
                title TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                sku TEXT NOT NULL DEFAULT \'\',
                short_desc TEXT NOT NULL DEFAULT \'\',
                description TEXT NOT NULL DEFAULT \'\',
                price INTEGER NOT NULL DEFAULT 0,
                sale_price INTEGER NOT NULL DEFAULT 0,
                stock INTEGER NOT NULL DEFAULT 0,
                track_stock INTEGER NOT NULL DEFAULT 1,
                status TEXT NOT NULL DEFAULT \'draft\',
                image TEXT NOT NULL DEFAULT \'\',
                featured INTEGER NOT NULL DEFAULT 0,
                sort INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
        // ستون‌های اضافه‌شده بعد از نصب‌های قدیمی (CREATE TABLE IF NOT EXISTS ستون نمی‌افزاید).
        $cols = $pdo->query('PRAGMA table_info(shop_products)')->fetchAll(\PDO::FETCH_COLUMN, 1);
        if (!in_array('featured', $cols, true)) {
            $pdo->exec('ALTER TABLE shop_products ADD COLUMN featured INTEGER NOT NULL DEFAULT 0');
        }
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS shop_fields (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                fkey TEXT NOT NULL UNIQUE,
                type TEXT NOT NULL DEFAULT \'text\',
                options TEXT NOT NULL DEFAULT \'\',
                required INTEGER NOT NULL DEFAULT 0,
                sort INTEGER NOT NULL DEFAULT 0
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS shop_product_values (
                product_id INTEGER NOT NULL,
                field_id INTEGER NOT NULL,
                value TEXT NOT NULL DEFAULT \'\',
                PRIMARY KEY (product_id, field_id)
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS shop_attributes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                sort INTEGER NOT NULL DEFAULT 0
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS shop_attribute_values (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                attribute_id INTEGER NOT NULL,
                value TEXT NOT NULL,
                sort INTEGER NOT NULL DEFAULT 0
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS shop_variants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                options_json TEXT NOT NULL DEFAULT \'{}\',
                price INTEGER,
                sale_price INTEGER,
                stock INTEGER NOT NULL DEFAULT 0,
                sku TEXT NOT NULL DEFAULT \'\',
                sort INTEGER NOT NULL DEFAULT 0
            )'
        );
    }

    /** داده نمونه حداقلی تا فروشگاه از اول خالی نباشد (همه از پنل قابل حذف‌اند). */
    public function seedIfEmpty(): void
    {
        $pdo = $this->pdo();
        if ((int) $pdo->query('SELECT COUNT(*) FROM shop_categories')->fetchColumn() === 0) {
            $now = date('Y-m-d H:i:s');
            $pdo->prepare('INSERT INTO shop_categories (parent_id, title, slug, description, sort, created_at) VALUES (0, :t, :s, \'\', 0, :n)')
                ->execute(['t' => 'عمومی', 's' => 'general', 'n' => $now]);
        }
        if ((int) $pdo->query('SELECT COUNT(*) FROM shop_attributes')->fetchColumn() === 0) {
            $pdo->prepare('INSERT INTO shop_attributes (title, slug, sort) VALUES (\'رنگ\', \'color\', 0)')->execute();
            $attrId = (int) $pdo->lastInsertId();
            $stmt = $pdo->prepare('INSERT INTO shop_attribute_values (attribute_id, value, sort) VALUES (:a, :v, :s)');
            foreach (['مشکی', 'سفید', 'قرمز', 'آبی'] as $i => $value) {
                $stmt->execute(['a' => $attrId, 'v' => $value, 's' => $i]);
            }
        }
        if ((int) $pdo->query('SELECT COUNT(*) FROM shop_products')->fetchColumn() === 0) {
            $now = date('Y-m-d H:i:s');
            $catId = (int) $pdo->query('SELECT id FROM shop_categories ORDER BY id LIMIT 1')->fetchColumn();
            $pdo->prepare(
                'INSERT INTO shop_products (category_id, title, slug, sku, short_desc, description, price, sale_price, stock, track_stock, status, image, sort, created_at, updated_at)
                 VALUES (:c, :t, :s, \'SAMPLE-1\', :sd, :d, 250000, 0, 10, 1, \'published\', \'\', 0, :n, :n)'
            )->execute([
                'c' => $catId,
                't' => 'محصول نمونه (قابل حذف)',
                's' => 'sample-product',
                'sd' => 'این محصول برای آشنایی با فروشگاه ساخته شده است.',
                'd' => '<p>محصول نمونه وصله — از پنل مدیریت، بخش فروشگاه، می‌توانید آن را ویرایش یا حذف کنید و محصولات واقعی خودتان را بسازید.</p>',
                'n' => $now,
            ]);
        }
    }

    public static function slugify(string $title, string $fallback = 'item'): string
    {
        $slug = trim($title);
        $slug = preg_replace('/\s+/u', '-', $slug) ?? '';
        $slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $slug) ?? '';
        $slug = trim(preg_replace('/-+/u', '-', $slug) ?? '', '-');
        return $slug === '' ? $fallback . '-' . substr(md5($title . microtime()), 0, 6) : $slug;
    }

    public static function effectivePrice(array $row): int
    {
        $sale = (int) ($row['sale_price'] ?? 0);
        return $sale > 0 ? $sale : (int) ($row['price'] ?? 0);
    }

    // ---------- دسته‌بندی‌ها ----------

    public function categories(): array
    {
        return $this->pdo()->query('SELECT * FROM shop_categories ORDER BY sort, id')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function category(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function categoryBySlug(string $slug): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_categories WHERE slug = :s');
        $stmt->execute(['s' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function categorySlugExists(string $slug, int $exceptId = 0): bool
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM shop_categories WHERE slug = :s AND id != :id');
        $stmt->execute(['s' => $slug, 'id' => $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function categorySave(array $data, int $id = 0): int
    {
        if ($id > 0) {
            $stmt = $this->pdo()->prepare(
                'UPDATE shop_categories SET parent_id = :p, title = :t, slug = :s, description = :d, sort = :so WHERE id = :id'
            );
            $stmt->execute(['p' => $data['parent_id'], 't' => $data['title'], 's' => $data['slug'], 'd' => $data['description'], 'so' => $data['sort'], 'id' => $id]);
            return $id;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO shop_categories (parent_id, title, slug, description, sort, created_at) VALUES (:p, :t, :s, :d, :so, :n)'
        );
        $stmt->execute(['p' => $data['parent_id'], 't' => $data['title'], 's' => $data['slug'], 'd' => $data['description'], 'so' => $data['sort'], 'n' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function categoryDelete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('UPDATE shop_categories SET parent_id = 0 WHERE parent_id = :id')->execute(['id' => $id]);
        $pdo->prepare('UPDATE shop_products SET category_id = 0 WHERE category_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM shop_categories WHERE id = :id')->execute(['id' => $id]);
    }

    public function categoryProductCount(int $id): int
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM shop_products WHERE category_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn();
    }

    // ---------- محصولات ----------

    public function productsAdmin(): array
    {
        return $this->pdo()->query('SELECT * FROM shop_products ORDER BY sort, id DESC')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function productsPublished(int $categoryId = 0): array
    {
        if ($categoryId > 0) {
            $stmt = $this->pdo()->prepare("SELECT * FROM shop_products WHERE status = 'published' AND category_id = :c ORDER BY sort, id DESC");
            $stmt->execute(['c' => $categoryId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $this->pdo()->query("SELECT * FROM shop_products WHERE status = 'published' ORDER BY sort, id DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** محصولات ویژه‌ی منتشرشده (پرچم featured از پنل). */
    public function featuredPublished(): array
    {
        return $this->pdo()->query("SELECT * FROM shop_products WHERE status = 'published' AND featured = 1 ORDER BY sort, id DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** جستجوی زنده‌ی محصولات منتشرشده بر اساس عنوان (حداکثر ۶ نتیجه). */
    public function searchPublished(string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }
        $stmt = $this->pdo()->prepare("SELECT * FROM shop_products WHERE status = 'published' AND title LIKE :q ORDER BY sort, id DESC LIMIT 6");
        $stmt->execute(['q' => '%' . $q . '%']);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function product(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function productBySlug(string $slug, bool $publishedOnly = true): ?array
    {
        $sql = 'SELECT * FROM shop_products WHERE slug = :s' . ($publishedOnly ? " AND status = 'published'" : '');
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute(['s' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function productSlugExists(string $slug, int $exceptId = 0): bool
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM shop_products WHERE slug = :s AND id != :id');
        $stmt->execute(['s' => $slug, 'id' => $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function productSave(array $data, int $id = 0): int
    {
        $now = date('Y-m-d H:i:s');
        if ($id > 0) {
            $stmt = $this->pdo()->prepare(
                'UPDATE shop_products SET category_id = :c, title = :t, slug = :s, sku = :sku, short_desc = :sd,
                 description = :d, price = :pr, sale_price = :sp, stock = :st, track_stock = :ts, status = :status,
                 image = :img, featured = :f, sort = :so, updated_at = :n WHERE id = :id'
            );
            $stmt->execute([
                'c' => $data['category_id'], 't' => $data['title'], 's' => $data['slug'], 'sku' => $data['sku'],
                'sd' => $data['short_desc'], 'd' => $data['description'], 'pr' => $data['price'], 'sp' => $data['sale_price'],
                'st' => $data['stock'], 'ts' => $data['track_stock'], 'status' => $data['status'], 'img' => $data['image'],
                'f' => $data['featured'] ?? 0, 'so' => $data['sort'], 'n' => $now, 'id' => $id,
            ]);
            return $id;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO shop_products (category_id, title, slug, sku, short_desc, description, price, sale_price, stock,
             track_stock, status, image, featured, sort, created_at, updated_at)
             VALUES (:c, :t, :s, :sku, :sd, :d, :pr, :sp, :st, :ts, :status, :img, :f, :so, :n, :n)'
        );
        $stmt->execute([
            'c' => $data['category_id'], 't' => $data['title'], 's' => $data['slug'], 'sku' => $data['sku'],
            'sd' => $data['short_desc'], 'd' => $data['description'], 'pr' => $data['price'], 'sp' => $data['sale_price'],
            'st' => $data['stock'], 'ts' => $data['track_stock'], 'status' => $data['status'], 'img' => $data['image'],
            'f' => $data['featured'] ?? 0, 'so' => $data['sort'], 'n' => $now,
        ]);
        return (int) $this->pdo()->lastInsertId();
    }

    /** ویرایش سریع قیمت/موجودی از صفحه لیست قیمت. */
    public function productQuickPrice(int $id, int $price, int $salePrice, int $stock): void
    {
        $stmt = $this->pdo()->prepare('UPDATE shop_products SET price = :pr, sale_price = :sp, stock = :st, updated_at = :n WHERE id = :id');
        $stmt->execute(['pr' => $price, 'sp' => $salePrice, 'st' => $stock, 'n' => date('Y-m-d H:i:s'), 'id' => $id]);
    }

    public function productDelete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM shop_product_values WHERE product_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM shop_variants WHERE product_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM shop_products WHERE id = :id')->execute(['id' => $id]);
    }

    // ---------- فیلدهای سفارشی محصول ----------

    public function fields(): array
    {
        return $this->pdo()->query('SELECT * FROM shop_fields ORDER BY sort, id')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function field(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_fields WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function fieldKeyExists(string $key, int $exceptId = 0): bool
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM shop_fields WHERE fkey = :k AND id != :id');
        $stmt->execute(['k' => $key, 'id' => $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function fieldSave(array $data, int $id = 0): int
    {
        if ($id > 0) {
            $stmt = $this->pdo()->prepare(
                'UPDATE shop_fields SET title = :t, fkey = :k, type = :ty, options = :o, required = :r, sort = :s WHERE id = :id'
            );
            $stmt->execute(['t' => $data['title'], 'k' => $data['fkey'], 'ty' => $data['type'], 'o' => $data['options'], 'r' => $data['required'], 's' => $data['sort'], 'id' => $id]);
            return $id;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO shop_fields (title, fkey, type, options, required, sort) VALUES (:t, :k, :ty, :o, :r, :s)'
        );
        $stmt->execute(['t' => $data['title'], 'k' => $data['fkey'], 'ty' => $data['type'], 'o' => $data['options'], 'r' => $data['required'], 's' => $data['sort']]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function fieldDelete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM shop_product_values WHERE field_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM shop_fields WHERE id = :id')->execute(['id' => $id]);
    }

    /** مقادیر فیلدهای یک محصول: [field_id => value] */
    public function valuesFor(int $productId): array
    {
        $stmt = $this->pdo()->prepare('SELECT field_id, value FROM shop_product_values WHERE product_id = :p');
        $stmt->execute(['p' => $productId]);
        $out = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $out[(int) $row['field_id']] = (string) $row['value'];
        }
        return $out;
    }

    public function saveValues(int $productId, array $values): void
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO shop_product_values (product_id, field_id, value) VALUES (:p, :f, :v)
             ON CONFLICT(product_id, field_id) DO UPDATE SET value = excluded.value'
        );
        foreach ($values as $fieldId => $value) {
            $stmt->execute(['p' => $productId, 'f' => (int) $fieldId, 'v' => (string) $value]);
        }
    }

    // ---------- ویژگی‌ها و واریانت‌ها ----------

    /** ویژگی‌ها همراه مقادیرشان: هر عضو مقدار کلید values دارد. */
    public function attributes(): array
    {
        $attrs = $this->pdo()->query('SELECT * FROM shop_attributes ORDER BY sort, id')->fetchAll(\PDO::FETCH_ASSOC);
        $values = $this->pdo()->query('SELECT * FROM shop_attribute_values ORDER BY sort, id')->fetchAll(\PDO::FETCH_ASSOC);
        $byAttr = [];
        foreach ($values as $value) {
            $byAttr[(int) $value['attribute_id']][] = $value;
        }
        foreach ($attrs as &$attr) {
            $attr['values'] = $byAttr[(int) $attr['id']] ?? [];
        }
        unset($attr);
        return $attrs;
    }

    public function attribute(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_attributes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function attributeSlugExists(string $slug, int $exceptId = 0): bool
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM shop_attributes WHERE slug = :s AND id != :id');
        $stmt->execute(['s' => $slug, 'id' => $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function attributeSave(array $data, int $id = 0): int
    {
        if ($id > 0) {
            $stmt = $this->pdo()->prepare('UPDATE shop_attributes SET title = :t, slug = :s, sort = :so WHERE id = :id');
            $stmt->execute(['t' => $data['title'], 's' => $data['slug'], 'so' => $data['sort'], 'id' => $id]);
            return $id;
        }
        $stmt = $this->pdo()->prepare('INSERT INTO shop_attributes (title, slug, sort) VALUES (:t, :s, :so)');
        $stmt->execute(['t' => $data['title'], 's' => $data['slug'], 'so' => $data['sort']]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function attributeDelete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM shop_attribute_values WHERE attribute_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM shop_attributes WHERE id = :id')->execute(['id' => $id]);
    }

    public function attributeValueAdd(int $attributeId, string $value, int $sort = 0): int
    {
        $stmt = $this->pdo()->prepare('INSERT INTO shop_attribute_values (attribute_id, value, sort) VALUES (:a, :v, :s)');
        $stmt->execute(['a' => $attributeId, 'v' => $value, 's' => $sort]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function attributeValueDelete(int $valueId): void
    {
        $this->pdo()->prepare('DELETE FROM shop_attribute_values WHERE id = :id')->execute(['id' => $valueId]);
    }

    public function variantsFor(int $productId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_variants WHERE product_id = :p ORDER BY sort, id');
        $stmt->execute(['p' => $productId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['options'] = json_decode((string) $row['options_json'], true) ?: [];
        }
        unset($row);
        return $rows;
    }

    public function variant(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_variants WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $row['options'] = json_decode((string) $row['options_json'], true) ?: [];
        return $row;
    }

    /** جایگزینی کامل واریانت‌های یک محصول (ویرایش از فرم محصول). */
    public function replaceVariants(int $productId, array $rows): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM shop_variants WHERE product_id = :p')->execute(['p' => $productId]);
        $stmt = $pdo->prepare(
            'INSERT INTO shop_variants (product_id, options_json, price, sale_price, stock, sku, sort)
             VALUES (:p, :o, :pr, :sp, :st, :sku, :so)'
        );
        foreach ($rows as $i => $row) {
            $stmt->execute([
                'p' => $productId,
                'o' => json_encode($row['options'], JSON_UNESCAPED_UNICODE),
                'pr' => $row['price'],
                'sp' => $row['sale_price'],
                'st' => $row['stock'],
                'sku' => $row['sku'],
                'so' => $i,
            ]);
        }
    }

    /** قیمت نهایی یک واریانت با fallback به قیمت محصول. */
    public static function variantPrice(array $product, array $variant): int
    {
        if ($variant['sale_price'] !== null && (int) $variant['sale_price'] > 0) {
            return (int) $variant['sale_price'];
        }
        if ($variant['price'] !== null && (int) $variant['price'] > 0) {
            return (int) $variant['price'];
        }
        return self::effectivePrice($product);
    }

    /** کاهش موجودی بعد از ثبت سفارش (فقط وقتی ردیابی موجودی فعال است؛ کف صفر). */
    public function decreaseStock(int $productId, int $variantId, int $qty): void
    {
        $product = $this->product($productId);
        if ($product === null || (int) $product['track_stock'] !== 1 || $qty < 1) {
            return;
        }
        if ($variantId > 0) {
            $stmt = $this->pdo()->prepare('UPDATE shop_variants SET stock = MAX(0, stock - :q) WHERE id = :id');
            $stmt->execute(['q' => $qty, 'id' => $variantId]);
            return;
        }
        $stmt = $this->pdo()->prepare('UPDATE shop_products SET stock = MAX(0, stock - :q) WHERE id = :id');
        $stmt->execute(['q' => $qty, 'id' => $productId]);
    }

    /** متن خوانای ترکیب ویژگی‌ها: «رنگ: قرمز، سایز: بزرگ». */
    public static function optionsText(array $options, array $attributesBySlug): string
    {
        $parts = [];
        foreach ($options as $slug => $value) {
            $title = $attributesBySlug[$slug]['title'] ?? (string) $slug;
            $parts[] = $title . ': ' . $value;
        }
        return implode('، ', $parts);
    }

    public function attributesBySlug(): array
    {
        $out = [];
        foreach ($this->attributes() as $attr) {
            $out[(string) $attr['slug']] = $attr;
        }
        return $out;
    }
}
