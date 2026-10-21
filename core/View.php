<?php
declare(strict_types=1);

namespace Core;

/**
 * رندر ویوهای ماژول‌ها:
 * modules/<slug>/views/<area>/<name>.php — area یعنی admin یا front.
 * نام‌ها اعتبارسنجی می‌شوند تا مسیر از پوشه ماژول بیرون نزند.
 */
final class View
{
    private static string $basePath = '';

    public static function init(string $basePath): void
    {
        self::$basePath = $basePath;
    }

    public static function render(string $slug, string $area, string $name, array $data = []): string
    {
        foreach ([$slug, $area, $name] as $part) {
            if (!preg_match('/^[a-z0-9_-]+$/i', $part)) {
                throw new \InvalidArgumentException('نام ویو نامعتبر است');
            }
        }
        $file = self::$basePath . "/modules/{$slug}/views/{$area}/{$name}.php";
        if (!is_file($file)) {
            throw new \RuntimeException('ویو پیدا نشد: ' . "{$slug}/{$area}/{$name}");
        }
        extract($data);
        ob_start();
        require $file;
        return (string) ob_get_clean();
    }
}
