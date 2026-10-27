<?php

declare(strict_types=1);

namespace Modules\Shop;

use Modules\Sqlite\Database;

/**
 * حساب مشتری فروشگاه — جدا از کاربران ادمین.
 * شناسه مشتری شماره موبایل است و ورود/ثبت‌نام فقط با کد پیامکی انجام می‌شود
 * (گام ۴۷)؛ متدهای رمزی فعلاً از رابط کاربری حذف شده‌اند ولی در سرویس مانده‌اند.
 * سفارش‌های مهمان به‌صورت خودکار به حساب وصل نمی‌شوند (موبایل به‌تنهایی مالکیت
 * را ثابت نمی‌کند)؛ فقط سفارش‌های ثبت‌شده در حالت ورود، customer_id می‌گیرند.
 */
final class Customers
{
    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $this->pdo()->exec(
            "CREATE TABLE IF NOT EXISTS shop_customers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                phone TEXT NOT NULL UNIQUE,
                pass_hash TEXT NOT NULL,
                address TEXT NOT NULL DEFAULT '',
                created_at TEXT NOT NULL
            )"
        );
    }

    /** مشتریان با آمار سفارش (جمع خرید بدون سفارش‌های لغوشده)؛ q روی نام یا موبایل می‌گردد. */
    public function allWithStats(?string $q = null): array
    {
        $sql = "SELECT c.*, COUNT(o.id) AS orders_count,
                       COALESCE(SUM(CASE WHEN o.status != 'cancelled' THEN o.total ELSE 0 END), 0) AS total_spent,
                       MAX(o.created_at) AS last_order
                FROM shop_customers c LEFT JOIN shop_orders o ON o.customer_id = c.id";
        $params = [];
        if ($q !== null && trim($q) !== '') {
            $sql .= ' WHERE c.name LIKE :q OR c.phone LIKE :q';
            $params['q'] = '%' . trim($q) . '%';
        }
        $sql .= ' GROUP BY c.id ORDER BY c.id DESC';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_customers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function findByPhone(string $phone): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_customers WHERE phone = :p');
        $stmt->execute(['p' => $phone]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public static function validPhone(string $phone): bool
    {
        return (bool) preg_match('/^09[0-9]{9}$/', $phone);
    }

    /** @return array{0: ?array, 1: ?string} مشتری ساخته‌شده + خطا */
    public function register(string $name, string $phone, string $password): array
    {
        $name = trim($name);
        $phone = trim($phone);
        if ($name === '') {
            return [null, 'نام الزامی است.'];
        }
        if (!self::validPhone($phone)) {
            return [null, 'شماره موبایل معتبر نیست (با 09 شروع شود و ۱۱ رقم باشد).'];
        }
        if (strlen($password) < 6) {
            return [null, 'رمز عبور باید حداقل ۶ نویسه باشد.'];
        }
        if ($this->findByPhone($phone) !== null) {
            return [null, 'این شماره موبایل قبلاً ثبت شده است.'];
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO shop_customers (name, phone, pass_hash, address, created_at)
             VALUES (:n, :p, :h, \'\', :now)'
        );
        $stmt->execute([
            'n' => $name, 'p' => $phone,
            'h' => password_hash($password, PASSWORD_DEFAULT),
            'now' => date('Y-m-d H:i:s'),
        ]);
        return [$this->find((int) $this->pdo()->lastInsertId()), null];
    }

    /** @return array{0: ?array, 1: ?string} ثبت‌نام با کد پیامکی — بدون رمز عبور */
    public function registerByOtp(string $name, string $phone): array
    {
        $name = trim($name);
        $phone = trim($phone);
        if ($name === '') {
            return [null, 'نام الزامی است.'];
        }
        if (!self::validPhone($phone)) {
            return [null, 'شماره موبایل معتبر نیست (با 09 شروع شود و ۱۱ رقم باشد).'];
        }
        if ($this->findByPhone($phone) !== null) {
            return [null, 'این شماره موبایل قبلاً ثبت شده است.'];
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO shop_customers (name, phone, pass_hash, address, created_at)
             VALUES (:n, :p, \'\', \'\', :now)'
        );
        $stmt->execute(['n' => $name, 'p' => $phone, 'now' => date('Y-m-d H:i:s')]);
        return [$this->find((int) $this->pdo()->lastInsertId()), null];
    }

    public function verify(string $phone, string $password): ?array
    {
        $customer = $this->findByPhone(trim($phone));
        if ($customer === null) {
            return null;
        }
        return password_verify($password, (string) $customer['pass_hash']) ? $customer : null;
    }

    public function updateProfile(int $id, string $name, string $address): void
    {
        $stmt = $this->pdo()->prepare('UPDATE shop_customers SET name = :n, address = :a WHERE id = :id');
        $stmt->execute(['n' => trim($name), 'a' => trim($address), 'id' => $id]);
    }
}
