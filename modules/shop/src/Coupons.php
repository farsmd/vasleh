<?php
declare(strict_types=1);

namespace Modules\Shop;

use Modules\Sqlite\Database;

/** کوپن‌های تخفیف فروشگاه: درصدی یا مبلغ ثابت، با انقضا و سقف مصرف — همه از پنل قابل مدیریت‌اند. */
final class Coupons
{
    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $this->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS shop_coupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT NOT NULL UNIQUE,
                kind TEXT NOT NULL DEFAULT \'percent\',
                value INTEGER NOT NULL DEFAULT 0,
                expires_at TEXT NOT NULL DEFAULT \'\',
                max_uses INTEGER NOT NULL DEFAULT 0,
                used INTEGER NOT NULL DEFAULT 0,
                active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL
            )'
        );
    }

    public function all(): array
    {
        return $this->pdo()->query('SELECT * FROM shop_coupons ORDER BY id DESC')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_coupons WHERE code = :c');
        $stmt->execute(['c' => $code]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /** کوپن معتبر برای این جمع سبد؛ نامعتبر/منقضی/تمام‌شده → null. */
    public function findValid(string $code, int $subtotal): ?array
    {
        $code = trim($code);
        if ($code === '' || $subtotal <= 0) {
            return null;
        }
        $row = $this->findByCode($code);
        if ($row === null || (int) $row['active'] !== 1) {
            return null;
        }
        if ((string) $row['expires_at'] !== '' && (string) $row['expires_at'] < date('Y-m-d')) {
            return null;
        }
        if ((int) $row['max_uses'] > 0 && (int) $row['used'] >= (int) $row['max_uses']) {
            return null;
        }
        return $row;
    }

    /** مبلغ تخفیف کوپن روی جمع سبد (هرگز بیشتر از جمع). */
    public static function discountFor(array $coupon, int $subtotal): int
    {
        if ((string) $coupon['kind'] === 'fixed') {
            return min(max(0, (int) $coupon['value']), $subtotal);
        }
        $pct = max(0, min(100, (int) $coupon['value']));
        return (int) round($subtotal * $pct / 100);
    }

    public function save(array $data, int $id = 0): int
    {
        $now = date('Y-m-d H:i:s');
        if ($id > 0) {
            $stmt = $this->pdo()->prepare(
                'UPDATE shop_coupons SET code = :c, kind = :k, value = :v, expires_at = :e, max_uses = :m, active = :a WHERE id = :id'
            );
            $stmt->execute([
                'c' => $data['code'], 'k' => $data['kind'], 'v' => $data['value'], 'e' => $data['expires_at'],
                'm' => $data['max_uses'], 'a' => $data['active'], 'id' => $id,
            ]);
            return $id;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO shop_coupons (code, kind, value, expires_at, max_uses, used, active, created_at)
             VALUES (:c, :k, :v, :e, :m, 0, :a, :n)'
        );
        $stmt->execute([
            'c' => $data['code'], 'k' => $data['kind'], 'v' => $data['value'], 'e' => $data['expires_at'],
            'm' => $data['max_uses'], 'a' => $data['active'], 'n' => $now,
        ]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function delete(int $id): void
    {
        $this->pdo()->prepare('DELETE FROM shop_coupons WHERE id = :id')->execute(['id' => $id]);
    }

    public function markUsed(string $code): void
    {
        $this->pdo()->prepare('UPDATE shop_coupons SET used = used + 1 WHERE code = :c')->execute(['c' => $code]);
    }
}
