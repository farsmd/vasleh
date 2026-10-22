<?php
declare(strict_types=1);

namespace Modules\Cms;

use Modules\Sqlite\Database;

/** مخزن محتواها (نوشته‌ها و برگه‌ها) روی دیتابیس sqlite. */
final class Posts
{
    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $this->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS cms_posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT NOT NULL UNIQUE,
                title TEXT NOT NULL,
                excerpt TEXT NOT NULL DEFAULT \'\',
                body TEXT NOT NULL DEFAULT \'\',
                type TEXT NOT NULL DEFAULT \'post\',
                status TEXT NOT NULL DEFAULT \'draft\',
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
    }

    /** محتوای نمونه فقط وقتی جدول کاملاً خالی است تا فرانت از اول خالی نباشد. */
    public function seedIfEmpty(): void
    {
        if ((int) $this->pdo()->query('SELECT COUNT(*) FROM cms_posts')->fetchColumn() > 0) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo()->prepare(
            'INSERT INTO cms_posts (slug, title, excerpt, body, type, status, created_at, updated_at)
             VALUES (:slug, :title, :excerpt, :body, :type, \'published\', :now, :now)'
        );
        $stmt->execute([
            'slug'    => 'hello',
            'title'   => 'سلام دنیا!',
            'excerpt' => 'اولین نوشته با ماژول مدیریت محتوای وصله.',
            'body'    => '<p>به وبلاگ وصله خوش آمدید. این نوشته نمونه را می‌توانید از بخش مدیریت ← مدیریت محتوا ویرایش یا حذف کنید و نوشته‌های خودتان را بسازید.</p>',
            'type'    => 'post',
            'now'     => $now,
        ]);
        $stmt->execute([
            'slug'    => 'about',
            'title'   => 'درباره ما',
            'excerpt' => '',
            'body'    => '<p>این یک برگه نمونه است؛ متن درباره ما را از بخش مدیریت ← مدیریت محتوا بنویسید.</p>',
            'type'    => 'page',
            'now'     => $now,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    public function allAdmin(): array
    {
        return $this->pdo()
            ->query('SELECT * FROM cms_posts ORDER BY updated_at DESC, id DESC')
            ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return array<int, array<string, mixed>> */
    public function published(string $type): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM cms_posts WHERE status = \'published\' AND type = :type ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM cms_posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function findPublishedBySlug(string $slug, string $type): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM cms_posts WHERE slug = :slug AND type = :type AND status = \'published\''
        );
        $stmt->execute(['slug' => $slug, 'type' => $type]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function slugTaken(string $slug, int $exceptId = 0): bool
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM cms_posts WHERE slug = :slug AND id != :except');
        $stmt->execute(['slug' => $slug, 'except' => $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo()->prepare(
            'INSERT INTO cms_posts (slug, title, excerpt, body, type, status, created_at, updated_at)
             VALUES (:slug, :title, :excerpt, :body, :type, :status, :now, :now)'
        );
        $stmt->execute([
            'slug'    => $data['slug'],
            'title'   => $data['title'],
            'excerpt' => $data['excerpt'],
            'body'    => $data['body'],
            'type'    => $data['type'],
            'status'  => $data['status'],
            'now'     => $now,
        ]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE cms_posts SET slug = :slug, title = :title, excerpt = :excerpt, body = :body,
             type = :type, status = :status, updated_at = :now WHERE id = :id'
        );
        $stmt->execute([
            'slug'    => $data['slug'],
            'title'   => $data['title'],
            'excerpt' => $data['excerpt'],
            'body'    => $data['body'],
            'type'    => $data['type'],
            'status'  => $data['status'],
            'now'     => date('Y-m-d H:i:s'),
            'id'      => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM cms_posts WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** ساخت نامک از عنوان: حروف و عدد (فارسی هم مجاز) با خط تیره، بقیه حذف. */
    public static function slugify(string $title): string
    {
        $slug = preg_replace('/[^\p{L}\p{N}]+/u', '-', trim($title)) ?? '';
        return trim($slug, '-');
    }
}
