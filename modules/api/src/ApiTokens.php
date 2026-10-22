<?php
declare(strict_types=1);

namespace Modules\Api;

use Modules\Sqlite\Database;

/**
 * توکن‌های API — متن توکن فقط یک بار (هنگام ساخت) نمایش داده می‌شود؛
 * در دیتابیس فقط هش SHA-256 آن نگه داشته می‌شود.
 */
final class ApiTokens
{
    public function __construct(private readonly Database $db) {}

    public function migrate(): void
    {
        $this->db->pdo()->exec('CREATE TABLE IF NOT EXISTS api_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            token_hash TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_used_at TEXT,
            revoked INTEGER NOT NULL DEFAULT 0
        )');
    }

    /** @return array{id:int, token:string} متن توکن را همین یک بار برگردان */
    public function create(string $name): array
    {
        $token = 'vsl_' . bin2hex(random_bytes(32));
        $stmt = $this->db->pdo()->prepare('INSERT INTO api_tokens (name, token_hash) VALUES (?, ?)');
        $stmt->execute([$name !== '' ? $name : 'بدون نام', hash('sha256', $token)]);
        return ['id' => (int) $this->db->pdo()->lastInsertId(), 'token' => $token];
    }

    public function all(): array
    {
        return $this->db->pdo()
            ->query('SELECT id, name, created_at, last_used_at, revoked FROM api_tokens ORDER BY id DESC')
            ->fetchAll();
    }

    public function revoke(int $id): void
    {
        $stmt = $this->db->pdo()->prepare('UPDATE api_tokens SET revoked = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    /** @return array|null ردیف توکن معتبر؛ زمان آخرین استفاده هم ثبت می‌شود */
    public function authenticate(string $token): ?array
    {
        if ($token === '') {
            return null;
        }
        $hash = hash('sha256', $token);
        $stmt = $this->db->pdo()->prepare('SELECT id, name, token_hash FROM api_tokens WHERE token_hash = ? AND revoked = 0');
        $stmt->execute([$hash]);
        $row = $stmt->fetch();
        if (!is_array($row) || !hash_equals((string) $row['token_hash'], $hash)) {
            return null;
        }
        $touch = $this->db->pdo()->prepare('UPDATE api_tokens SET last_used_at = CURRENT_TIMESTAMP WHERE id = ?');
        $touch->execute([(int) $row['id']]);
        return $row;
    }
}
