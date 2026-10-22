<?php
declare(strict_types=1);

namespace Core;

/**
 * لودر ماژول‌ها — قلب ساختار ماژولار:
 * پوشه modules/ را اسکن می‌کند؛ نام هر پوشه = نام ماژول.
 * فایل core.php هر ماژول تعریفش را برمی‌گرداند:
 *   slug, title, description, version, system, requires,
 *   services, routes, hooks, migrate, permissions, admin_menu
 * برای افزودن ماژول تازه هیچ تغییری در هسته لازم نیست.
 *
 * ویرایش مشخصات از پنل روی لایه متادیتا (storage/modules-meta.json) اعمال
 * می‌شود تا کد هیچ ماژولی از پنل دست نخورد.
 */
final class ModuleLoader
{
    /** @var array<string, array> */
    private array $definitions = [];

    /** @var string[] */
    private array $disabled = [];

    /** @var array<string, array> */
    private array $metaOverrides = [];

    /** @var array<string, string> slug => خطا */
    public array $errors = [];

    public function __construct(
        private readonly string $modulesDir,
        private readonly string $stateFile,
        private readonly string $metaFile,
    ) {
        $this->loadState();
        $this->scan();
        $this->applyMetaOverrides();
        $this->checkDependencies();
    }

    private function loadState(): void
    {
        if (is_file($this->stateFile)) {
            $data = json_decode((string) file_get_contents($this->stateFile), true);
            $this->disabled = array_values(array_map('strval', $data['disabled'] ?? []));
        }
        if (is_file($this->metaFile)) {
            $meta = json_decode((string) file_get_contents($this->metaFile), true);
            $this->metaOverrides = is_array($meta) ? $meta : [];
        }
    }

    private function scan(): void
    {
        foreach (glob($this->modulesDir . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $slug = basename($dir);
            if (!preg_match('/^[a-z][a-z0-9_-]*$/', $slug)) {
                $this->errors[$slug] = 'نام پوشه ماژول معتبر نیست';
                continue;
            }
            $file = $dir . '/core.php';
            if (!is_file($file)) {
                $this->errors[$slug] = 'فایل core.php ندارد';
                continue;
            }
            try {
                $definition = require $file;
            } catch (\Throwable $error) {
                // ماژول خراب (خطای نحوی یا اجرا در core.php) نباید کل سیستم را بخواباند؛
                // با یک تعریف جایگزین در فهرست می‌ماند تا خطایش در پنل دیده شود.
                $this->errors[$slug] = 'خطای بارگذاری ماژول: ' . substr($error->getMessage(), 0, 200);
                $definition = ['slug' => $slug, 'title' => $slug, 'description' => '', 'version' => '?', 'system' => false, 'requires' => []];
            }
            if (!is_array($definition) || ($definition['slug'] ?? null) !== $slug) {
                $this->errors[$slug] = 'تعریف core.php نامعتبر است یا slug با نام پوشه یکی نیست';
                $definition = ['slug' => $slug, 'title' => $slug, 'description' => '', 'version' => '?', 'system' => false, 'requires' => []];
            }
            $definition['dir'] = $dir;
            $this->definitions[$slug] = $definition;
        }
        ksort($this->definitions);
    }

    private function applyMetaOverrides(): void
    {
        foreach ($this->metaOverrides as $slug => $meta) {
            if (isset($this->definitions[$slug]) && is_array($meta)) {
                foreach (['title', 'description', 'version'] as $key) {
                    if (isset($meta[$key])) {
                        $this->definitions[$slug][$key] = $meta[$key];
                    }
                }
            }
        }
    }

    private function checkDependencies(): void
    {
        foreach ($this->definitions as $slug => $definition) {
            foreach ((array) ($definition['requires'] ?? []) as $need) {
                if (!isset($this->definitions[$need]) || !$this->isEnabled((string) $need)) {
                    $this->errors[$slug] = 'وابستگی برآورده نشده: ' . $need;
                }
            }
        }
    }

    /**
     * ثبت خطای زمان اجرا (مایگریشن/روت/هوک) — ماژول از این لحظه کنار گذاشته
     * می‌شود تا خرابی‌اش کل سیستم را نخواباند؛ خطا روی داشبورد دیده می‌شود.
     */
    public function markError(string $slug, string $message): void
    {
        if (!isset($this->errors[$slug])) {
            $this->errors[$slug] = $message;
        }
    }

    /** @return array<string, array> */
    public function all(): array
    {
        return $this->definitions;
    }

    /** فقط ماژول‌های فعال و بدون خطا در چرخه درخواست شرکت می‌کنند. */
    public function active(): array
    {
        $out = [];
        foreach ($this->definitions as $slug => $definition) {
            if ($this->isEnabled($slug) && !isset($this->errors[$slug])) {
                $out[$slug] = $definition;
            }
        }
        return $out;
    }

    public function get(string $slug): ?array
    {
        return $this->definitions[$slug] ?? null;
    }

    public function exists(string $slug): bool
    {
        return isset($this->definitions[$slug]);
    }

    public function isEnabled(string $slug): bool
    {
        return $this->exists($slug) && !in_array($slug, $this->disabled, true);
    }

    public function isSystem(string $slug): bool
    {
        return (bool) ($this->definitions[$slug]['system'] ?? false);
    }

    public function setEnabled(string $slug, bool $enabled): bool
    {
        if (!$this->exists($slug) || ($this->isSystem($slug) && !$enabled)) {
            return false;
        }
        $this->disabled = array_values(array_diff($this->disabled, [$slug]));
        if (!$enabled) {
            $this->disabled[] = $slug;
        }
        file_put_contents($this->stateFile, json_encode(['disabled' => $this->disabled], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return true;
    }

    /** ویرایش مشخصات ماژول از پنل — روی لایه متادیتا، نه روی کد ماژول. */
    public function updateMeta(string $slug, array $meta): bool
    {
        if (!$this->exists($slug)) {
            return false;
        }
        $clean = [];
        foreach (['title', 'description', 'version'] as $key) {
            if (isset($meta[$key])) {
                $clean[$key] = (string) $meta[$key];
            }
        }
        $this->metaOverrides[$slug] = array_merge($this->metaOverrides[$slug] ?? [], $clean);
        file_put_contents($this->metaFile, json_encode($this->metaOverrides, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return true;
    }

    /** آیتم‌های منوی مدیریت از همه ماژول‌های فعال جمع می‌شود؛ عنوان و آیکون ماژول هم همراهشان می‌آید تا منو گروهی رندر شود. */
    public function adminMenu(): array
    {
        $items = [];
        foreach ($this->active() as $slug => $definition) {
            foreach ((array) ($definition['admin_menu'] ?? []) as $item) {
                $item['module'] = $slug;
                $item['module_title'] = (string) ($definition['title'] ?? $slug);
                $item['module_icon'] = (string) ($definition['icon'] ?? '');
                $items[] = $item;
            }
        }
        return $items;
    }

    /** دسترسی‌های اعلام‌شده از همه ماژول‌ها: key => label */
    public function permissions(): array
    {
        $all = [];
        foreach ($this->definitions as $definition) {
            foreach ((array) ($definition['permissions'] ?? []) as $key => $label) {
                $all[(string) $key] = (string) $label;
            }
        }
        return $all;
    }
}
