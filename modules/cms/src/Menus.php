<?php
declare(strict_types=1);

namespace Modules\Cms;

use Modules\Sqlite\Database;

/** منوهای سایت و آیتم‌هایشان (با یک سطح زیرمنو) + رندر HTML منو برای جانگهدار {{menu:slug}}. */
final class Menus
{
    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $this->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS cms_menus (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                created_at TEXT NOT NULL
            )'
        );
        $this->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS cms_menu_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                menu_id INTEGER NOT NULL,
                parent_id INTEGER NOT NULL DEFAULT 0,
                label TEXT NOT NULL,
                url TEXT NOT NULL DEFAULT \'/\',
                sort INTEGER NOT NULL DEFAULT 0
            )'
        );
    }

    public function seedIfEmpty(): void
    {
        if ((int) $this->pdo()->query('SELECT COUNT(*) FROM cms_menus')->fetchColumn() > 0) {
            return;
        }
        $id = $this->create('منوی اصلی', 'main');
        $this->addItem($id, 0, 'خانه', '/', 1);
        $this->addItem($id, 0, 'وبلاگ', '/blog', 2);
        $this->addItem($id, 0, 'درباره ما', '/page/about', 3);
    }

    /** @return array<int, array<string, mixed>> */
    public function all(): array
    {
        return $this->pdo()->query('SELECT * FROM cms_menus ORDER BY id ASC')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM cms_menus WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM cms_menus WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function create(string $name, string $slug): int
    {
        $stmt = $this->pdo()->prepare('INSERT INTO cms_menus (name, slug, created_at) VALUES (:name, :slug, :now)');
        $stmt->execute(['name' => $name, 'slug' => $slug, 'now' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo()->prepare('DELETE FROM cms_menu_items WHERE menu_id = :id');
        $stmt->execute(['id' => $id]);
        $stmt = $this->pdo()->prepare('DELETE FROM cms_menus WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** @return array<int, array<string, mixed>> */
    public function items(int $menuId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM cms_menu_items WHERE menu_id = :menu ORDER BY sort ASC, id ASC'
        );
        $stmt->execute(['menu' => $menuId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function addItem(int $menuId, int $parentId, string $label, string $url, int $sort): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO cms_menu_items (menu_id, parent_id, label, url, sort)
             VALUES (:menu, :parent, :label, :url, :sort)'
        );
        $stmt->execute(['menu' => $menuId, 'parent' => $parentId, 'label' => $label, 'url' => $url, 'sort' => $sort]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function updateItem(int $id, int $parentId, string $label, string $url, int $sort): void
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE cms_menu_items SET parent_id = :parent, label = :label, url = :url, sort = :sort WHERE id = :id'
        );
        $stmt->execute(['parent' => $parentId, 'label' => $label, 'url' => $url, 'sort' => $sort, 'id' => $id]);
    }

    public function deleteItem(int $id): void
    {
        // بچه‌های یتیم‌شده به سطح اصلی برمی‌گردند تا منو نصفه نماند.
        $stmt = $this->pdo()->prepare('UPDATE cms_menu_items SET parent_id = 0 WHERE parent_id = :id');
        $stmt->execute(['id' => $id]);
        $stmt = $this->pdo()->prepare('DELETE FROM cms_menu_items WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    /** رندر منو به HTML (لیست تو‌در‌تو، یک سطح زیرمنو). منوی ناموجود = رشته خالی. */
    public function render(string $slug): string
    {
        $menu = $this->findBySlug($slug);
        if ($menu === null) {
            return '';
        }
        $items = $this->items((int) $menu['id']);
        $children = [];
        foreach ($items as $item) {
            $children[(int) $item['parent_id']][] = $item;
        }
        return '<ul class="cms-menu">' . $this->renderLevel($children, 0) . '</ul>';
    }

    private function renderLevel(array $children, int $parentId): string
    {
        $html = '';
        foreach ($children[$parentId] ?? [] as $item) {
            $html .= '<li><a href="' . e((string) $item['url']) . '">' . e((string) $item['label']) . '</a>';
            if (!empty($children[(int) $item['id']])) {
                $html .= '<ul class="cms-submenu">' . $this->renderLevel($children, (int) $item['id']) . '</ul>';
            }
            $html .= '</li>';
        }
        return $html;
    }
}
