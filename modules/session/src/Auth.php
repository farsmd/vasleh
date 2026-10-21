<?php
declare(strict_types=1);

namespace Modules\Session;

use Modules\Sqlite\Database;
use PDOException;

/** کاربران مدیریت و نقش‌ها — داده در دیتابیس ماژول sqlite. */
final class Auth
{
    public function __construct(private readonly Database $db) {}

    public function migrate(): void
    {
        $pdo = $this->db->pdo();
        $pdo->exec('CREATE TABLE IF NOT EXISTS app_sessions (
            id TEXT PRIMARY KEY,
            data TEXT NOT NULL DEFAULT "{}",
            expires_at INTEGER NOT NULL
        )');
        $pdo->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            pass_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT "admin",
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        // سید اولین مدیر — فقط وقتی هیچ کاربری نیست.
        // رمز اولیه پروتوتایپ: admin123 — باید همان اول از صفحه کاربران عوض/حذف شود؛
        // قدم «نصاب» که بیاید، مدیر در نصب ساخته می‌شود و این سید حذف می‌شود.
        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count === 0) {
            $stmt = $pdo->prepare('INSERT INTO users (username, pass_hash, role) VALUES (?, ?, ?)');
            $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'owner']);
        }
    }

    /** @return array{id:int, username:string, role:string}|null */
    public function attempt(string $username, string $password): ?array
    {
        $stmt = $this->db->pdo()->prepare('SELECT id, username, pass_hash, role FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if (!is_array($row) || !password_verify($password, (string) $row['pass_hash'])) {
            return null;
        }
        return ['id' => (int) $row['id'], 'username' => (string) $row['username'], 'role' => (string) $row['role']];
    }

    public function users(): array
    {
        return $this->db->pdo()
            ->query('SELECT id, username, role, created_at FROM users ORDER BY id')
            ->fetchAll();
    }

    public function create(string $username, string $password, string $role): bool
    {
        if (!in_array($role, ['owner', 'admin'], true) || $username === '' || strlen($password) < 8) {
            return false;
        }
        try {
            $stmt = $this->db->pdo()->prepare('INSERT INTO users (username, pass_hash, role) VALUES (?, ?, ?)');
            return $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
        } catch (PDOException) {
            return false; // نام کاربری تکراری
        }
    }
}
