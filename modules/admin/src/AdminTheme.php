<?php
declare(strict_types=1);

namespace Modules\Admin;

/**
 * تنظیمات کامل قالب ادمین — ذخیره در storage/admin-theme.json (وضعیت نصب،
 * مثل modules-state؛ هرگز پوش نمی‌شود). خواندن خراب یا ناقص هرگز لی‌اوت را
 * نمی‌خواباند: هر کلید نامعتبر به پیش‌فرض برمی‌گردد.
 */
final class AdminTheme
{
    public const DEFAULTS = [
        'accent' => '#2563eb',
        'panel_dark' => '#101a33',
        'panel_light' => '#ffffff',
        'main_dark' => '#070b14',
        'main_light' => '#f1f5f9',
        'radius' => 16,
        'font_size' => 16,
        'default_theme' => 'dark',
    ];

    private const RADII = [8, 12, 16, 20, 24];
    private const FONT_SIZES = [13, 14, 15, 16];

    public static function load(string $basePath): array
    {
        $out = self::DEFAULTS;
        $file = $basePath . '/storage/admin-theme.json';
        if (!is_file($file)) {
            return $out;
        }
        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            return $out;
        }
        foreach (self::DEFAULTS as $key => $def) {
            if (array_key_exists($key, $data)) {
                $out[$key] = self::valid($key, $data[$key], $def);
            }
        }
        return $out;
    }

    /**
     * بلوک CSS متغیرهای تم — یک منبع واحد برای همه پوسته‌های کاربری
     * (فرانت، فروشگاه، بخش شخصی، CSS کلی سایت). مقدارها از load() آمده‌اند
     * و اعتبارسنجی شده‌اند، پس بدون escape در CSS نوشته می‌شوند.
     */
    public static function varsCss(array $theme): string
    {
        $t = array_merge(self::DEFAULTS, $theme);
        return ':root{--admin-accent:' . $t['accent'] . ';--admin-panel-bg:' . $t['panel_light'] . ';--admin-main-bg:' . $t['main_light'] . ';--admin-radius:' . (int) $t['radius'] . 'px;--admin-text:#0f172a;--admin-muted:#475569;--admin-border:#e2e8f0;--admin-font-size:' . (int) $t['font_size'] . 'px}'
            . '.dark{--admin-panel-bg:' . $t['panel_dark'] . ';--admin-main-bg:' . $t['main_dark'] . ';--admin-text:#f1f5f9;--admin-muted:#94a3b8;--admin-border:rgba(255,255,255,.12)}'
            . 'html{font-size:var(--admin-font-size)}';
    }

    /** ورودی فرم را روی تنظیمات فعلی سوار می‌کند و اتمیک ذخیره می‌کند. */
    public static function save(string $basePath, array $input): array
    {
        $out = self::load($basePath);
        foreach (self::DEFAULTS as $key => $def) {
            if (array_key_exists($key, $input)) {
                $out[$key] = self::valid($key, $input[$key], $out[$key]);
            }
        }
        $file = $basePath . '/storage/admin-theme.json';
        $tmp = $file . '.tmp' . bin2hex(random_bytes(4));
        file_put_contents($tmp, json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        rename($tmp, $file);
        return $out;
    }

    public static function reset(string $basePath): void
    {
        $file = $basePath . '/storage/admin-theme.json';
        if (is_file($file)) {
            unlink($file);
        }
    }

    private static function valid(string $key, mixed $value, mixed $fallback): mixed
    {
        return match ($key) {
            'accent', 'panel_dark', 'panel_light', 'main_dark', 'main_light' => (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1) ? strtolower($value) : $fallback,
            'radius' => in_array((int) $value, self::RADII, true) ? (int) $value : $fallback,
            'font_size' => in_array((int) $value, self::FONT_SIZES, true) ? (int) $value : $fallback,
            'default_theme' => in_array($value, ['dark', 'light'], true) ? $value : $fallback,
            default => $fallback,
        };
    }
}
