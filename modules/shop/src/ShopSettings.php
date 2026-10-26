<?php
declare(strict_types=1);

namespace Modules\Shop;

use Modules\Sqlite\Database;

/** تنظیمات کلید/مقدار فروشگاه (عنوان، هزینه ارسال، مالیات و…). */
final class ShopSettings
{
    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $this->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS shop_settings (
                name TEXT PRIMARY KEY,
                value TEXT NOT NULL DEFAULT \'\'
            )'
        );
    }

    public function seedDefaults(): void
    {
        $defaults = [
            'shop_title'       => 'فروشگاه وصله',
            'currency'         => 'تومان',
            'shipping_cost'    => '0',
            'free_shipping_over' => '0',
            'tax_percent'      => '0',
            'order_note'       => '',
            'shop_accent'      => '#4f46e5',
            'gift_wrap_price'  => '0',
            'delivery_slots'   => '',
            'payment_methods'  => "online:پرداخت اینترنتی\ncod:پرداخت در محل",
        ];
        $stmt = $this->pdo()->prepare('INSERT OR IGNORE INTO shop_settings (name, value) VALUES (:n, :v)');
        foreach ($defaults as $name => $value) {
            $stmt->execute(['n' => $name, 'v' => $value]);
        }
    }

    public function get(string $name, string $default = ''): string
    {
        $stmt = $this->pdo()->prepare('SELECT value FROM shop_settings WHERE name = :n');
        $stmt->execute(['n' => $name]);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : (string) $value;
    }

    public function getInt(string $name, int $default = 0): int
    {
        $value = $this->get($name, '');
        return $value === '' ? $default : (int) $value;
    }

    public function set(string $name, string $value): void
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO shop_settings (name, value) VALUES (:n, :v)
             ON CONFLICT(name) DO UPDATE SET value = excluded.value'
        );
        $stmt->execute(['n' => $name, 'v' => $value]);
    }
}
