<?php
declare(strict_types=1);

namespace Modules\Inventory;

use Modules\Sqlite\Database;

/**
 * انبارداری: انبارها، کالاها (مواد اولیه/محصول)، موجودی per-warehouse
 * و دفتر گردش (کاردکس) — هر تغییر موجودی یک سند حرکتی دارد.
 * مقدارها REAL اند تا واحدهای اعشاری (متر، کیلو) هم پشتیبانی شوند.
 */
final class Stock
{
    public const TYPES = [
        'in'           => 'ورود',
        'out'          => 'خروج',
        'transfer_out' => 'انتقال (خروج)',
        'transfer_in'  => 'انتقال (ورود)',
        'adjust'       => 'اصلاح موجودی',
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
            'CREATE TABLE IF NOT EXISTS inv_warehouses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                location TEXT NOT NULL DEFAULT \'\',
                is_default INTEGER NOT NULL DEFAULT 0,
                active INTEGER NOT NULL DEFAULT 1,
                sort INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS inv_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                sku TEXT NOT NULL DEFAULT \'\',
                unit TEXT NOT NULL DEFAULT \'عدد\',
                kind TEXT NOT NULL DEFAULT \'material\',
                min_stock REAL NOT NULL DEFAULT 0,
                shop_product_id INTEGER NOT NULL DEFAULT 0,
                note TEXT NOT NULL DEFAULT \'\',
                active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS inv_stock (
                warehouse_id INTEGER NOT NULL,
                item_id INTEGER NOT NULL,
                qty REAL NOT NULL DEFAULT 0,
                PRIMARY KEY (warehouse_id, item_id)
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS inv_movements (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item_id INTEGER NOT NULL,
                warehouse_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                qty REAL NOT NULL,
                balance_after REAL NOT NULL DEFAULT 0,
                unit_price REAL NOT NULL DEFAULT 0,
                ref TEXT NOT NULL DEFAULT \'\',
                note TEXT NOT NULL DEFAULT \'\',
                actor TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );
        $moveCols = $pdo->query('PRAGMA table_info(inv_movements)')->fetchAll(\PDO::FETCH_COLUMN, 1);
        if (!in_array('unit_price', $moveCols, true)) {
            $pdo->exec('ALTER TABLE inv_movements ADD COLUMN unit_price REAL NOT NULL DEFAULT 0');
        }
    }

    public function seedIfEmpty(): void
    {
        if ((int) $this->pdo()->query('SELECT COUNT(*) FROM inv_warehouses')->fetchColumn() === 0) {
            $this->pdo()->prepare('INSERT INTO inv_warehouses (title, location, is_default, active, sort, created_at) VALUES (\'انبار اصلی\', \'\', 1, 1, 0, :n)')
                ->execute(['n' => date('Y-m-d H:i:s')]);
        }
    }

    public static function fmt(float $qty): string
    {
        $s = rtrim(rtrim(number_format($qty, 3, '.', ''), '0'), '.');
        return $s === '' || $s === '-0' ? '0' : $s;
    }

    // ---------- انبارها ----------

    public function warehouses(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM inv_warehouses' . ($activeOnly ? ' WHERE active = 1' : '') . ' ORDER BY sort, id';
        return $this->pdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function warehouse(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM inv_warehouses WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function defaultWarehouse(): ?array
    {
        $row = $this->pdo()->query('SELECT * FROM inv_warehouses WHERE is_default = 1 AND active = 1 ORDER BY id LIMIT 1')->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            $row = $this->pdo()->query('SELECT * FROM inv_warehouses WHERE active = 1 ORDER BY sort, id LIMIT 1')->fetch(\PDO::FETCH_ASSOC);
        }
        return $row === false ? null : $row;
    }

    public function warehouseSave(array $data, int $id = 0): int
    {
        $pdo = $this->pdo();
        if (!empty($data['is_default'])) {
            $pdo->exec('UPDATE inv_warehouses SET is_default = 0');
        }
        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE inv_warehouses SET title = :t, location = :l, is_default = :d, active = :a, sort = :s WHERE id = :id');
            $stmt->execute(['t' => $data['title'], 'l' => $data['location'], 'd' => $data['is_default'], 'a' => $data['active'], 's' => $data['sort'], 'id' => $id]);
            return $id;
        }
        $stmt = $pdo->prepare('INSERT INTO inv_warehouses (title, location, is_default, active, sort, created_at) VALUES (:t, :l, :d, :a, :s, :n)');
        $stmt->execute(['t' => $data['title'], 'l' => $data['location'], 'd' => $data['is_default'], 'a' => $data['active'], 's' => $data['sort'], 'n' => date('Y-m-d H:i:s')]);
        return (int) $pdo->lastInsertId();
    }

    public function warehouseDelete(int $id): bool
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM inv_stock WHERE warehouse_id = :id AND qty != 0');
        $stmt->execute(['id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false; // تا وقتی موجودی دارد حذف نمی‌شود
        }
        $this->pdo()->prepare('DELETE FROM inv_stock WHERE warehouse_id = :id')->execute(['id' => $id]);
        $this->pdo()->prepare('DELETE FROM inv_warehouses WHERE id = :id')->execute(['id' => $id]);
        return true;
    }

    // ---------- کالاها ----------

    public function items(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM inv_items' . ($activeOnly ? ' WHERE active = 1' : '') . ' ORDER BY title';
        return $this->pdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function item(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM inv_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function itemBySku(string $sku): ?array
    {
        if ($sku === '') {
            return null;
        }
        $stmt = $this->pdo()->prepare('SELECT * FROM inv_items WHERE sku = :s');
        $stmt->execute(['s' => $sku]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function itemSave(array $data, int $id = 0): int
    {
        $now = date('Y-m-d H:i:s');
        if ($id > 0) {
            $stmt = $this->pdo()->prepare(
                'UPDATE inv_items SET title = :t, sku = :sku, unit = :u, kind = :k, min_stock = :min, shop_product_id = :sp, note = :note, active = :a, updated_at = :n WHERE id = :id'
            );
            $stmt->execute(['t' => $data['title'], 'sku' => $data['sku'], 'u' => $data['unit'], 'k' => $data['kind'], 'min' => $data['min_stock'], 'sp' => $data['shop_product_id'], 'note' => $data['note'], 'a' => $data['active'], 'n' => $now, 'id' => $id]);
            return $id;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO inv_items (title, sku, unit, kind, min_stock, shop_product_id, note, active, created_at, updated_at)
             VALUES (:t, :sku, :u, :k, :min, :sp, :note, :a, :n, :n)'
        );
        $stmt->execute(['t' => $data['title'], 'sku' => $data['sku'], 'u' => $data['unit'], 'k' => $data['kind'], 'min' => $data['min_stock'], 'sp' => $data['shop_product_id'], 'note' => $data['note'], 'a' => $data['active'], 'n' => $now]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function itemDelete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM inv_stock WHERE item_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM inv_movements WHERE item_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM inv_items WHERE id = :id')->execute(['id' => $id]);
    }

    // ---------- موجودی و حرکت‌ها ----------

    public function qty(int $itemId, int $warehouseId): float
    {
        $stmt = $this->pdo()->prepare('SELECT qty FROM inv_stock WHERE warehouse_id = :w AND item_id = :i');
        $stmt->execute(['w' => $warehouseId, 'i' => $itemId]);
        $value = $stmt->fetchColumn();
        return $value === false ? 0.0 : (float) $value;
    }

    public function totalQty(int $itemId): float
    {
        $stmt = $this->pdo()->prepare('SELECT COALESCE(SUM(qty), 0) FROM inv_stock WHERE item_id = :i');
        $stmt->execute(['i' => $itemId]);
        return (float) $stmt->fetchColumn();
    }

    /** جدول موجودی: هر سطر کالا × انبار با مقدار (فقط ترکیب‌های دارای سابقه یا همه کالاها در انبار منتخب). */
    public function stockTable(int $warehouseId = 0): array
    {
        if ($warehouseId > 0) {
            $stmt = $this->pdo()->prepare(
                'SELECT i.*, COALESCE(s.qty, 0) AS qty, :w AS warehouse_id
                 FROM inv_items i LEFT JOIN inv_stock s ON s.item_id = i.id AND s.warehouse_id = :w
                 ORDER BY i.title'
            );
            $stmt->execute(['w' => $warehouseId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $this->pdo()->query(
            'SELECT i.*, COALESCE(SUM(s.qty), 0) AS qty, 0 AS warehouse_id
             FROM inv_items i LEFT JOIN inv_stock s ON s.item_id = i.id
             GROUP BY i.id ORDER BY i.title'
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** کالاهایی که موجودی کلشان به/زیر حد هشدار رسیده (و حد هشدار > ۰ است). */
    public function lowStock(): array
    {
        return $this->pdo()->query(
            'SELECT i.*, COALESCE(SUM(s.qty), 0) AS qty
             FROM inv_items i LEFT JOIN inv_stock s ON s.item_id = i.id
             WHERE i.active = 1 AND i.min_stock > 0
             GROUP BY i.id HAVING qty <= i.min_stock ORDER BY qty'
        )->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function applyDelta(int $itemId, int $warehouseId, float $delta, string $type, string $ref, string $note, string $actor, float $unitPrice = 0.0): float
    {
        $pdo = $this->pdo();
        $new = $this->qty($itemId, $warehouseId) + $delta;
        $stmt = $pdo->prepare(
            'INSERT INTO inv_stock (warehouse_id, item_id, qty) VALUES (:w, :i, :q)
             ON CONFLICT(warehouse_id, item_id) DO UPDATE SET qty = excluded.qty'
        );
        $stmt->execute(['w' => $warehouseId, 'i' => $itemId, 'q' => $new]);
        $pdo->prepare(
            'INSERT INTO inv_movements (item_id, warehouse_id, type, qty, balance_after, unit_price, ref, note, actor, created_at)
             VALUES (:i, :w, :t, :q, :b, :up, :r, :n, :a, :now)'
        )->execute([
            'i' => $itemId, 'w' => $warehouseId, 't' => $type, 'q' => $delta,
            'b' => $new, 'up' => $unitPrice, 'r' => $ref, 'n' => $note, 'a' => $actor, 'now' => date('Y-m-d H:i:s'),
        ]);
        return $new;
    }

    /** @return array{ok: bool, error: ?string, balance: float} */
    public function move(string $type, int $itemId, int $warehouseId, float $qty, string $ref = '', string $note = '', string $actor = '', float $unitPrice = 0.0): array
    {
        if ($this->item($itemId) === null || $this->warehouse($warehouseId) === null) {
            return ['ok' => false, 'error' => 'کالا یا انبار یافت نشد.', 'balance' => 0.0];
        }
        if ($type === 'in') {
            if ($qty <= 0) {
                return ['ok' => false, 'error' => 'مقدار ورود باید بزرگ‌تر از صفر باشد.', 'balance' => $this->qty($itemId, $warehouseId)];
            }
            return ['ok' => true, 'error' => null, 'balance' => $this->applyDelta($itemId, $warehouseId, $qty, 'in', $ref, $note, $actor, $unitPrice)];
        }
        if ($type === 'out') {
            if ($qty <= 0) {
                return ['ok' => false, 'error' => 'مقدار خروج باید بزرگ‌تر از صفر باشد.', 'balance' => $this->qty($itemId, $warehouseId)];
            }
            if ($this->qty($itemId, $warehouseId) < $qty) {
                return ['ok' => false, 'error' => 'موجودی این انبار کافی نیست.', 'balance' => $this->qty($itemId, $warehouseId)];
            }
            return ['ok' => true, 'error' => null, 'balance' => $this->applyDelta($itemId, $warehouseId, -$qty, 'out', $ref, $note, $actor, $unitPrice)];
        }
        if ($type === 'adjust') {
            // در اصلاح، qty موجودی درستِ نهایی است (شمارش واقعی).
            $current = $this->qty($itemId, $warehouseId);
            return ['ok' => true, 'error' => null, 'balance' => $this->applyDelta($itemId, $warehouseId, $qty - $current, 'adjust', $ref, $note, $actor, $unitPrice)];
        }
        return ['ok' => false, 'error' => 'نوع حرکت نامعتبر است.', 'balance' => 0.0];
    }

    /** @return array{ok: bool, error: ?string} */
    public function transfer(int $itemId, int $fromId, int $toId, float $qty, string $ref = '', string $note = '', string $actor = ''): array
    {
        if ($fromId === $toId) {
            return ['ok' => false, 'error' => 'انبار مبدأ و مقصد یکی است.'];
        }
        if ($qty <= 0) {
            return ['ok' => false, 'error' => 'مقدار انتقال باید بزرگ‌تر از صفر باشد.'];
        }
        if ($this->qty($itemId, $fromId) < $qty) {
            return ['ok' => false, 'error' => 'موجودی انبار مبدأ کافی نیست.'];
        }
        if ($this->item($itemId) === null || $this->warehouse($fromId) === null || $this->warehouse($toId) === null) {
            return ['ok' => false, 'error' => 'کالا یا انبار یافت نشد.'];
        }
        $ref = $ref !== '' ? $ref : 'TR-' . date('His');
        $this->applyDelta($itemId, $fromId, -$qty, 'transfer_out', $ref, $note, $actor);
        $this->applyDelta($itemId, $toId, $qty, 'transfer_in', $ref, $note, $actor);
        return ['ok' => true, 'error' => null];
    }

    /** کاردکس یک کالا (همه انبارها یا یک انبار) — تازه‌ترین اول. */
    public function movements(int $itemId = 0, int $warehouseId = 0, int $limit = 200): array
    {
        $sql = 'SELECT m.*, i.title AS item_title, i.unit AS item_unit, w.title AS warehouse_title
                FROM inv_movements m
                JOIN inv_items i ON i.id = m.item_id
                JOIN inv_warehouses w ON w.id = m.warehouse_id';
        $where = [];
        $args = [];
        if ($itemId > 0) {
            $where[] = 'm.item_id = :i';
            $args['i'] = $itemId;
        }
        if ($warehouseId > 0) {
            $where[] = 'm.warehouse_id = :w';
            $args['w'] = $warehouseId;
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY m.id DESC LIMIT ' . max(1, $limit);
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** ارزش تقریبی موجودی وقتی کالا به محصول فروشگاه وصل است (از قیمت محصول) — فقط برای گزارش. */
    public function stats(): array
    {
        $pdo = $this->pdo();
        return [
            'warehouses' => (int) $pdo->query('SELECT COUNT(*) FROM inv_warehouses WHERE active = 1')->fetchColumn(),
            'items' => (int) $pdo->query('SELECT COUNT(*) FROM inv_items WHERE active = 1')->fetchColumn(),
            'low' => count($this->lowStock()),
            'moves' => (int) $pdo->query('SELECT COUNT(*) FROM inv_movements')->fetchColumn(),
        ];
    }
}
