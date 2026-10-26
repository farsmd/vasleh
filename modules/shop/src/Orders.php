<?php
declare(strict_types=1);

namespace Modules\Shop;

use Modules\Sqlite\Database;

/** سفارش‌ها و ردیف‌هایشان + کد پیگیری یکتا برای هر سفارش. */
final class Orders
{
    public const STATUSES = [
        'new'        => 'جدید',
        'processing' => 'در حال آماده‌سازی',
        'shipped'    => 'ارسال شده',
        'delivered'  => 'تحویل شده',
        'cancelled'  => 'لغو شده',
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
            'CREATE TABLE IF NOT EXISTS shop_orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                customer_name TEXT NOT NULL,
                phone TEXT NOT NULL,
                email TEXT NOT NULL DEFAULT \'\',
                address TEXT NOT NULL DEFAULT \'\',
                note TEXT NOT NULL DEFAULT \'\',
                subtotal INTEGER NOT NULL DEFAULT 0,
                shipping INTEGER NOT NULL DEFAULT 0,
                tax INTEGER NOT NULL DEFAULT 0,
                total INTEGER NOT NULL DEFAULT 0,
                status TEXT NOT NULL DEFAULT \'new\',
                payment_status TEXT NOT NULL DEFAULT \'unpaid\',
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS shop_order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL DEFAULT 0,
                variant_id INTEGER NOT NULL DEFAULT 0,
                title TEXT NOT NULL,
                options_text TEXT NOT NULL DEFAULT \'\',
                unit_price INTEGER NOT NULL DEFAULT 0,
                qty INTEGER NOT NULL DEFAULT 1,
                line_total INTEGER NOT NULL DEFAULT 0
            )'
        );
    }

    private function newCode(): string
    {
        for ($i = 0; $i < 20; $i++) {
            $code = 'VSL-' . random_int(100000, 999999);
            $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM shop_orders WHERE code = :c');
            $stmt->execute(['c' => $code]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $code;
            }
        }
        return 'VSL-' . time();
    }

    /**
     * ثبت سفارش تازه. $items ردیف‌هایی با کلیدهای
     * product_id, variant_id, title, options_text, unit_price, qty است.
     * برمی‌گرداند: [orderId, code]
     */
    public function create(array $customer, array $items, int $shipping, int $taxPercent): array
    {
        $pdo = $this->pdo();
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (int) $item['unit_price'] * (int) $item['qty'];
        }
        $tax = $taxPercent > 0 ? (int) round($subtotal * $taxPercent / 100) : 0;
        $total = $subtotal + $shipping + $tax;
        $code = $this->newCode();
        $now = date('Y-m-d H:i:s');

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO shop_orders (code, customer_name, phone, email, address, note, subtotal, shipping, tax, total, status, payment_status, created_at, updated_at)
                 VALUES (:code, :name, :phone, :email, :addr, :note, :sub, :ship, :tax, :total, \'new\', \'unpaid\', :now, :now)'
            );
            $stmt->execute([
                'code' => $code, 'name' => $customer['name'], 'phone' => $customer['phone'],
                'email' => $customer['email'] ?? '', 'addr' => $customer['address'] ?? '', 'note' => $customer['note'] ?? '',
                'sub' => $subtotal, 'ship' => $shipping, 'tax' => $tax, 'total' => $total, 'now' => $now,
            ]);
            $orderId = (int) $pdo->lastInsertId();
            $itemStmt = $pdo->prepare(
                'INSERT INTO shop_order_items (order_id, product_id, variant_id, title, options_text, unit_price, qty, line_total)
                 VALUES (:o, :p, :v, :t, :ot, :pr, :q, :lt)'
            );
            foreach ($items as $item) {
                $qty = (int) $item['qty'];
                $price = (int) $item['unit_price'];
                $itemStmt->execute([
                    'o' => $orderId, 'p' => (int) $item['product_id'], 'v' => (int) ($item['variant_id'] ?? 0),
                    't' => $item['title'], 'ot' => $item['options_text'] ?? '', 'pr' => $price,
                    'q' => $qty, 'lt' => $price * $qty,
                ]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        return [$orderId, $code];
    }

    public function all(?string $status = null): array
    {
        if ($status !== null && isset(self::STATUSES[$status])) {
            $stmt = $this->pdo()->prepare('SELECT * FROM shop_orders WHERE status = :s ORDER BY id DESC');
            $stmt->execute(['s' => $status]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $this->pdo()->query('SELECT * FROM shop_orders ORDER BY id DESC')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_orders WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_orders WHERE code = :c');
        $stmt->execute(['c' => $code]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /** پیگیری عمومی: کد + شماره موبایل باید هر دو بخورد. */
    public function track(string $code, string $phone): ?array
    {
        $order = $this->findByCode(trim($code));
        if ($order === null || trim((string) $order['phone']) !== trim($phone)) {
            return null;
        }
        $order['items'] = $this->items((int) $order['id']);
        return $order;
    }

    public function items(int $orderId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_order_items WHERE order_id = :o ORDER BY id');
        $stmt->execute(['o' => $orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function setStatus(int $id, string $status): void
    {
        if (!isset(self::STATUSES[$status])) {
            return;
        }
        $stmt = $this->pdo()->prepare('UPDATE shop_orders SET status = :s, updated_at = :n WHERE id = :id');
        $stmt->execute(['s' => $status, 'n' => date('Y-m-d H:i:s'), 'id' => $id]);
    }

    public function setPayment(int $id, string $paymentStatus): void
    {
        if (!in_array($paymentStatus, ['unpaid', 'paid'], true)) {
            return;
        }
        $stmt = $this->pdo()->prepare('UPDATE shop_orders SET payment_status = :s, updated_at = :n WHERE id = :id');
        $stmt->execute(['s' => $paymentStatus, 'n' => date('Y-m-d H:i:s'), 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM shop_order_items WHERE order_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM shop_orders WHERE id = :id')->execute(['id' => $id]);
    }

    /** جمع فروش سفارش‌های لغونشده + تعداد سفارش‌ها. */
    public function stats(): array
    {
        $pdo = $this->pdo();
        $count = (int) $pdo->query('SELECT COUNT(*) FROM shop_orders')->fetchColumn();
        $new = (int) $pdo->query("SELECT COUNT(*) FROM shop_orders WHERE status = 'new'")->fetchColumn();
        $sum = (int) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM shop_orders WHERE status != 'cancelled'")->fetchColumn();
        return ['orders' => $count, 'new' => $new, 'sales' => $sum];
    }

    /** فروش سفارش‌های لغونشده از تاریخ داده‌شده (Y-m-d H:i:s) به بعد. */
    public function salesSince(string $from): array
    {
        $stmt = $this->pdo()->prepare(
            "SELECT COUNT(*) AS n, COALESCE(SUM(total), 0) AS s FROM shop_orders WHERE status != 'cancelled' AND created_at >= :f"
        );
        $stmt->execute(['f' => $from]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        return ['count' => (int) ($row['n'] ?? 0), 'sum' => (int) ($row['s'] ?? 0)];
    }
}
