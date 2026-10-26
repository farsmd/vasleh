<?php

declare(strict_types=1);

namespace Modules\Zibal;

use Modules\Sqlite\Database;

/** ذخیره پرداخت‌های زیبال + تنظیمات ماژول (جدول‌های خودش؛ جدول فروشگاه دست نمی‌خورد). */
final class ZibalPayments
{
    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS zibal_payments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            order_code TEXT NOT NULL,
            track_id TEXT NOT NULL DEFAULT '',
            amount_rial INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'pending',
            ref_number TEXT NOT NULL DEFAULT '',
            card_number TEXT NOT NULL DEFAULT '',
            note TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL,
            verified_at TEXT NOT NULL DEFAULT ''
        )");
        $pdo->exec("CREATE TABLE IF NOT EXISTS zibal_settings (
            name TEXT PRIMARY KEY,
            value TEXT NOT NULL DEFAULT ''
        )");
        $stmt = $pdo->prepare('INSERT OR IGNORE INTO zibal_settings (name, value) VALUES (:name, :value)');
        foreach (['merchant' => '', 'test_mode' => '1'] as $name => $value) {
            $stmt->execute(['name' => $name, 'value' => $value]);
        }
    }

    // ---------- تنظیمات ----------

    public function get(string $name, string $default = ''): string
    {
        $stmt = $this->pdo()->prepare('SELECT value FROM zibal_settings WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? (string) $row['value'] : $default;
    }

    public function set(string $name, string $value): void
    {
        $stmt = $this->pdo()->prepare('INSERT INTO zibal_settings (name, value) VALUES (:name, :value)
            ON CONFLICT(name) DO UPDATE SET value = excluded.value');
        $stmt->execute(['name' => $name, 'value' => $value]);
    }

    public function testMode(): bool
    {
        return $this->get('test_mode', '1') === '1';
    }

    /** مرچنت مؤثر: در حالت تست، مرچنت تست رسمی زیبال؛ وگرنه مرچنت ثبت‌شده. */
    public function merchant(): string
    {
        return $this->testMode() ? ZibalClient::TEST_MERCHANT : trim($this->get('merchant', ''));
    }

    /** آماده برای نمایش در تسویه: حالت تست همیشه آماده است؛ حالت واقعی مرچنت می‌خواهد. */
    public function ready(): bool
    {
        return $this->testMode() || $this->merchant() !== '';
    }

    // ---------- پرداخت‌ها ----------

    public function createPending(string $orderCode, int $amountRial): int
    {
        $stmt = $this->pdo()->prepare("INSERT INTO zibal_payments
            (order_code, track_id, amount_rial, status, created_at)
            VALUES (:code, '', :amount, 'pending', :now)");
        $stmt->execute(['code' => $orderCode, 'amount' => $amountRial, 'now' => gmdate('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function setTrack(int $id, string $trackId): void
    {
        $stmt = $this->pdo()->prepare('UPDATE zibal_payments SET track_id = :t WHERE id = :id');
        $stmt->execute(['t' => $trackId, 'id' => $id]);
    }

    public function byTrack(string $trackId): ?array
    {
        if ($trackId === '') {
            return null;
        }
        $stmt = $this->pdo()->prepare('SELECT * FROM zibal_payments WHERE track_id = :t ORDER BY id DESC LIMIT 1');
        $stmt->execute(['t' => $trackId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function byOrder(string $orderCode): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM zibal_payments WHERE order_code = :c ORDER BY id DESC LIMIT 1');
        $stmt->execute(['c' => $orderCode]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function markPaid(int $id, string $refNumber, string $cardNumber): void
    {
        $stmt = $this->pdo()->prepare("UPDATE zibal_payments
            SET status = 'paid', ref_number = :ref, card_number = :card, verified_at = :now WHERE id = :id");
        $stmt->execute(['ref' => $refNumber, 'card' => $cardNumber, 'now' => gmdate('Y-m-d H:i:s'), 'id' => $id]);
    }

    public function markFailed(int $id, string $status, string $note = ''): void
    {
        $stmt = $this->pdo()->prepare("UPDATE zibal_payments SET status = :s, note = :n WHERE id = :id");
        $stmt->execute(['s' => $status, 'n' => $note, 'id' => $id]);
    }

    /** @return array<int, array> */
    public function all(int $limit = 100): array
    {
        $stmt = $this->pdo()->query('SELECT * FROM zibal_payments ORDER BY id DESC LIMIT ' . $limit);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
