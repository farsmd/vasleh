<?php
declare(strict_types=1);

namespace Modules\Admin;

/**
 * سازنده ماژول: پوشه ماژول تازه را با الگوی استاندارد می‌سازد —
 * یک core.php که تعریف و روت‌ها را برمی‌گرداند، و ویوی فرانت و مدیریت.
 * هسته از قبل این الگو را می‌شناسد؛ تغییری لازم نیست.
 */
final class ModuleBuilder
{
    public function __construct(private readonly string $modulesDir) {}

    public static function validSlug(string $slug): bool
    {
        return preg_match('/^[a-z][a-z0-9_-]*$/', $slug) === 1;
    }

    public function create(string $slug, string $title, string $description): bool
    {
        if (!self::validSlug($slug)) {
            return false;
        }
        $dir = $this->modulesDir . '/' . $slug;
        if (is_dir($dir)) {
            return false;
        }
        mkdir($dir . '/views/front', 0775, true);
        mkdir($dir . '/views/admin', 0775, true);

        $export = static fn (string $v): string => var_export($v, true);

        file_put_contents($dir . '/core.php', <<<PHP
<?php
declare(strict_types=1);

use Core\\Container;
use Core\\Response;
use Core\\Router;
use Core\\View;

/* ماژول {$export($slug)} — ساخته‌شده از پنل مدیریت */

return [
    'slug'        => {$export($slug)},
    'title'       => {$export($title)},
    'description' => {$export($description)},
    'version'     => '0.1.0',
    'system'      => false,
    'icon'        => '', // آیکون SVG گروه منو — خالی یعنی آیکون پیش‌فرض وصله

    'routes' => static function (Router \$router, Container \$c, string \$module): void {
        \$router->get('/{$slug}', static fn (): Response =>
            Response::html(View::render('{$slug}', 'front', 'index')), \$module);
        \$router->get('/admin/{$slug}', static fn (): Response =>
            Response::html(View::render('{$slug}', 'admin', 'index')), \$module);
    },
];

PHP);

        $titleSafe = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        file_put_contents($dir . '/views/front/index.php', <<<HTML
<!doctype html>
<html lang="fa" dir="rtl"><head><meta charset="utf-8"><title>{$titleSafe}</title></head>
<body><h1>ماژول «{$titleSafe}» — ویوی فرانت</h1><p>این صفحه را ویوی فرانت ماژول تازه پاسخ داد.</p></body></html>

HTML);

        file_put_contents($dir . '/views/admin/index.php', <<<HTML
<h1>ماژول «{$titleSafe}» — ویوی مدیریت</h1><p>این بخش، ویوی مدیریت ماژول تازه است.</p>

HTML);

        return true;
    }
}
