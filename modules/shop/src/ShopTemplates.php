<?php
declare(strict_types=1);

namespace Modules\Shop;

use Modules\Sqlite\Database;

/** قالب‌های فروشگاه: پوسته دور صفحات فروشگاه، مثل فایل‌های HTML لندینگ.
 *  محتوا HTML با توکن‌هاست ({{content}} و ...) و فقط مالک از ادمین ویرایشش می‌کند، پس خام رندر می‌شود. */
final class ShopTemplates
{
    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $pdo = $this->pdo();
        $pdo->exec("CREATE TABLE IF NOT EXISTS shop_templates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            title TEXT NOT NULL DEFAULT '',
            content TEXT NOT NULL DEFAULT '',
            is_default INTEGER NOT NULL DEFAULT 0
        )");
        $n = (int) $pdo->query('SELECT COUNT(*) FROM shop_templates')->fetchColumn();
        if ($n === 0) {
            $seed = $this->seedContent();
            $stmt = $pdo->prepare('INSERT INTO shop_templates (name, title, content, is_default) VALUES (:name, :title, :content, 1)');
            $stmt->execute(['name' => 'nexus', 'title' => 'نکسوس (پیش‌فرض)', 'content' => $seed]);
        }
    }

    /** محتوای سید پیش‌فرض: فایل تبدیل‌شده پوسته نکسوس؛ اگر نبود، پوسته حداقلی سالم. */
    private function seedContent(): string
    {
        $file = dirname(__DIR__) . '/views/front/seed-nexus-template.html';
        if (is_file($file)) {
            $html = (string) file_get_contents($file);
            if (str_contains($html, '{{content}}')) {
                return $html;
            }
        }
        return '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><title>{{title}}</title></head><body>{{content}}</body></html>';
    }

    /** @return array<int, array> */
    public function all(): array
    {
        return $this->pdo()->query('SELECT * FROM shop_templates ORDER BY id')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_templates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function findByName(string $name): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM shop_templates WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    public function defaultTemplate(): ?array
    {
        $row = $this->pdo()->query('SELECT * FROM shop_templates WHERE is_default = 1 ORDER BY id LIMIT 1')->fetch(\PDO::FETCH_ASSOC);
        if ($row !== false) {
            return $row;
        }
        $row = $this->pdo()->query('SELECT * FROM shop_templates ORDER BY id LIMIT 1')->fetch(\PDO::FETCH_ASSOC);
        return $row !== false ? $row : null;
    }

    /** @return array{0: ?array, 1: ?string} قالب ذخیره‌شده و خطا */
    public function save(?int $id, string $name, string $title, string $content): array
    {
        $name = trim($name);
        $title = trim($title);
        if ($name === '' || !preg_match('/^[a-z0-9_-]+$/', $name)) {
            return [null, 'نام قالب فقط حروف لاتین کوچک، رقم، خط تیره و زیرخط باشد.'];
        }
        if (!str_contains($content, '{{content}}')) {
            return [null, 'قالب باید جای {{content}} را داشته باشد.'];
        }
        $existing = $this->findByName($name);
        if ($existing !== null && (int) $existing['id'] !== (int) ($id ?? 0)) {
            return [null, 'این نام قبلاً ثبت شده است.'];
        }
        $pdo = $this->pdo();
        if ($id !== null && $id > 0 && $this->find($id) !== null) {
            $stmt = $pdo->prepare('UPDATE shop_templates SET name = :name, title = :title, content = :content WHERE id = :id');
            $stmt->execute(['name' => $name, 'title' => $title, 'content' => $content, 'id' => $id]);
            return [$this->find($id), null];
        }
        $stmt = $pdo->prepare('INSERT INTO shop_templates (name, title, content, is_default) VALUES (:name, :title, :content, 0)');
        $stmt->execute(['name' => $name, 'title' => $title, 'content' => $content]);
        return [$this->find((int) $pdo->lastInsertId()), null];
    }

    /** حذف قالب؛ پیش‌فرض حذف نمی‌شود. */
    public function delete(int $id): bool
    {
        $row = $this->find($id);
        if ($row === null || (int) $row['is_default'] === 1) {
            return false;
        }
        $stmt = $this->pdo()->prepare('DELETE FROM shop_templates WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return true;
    }

    public function setDefault(int $id): bool
    {
        if ($this->find($id) === null) {
            return false;
        }
        $pdo = $this->pdo();
        $pdo->exec('UPDATE shop_templates SET is_default = 0');
        $stmt = $pdo->prepare('UPDATE shop_templates SET is_default = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return true;
    }
}
