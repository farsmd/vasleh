<?php
declare(strict_types=1);

namespace Modules\Cms;

use Modules\Sqlite\Database;

/** چیدمان‌ها: هدرها، فوترها و قالب‌های نمایش محتوا — هرکدام HTML آزاد با جانگهدار. */
final class Layouts
{
    public const KINDS = ['header', 'footer', 'template'];

    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $this->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS cms_layouts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                kind TEXT NOT NULL,
                name TEXT NOT NULL,
                slug TEXT NOT NULL,
                html TEXT NOT NULL DEFAULT \'\',
                is_default INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL,
                UNIQUE(kind, slug)
            )'
        );
    }

    /** چیدمان‌های پیش‌فرض زیبا برای هر نوع — فقط وقتی آن نوع هیچ ردیفی ندارد. */
    public function seedIfEmpty(): void
    {
        $seeds = [
            'header' => [
                ['name' => 'هدر پیش‌فرض', 'slug' => 'default', 'default' => 1, 'html' =>
                    '<header class="site-header"><div class="wrap"><a class="brand" href="/">{{site_name}}</a><nav>{{menu:main}}</nav></div></header>'],
            ],
            'footer' => [
                ['name' => 'فوتر پیش‌فرض', 'slug' => 'default', 'default' => 1, 'html' =>
                    '<footer class="site-footer"><div class="wrap"><p>{{site_name}}</p><nav>{{menu:main}}</nav><small>ساخته‌شده با وصله</small></div></footer>'],
            ],
            'template' => [
                ['name' => 'قالب ساده', 'slug' => 'simple', 'default' => 1, 'html' =>
                    '<main class="wrap"><article class="post"><h1>{{title}}</h1><time>{{date}}</time><div class="body">{{content}}</div></article></main>'],
                ['name' => 'قالب تمام‌عرض', 'slug' => 'wide', 'default' => 0, 'html' =>
                    '<main class="wrap wide"><h1>{{title}}</h1><div class="body">{{content}}</div></main>'],
            ],
        ];
        $now = date('Y-m-d H:i:s');
        foreach ($seeds as $kind => $rows) {
            $count = (int) $this->pdo()
                ->query('SELECT COUNT(*) FROM cms_layouts WHERE kind = ' . $this->pdo()->quote($kind))
                ->fetchColumn();
            if ($count > 0) {
                continue;
            }
            $stmt = $this->pdo()->prepare(
                'INSERT INTO cms_layouts (kind, name, slug, html, is_default, created_at, updated_at)
                 VALUES (:kind, :name, :slug, ' . ':html' . ', :def, :now, :now)'
            );
            foreach ($rows as $row) {
                $stmt->execute([
                    'kind' => $kind, 'name' => $row['name'], 'slug' => $row['slug'],
                    'html' => $row['html'], 'def' => $row['default'], 'now' => $now,
                ]);
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    public function all(string $kind): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM cms_layouts WHERE kind = :kind ORDER BY is_default DESC, id ASC');
        $stmt->execute(['kind' => $kind]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM cms_layouts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function defaultOf(string $kind): ?array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM cms_layouts WHERE kind = :kind ORDER BY is_default DESC, id ASC LIMIT 1'
        );
        $stmt->execute(['kind' => $kind]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function slugTaken(string $kind, string $slug, int $exceptId = 0): bool
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM cms_layouts WHERE kind = :kind AND slug = :slug AND id != :except'
        );
        $stmt->execute(['kind' => $kind, 'slug' => $slug, 'except' => $exceptId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(string $kind, string $name, string $slug, string $html, bool $isDefault): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo()->prepare(
            'INSERT INTO cms_layouts (kind, name, slug, html, is_default, created_at, updated_at)
             VALUES (:kind, :name, :slug, :html, :def, :now, :now)'
        );
        $stmt->execute([
            'kind' => $kind, 'name' => $name, 'slug' => $slug, 'html' => $html,
            'def' => $isDefault ? 1 : 0, 'now' => $now,
        ]);
        $id = (int) $this->pdo()->lastInsertId();
        if ($isDefault) {
            $this->setDefault($id);
        }
        return $id;
    }

    public function update(int $id, string $name, string $slug, string $html): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE cms_layouts SET name = :name, slug = :slug, html = :html, updated_at = :now WHERE id = :id'
        );
        $stmt->execute(['name' => $name, 'slug' => $slug, 'html' => $html, 'now' => date('Y-m-d H:i:s'), 'id' => $id]);
    }

    public function setDefault(int $id): void
    {
        $row = $this->find($id);
        if ($row === null) {
            return;
        }
        $stmt = $this->pdo()->prepare('UPDATE cms_layouts SET is_default = 0 WHERE kind = :kind');
        $stmt->execute(['kind' => $row['kind']]);
        $stmt = $this->pdo()->prepare('UPDATE cms_layouts SET is_default = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM cms_layouts WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
