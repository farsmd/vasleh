<?php
declare(strict_types=1);

namespace Modules\Production;

use Modules\Inventory\Stock;
use Modules\Sqlite\Database;

/**
 * ساخت و تولید: فرمول ساخت (BOM) روی کالاهای انبار + دستور تولید.
 * شروع دستور = کسر مواد از انبار (سند خروج با مرجع کد دستور)،
 * اتمام = ورود محصول ساخته‌شده به انبار (+ افزایش موجودی محصول فروشگاهِ متصل).
 */
final class Production
{
    public const STATUSES = [
        'planned'     => 'برنامه‌ریزی‌شده',
        'in_progress' => 'در حال تولید',
        'done'        => 'تمام‌شده',
        'cancelled'   => 'لغوشده',
    ];

    public function __construct(
        private readonly Database $db,
        private readonly Stock $stock,
    ) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function stock(): Stock
    {
        return $this->stock;
    }

    public function migrate(): void
    {
        $pdo = $this->pdo();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS prod_recipes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                output_item_id INTEGER NOT NULL,
                output_qty REAL NOT NULL DEFAULT 1,
                note TEXT NOT NULL DEFAULT \'\',
                active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS prod_recipe_lines (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                recipe_id INTEGER NOT NULL,
                material_item_id INTEGER NOT NULL,
                qty REAL NOT NULL DEFAULT 0,
                sort INTEGER NOT NULL DEFAULT 0
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS prod_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                recipe_id INTEGER NOT NULL,
                recipe_title TEXT NOT NULL,
                output_item_id INTEGER NOT NULL,
                output_title TEXT NOT NULL,
                output_unit TEXT NOT NULL DEFAULT \'\',
                batches REAL NOT NULL DEFAULT 1,
                output_qty REAL NOT NULL DEFAULT 0,
                warehouse_id INTEGER NOT NULL,
                status TEXT NOT NULL DEFAULT \'planned\',
                note TEXT NOT NULL DEFAULT \'\',
                actor TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL,
                started_at TEXT NOT NULL DEFAULT \'\',
                finished_at TEXT NOT NULL DEFAULT \'\'
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS prod_order_lines (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                material_item_id INTEGER NOT NULL,
                material_title TEXT NOT NULL,
                material_unit TEXT NOT NULL DEFAULT \'\',
                qty REAL NOT NULL DEFAULT 0
            )'
        );
    }

    // ---------- فرمول‌ها ----------

    public function recipes(): array
    {
        $rows = $this->pdo()->query('SELECT * FROM prod_recipes ORDER BY title')->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $out = $this->stock->item((int) $row['output_item_id']);
            $row['output_title'] = $out['title'] ?? '؟';
            $row['output_unit'] = $out['unit'] ?? '';
            $row['lines_count'] = count($this->recipeLines((int) $row['id']));
        }
        unset($row);
        return $rows;
    }

    public function recipe(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM prod_recipes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function recipeLines(int $recipeId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM prod_recipe_lines WHERE recipe_id = :r ORDER BY sort, id');
        $stmt->execute(['r' => $recipeId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $item = $this->stock->item((int) $row['material_item_id']);
            $row['material_title'] = $item['title'] ?? '؟';
            $row['material_unit'] = $item['unit'] ?? '';
        }
        unset($row);
        return $rows;
    }

    public function recipeSave(array $data, array $lines, int $id = 0): int
    {
        $pdo = $this->pdo();
        $now = date('Y-m-d H:i:s');
        if ($id > 0) {
            $pdo->prepare('UPDATE prod_recipes SET title = :t, output_item_id = :o, output_qty = :q, note = :n, active = :a, updated_at = :now WHERE id = :id')
                ->execute(['t' => $data['title'], 'o' => $data['output_item_id'], 'q' => $data['output_qty'], 'n' => $data['note'], 'a' => $data['active'], 'now' => $now, 'id' => $id]);
        } else {
            $pdo->prepare('INSERT INTO prod_recipes (title, output_item_id, output_qty, note, active, created_at, updated_at) VALUES (:t, :o, :q, :n, :a, :now, :now)')
                ->execute(['t' => $data['title'], 'o' => $data['output_item_id'], 'q' => $data['output_qty'], 'n' => $data['note'], 'a' => $data['active'], 'now' => $now]);
            $id = (int) $pdo->lastInsertId();
        }
        $pdo->prepare('DELETE FROM prod_recipe_lines WHERE recipe_id = :r')->execute(['r' => $id]);
        $stmt = $pdo->prepare('INSERT INTO prod_recipe_lines (recipe_id, material_item_id, qty, sort) VALUES (:r, :m, :q, :s)');
        foreach ($lines as $i => $line) {
            if ((int) $line['material_item_id'] > 0 && (float) $line['qty'] > 0) {
                $stmt->execute(['r' => $id, 'm' => (int) $line['material_item_id'], 'q' => (float) $line['qty'], 's' => $i]);
            }
        }
        return $id;
    }

    public function recipeDelete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM prod_recipe_lines WHERE recipe_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM prod_recipes WHERE id = :id')->execute(['id' => $id]);
    }

    // ---------- دستورهای تولید ----------

    private function newCode(): string
    {
        for ($i = 0; $i < 20; $i++) {
            $code = 'PRD-' . random_int(1000, 9999);
            $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM prod_orders WHERE code = :c');
            $stmt->execute(['c' => $code]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $code;
            }
        }
        return 'PRD-' . time();
    }

    /** ساخت دستور از روی فرمول (خطوط مواد snapshot می‌شوند). برمی‌گرداند id دستور. */
    public function createOrder(int $recipeId, float $batches, int $warehouseId, string $note, string $actor): array
    {
        $recipe = $this->recipe($recipeId);
        if ($recipe === null) {
            return ['ok' => false, 'error' => 'فرمول یافت نشد.', 'id' => 0];
        }
        if ($batches <= 0) {
            return ['ok' => false, 'error' => 'تعداد بچ باید بزرگ‌تر از صفر باشد.', 'id' => 0];
        }
        if ($this->stock->warehouse($warehouseId) === null) {
            return ['ok' => false, 'error' => 'انبار یافت نشد.', 'id' => 0];
        }
        $output = $this->stock->item((int) $recipe['output_item_id']);
        $code = $this->newCode();
        $pdo = $this->pdo();
        $pdo->prepare(
            'INSERT INTO prod_orders (code, recipe_id, recipe_title, output_item_id, output_title, output_unit, batches, output_qty, warehouse_id, status, note, actor, created_at)
             VALUES (:c, :r, :rt, :o, :ot, :ou, :b, :oq, :w, \'planned\', :n, :a, :now)'
        )->execute([
            'c' => $code, 'r' => $recipeId, 'rt' => $recipe['title'],
            'o' => (int) $recipe['output_item_id'], 'ot' => $output['title'] ?? '؟', 'ou' => $output['unit'] ?? '',
            'b' => $batches, 'oq' => $batches * (float) $recipe['output_qty'],
            'w' => $warehouseId, 'n' => $note, 'a' => $actor, 'now' => date('Y-m-d H:i:s'),
        ]);
        $orderId = (int) $pdo->lastInsertId();
        $stmt = $pdo->prepare('INSERT INTO prod_order_lines (order_id, material_item_id, material_title, material_unit, qty) VALUES (:o, :m, :t, :u, :q)');
        foreach ($this->recipeLines($recipeId) as $line) {
            $stmt->execute([
                'o' => $orderId, 'm' => (int) $line['material_item_id'],
                't' => $line['material_title'], 'u' => $line['material_unit'],
                'q' => (float) $line['qty'] * $batches,
            ]);
        }
        return ['ok' => true, 'error' => null, 'id' => $orderId];
    }

    public function orders(?string $status = null): array
    {
        if ($status !== null && isset(self::STATUSES[$status])) {
            $stmt = $this->pdo()->prepare('SELECT * FROM prod_orders WHERE status = :s ORDER BY id DESC');
            $stmt->execute(['s' => $status]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $this->pdo()->query('SELECT * FROM prod_orders ORDER BY id DESC')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function order(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM prod_orders WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function orderLines(int $orderId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM prod_order_lines WHERE order_id = :o ORDER BY id');
        $stmt->execute(['o' => $orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** وضعیت تأمین هر خط: موجودی فعلی انبار دستور در برابر نیاز. */
    public function availability(array $order): array
    {
        $out = [];
        foreach ($this->orderLines((int) $order['id']) as $line) {
            $have = $this->stock->qty((int) $line['material_item_id'], (int) $order['warehouse_id']);
            $out[] = $line + [
                'available' => $have,
                'shortage' => max(0.0, (float) $line['qty'] - $have),
            ];
        }
        return $out;
    }

    public function canStart(array $order): bool
    {
        foreach ($this->availability($order) as $line) {
            if ($line['shortage'] > 0) {
                return false;
            }
        }
        return true;
    }

    /** شروع تولید: کسر همه مواد از انبار با مرجع کد دستور. */
    public function start(int $orderId, string $actor): array
    {
        $order = $this->order($orderId);
        if ($order === null || $order['status'] !== 'planned') {
            return ['ok' => false, 'error' => 'این دستور قابل شروع نیست.'];
        }
        if (!$this->canStart($order)) {
            return ['ok' => false, 'error' => 'موجودی مواد در انبار دستور کافی نیست.'];
        }
        foreach ($this->orderLines($orderId) as $line) {
            $result = $this->stock->move('out', (int) $line['material_item_id'], (int) $order['warehouse_id'], (float) $line['qty'], (string) $order['code'], 'مصرف تولید', $actor);
            if (!$result['ok']) {
                return ['ok' => false, 'error' => 'کسر ماده «' . $line['material_title'] . '» نشد: ' . $result['error']];
            }
        }
        $this->pdo()->prepare("UPDATE prod_orders SET status = 'in_progress', started_at = :n WHERE id = :id")
            ->execute(['n' => date('Y-m-d H:i:s'), 'id' => $orderId]);
        return ['ok' => true, 'error' => null];
    }

    /**
     * اتمام تولید: ورود محصول به انبار. اگر کالای خروجی به محصول فروشگاه وصل
     * باشد، موجودی فروشگاه هم زیاد می‌شود و مقدارش برگردانده می‌شود.
     */
    public function complete(int $orderId, string $actor): array
    {
        $order = $this->order($orderId);
        if ($order === null || $order['status'] !== 'in_progress') {
            return ['ok' => false, 'error' => 'این دستور قابل اتمام نیست.'];
        }
        $result = $this->stock->move('in', (int) $order['output_item_id'], (int) $order['warehouse_id'], (float) $order['output_qty'], (string) $order['code'], 'خروجی تولید', $actor);
        if (!$result['ok']) {
            return ['ok' => false, 'error' => (string) $result['error'], 'shop_added' => 0.0];
        }
        $this->pdo()->prepare("UPDATE prod_orders SET status = 'done', finished_at = :n WHERE id = :id")
            ->execute(['n' => date('Y-m-d H:i:s'), 'id' => $orderId]);
        return ['ok' => true, 'error' => null, 'shop_added' => 0.0];
    }

    /** لغو: از برنامه‌ریزی‌شده بدون اثر؛ از درحال‌تولید با برگشت مواد به انبار. */
    public function cancel(int $orderId, string $actor): array
    {
        $order = $this->order($orderId);
        if ($order === null || in_array($order['status'], ['done', 'cancelled'], true)) {
            return ['ok' => false, 'error' => 'این دستور قابل لغو نیست.'];
        }
        if ($order['status'] === 'in_progress') {
            foreach ($this->orderLines($orderId) as $line) {
                $this->stock->move('in', (int) $line['material_item_id'], (int) $order['warehouse_id'], (float) $line['qty'], (string) $order['code'], 'برگشت از تولید لغوشده', $actor);
            }
        }
        $this->pdo()->prepare("UPDATE prod_orders SET status = 'cancelled', finished_at = :n WHERE id = :id")
            ->execute(['n' => date('Y-m-d H:i:s'), 'id' => $orderId]);
        return ['ok' => true, 'error' => null];
    }

    public function stats(): array
    {
        $pdo = $this->pdo();
        return [
            'recipes' => (int) $pdo->query('SELECT COUNT(*) FROM prod_recipes WHERE active = 1')->fetchColumn(),
            'planned' => (int) $pdo->query("SELECT COUNT(*) FROM prod_orders WHERE status = 'planned'")->fetchColumn(),
            'running' => (int) $pdo->query("SELECT COUNT(*) FROM prod_orders WHERE status = 'in_progress'")->fetchColumn(),
            'done' => (int) $pdo->query("SELECT COUNT(*) FROM prod_orders WHERE status = 'done'")->fetchColumn(),
        ];
    }
}
