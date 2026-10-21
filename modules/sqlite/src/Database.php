<?php
declare(strict_types=1);

namespace Modules\Sqlite;

use PDO;

/** اتصال SQLite — فایل دیتابیس در storage/ و دور از دسترس وب. */
final class Database
{
    private ?PDO $pdo = null;

    public function __construct(private readonly string $file) {}

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }
        $dir = dirname($this->file);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $pdo = new PDO('sqlite:' . $this->file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $pdo->exec('PRAGMA journal_mode = WAL');
        return $this->pdo = $pdo;
    }

    /** وضعیت دیتابیس برای ویوی مدیریت: حجم، نسخه و جدول‌ها با تعداد ردیف. */
    public function info(): array
    {
        $pdo = $this->pdo();
        $tables = [];
        $names = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name")
            ->fetchAll(PDO::FETCH_COLUMN);
        foreach ($names as $name) {
            $count = (int) $pdo->query('SELECT COUNT(*) FROM "' . str_replace('"', '', (string) $name) . '"')->fetchColumn();
            $tables[] = ['name' => (string) $name, 'rows' => $count];
        }
        return [
            'file'    => $this->file,
            'size'    => is_file($this->file) ? (int) filesize($this->file) : 0,
            'version' => (string) $pdo->query('SELECT sqlite_version()')->fetchColumn(),
            'tables'  => $tables,
        ];
    }
}
