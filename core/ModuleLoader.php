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

    /**
     * کش مایگریشن (۶٫۰): slug => نسخه‌ای که مایگریشنش با موفقیت اجرا شده.
     * مایگریشن فقط وقتی اجرا می‌شود که ماژول تازه نصب شده باشد یا نسخه‌اش
     * عوض شده باشد؛ در حالت پایدار، ۱۶ ماژول در هر درخواست مایگریشن نمی‌شوند.
     * این فایل فقط کش است و هرگز نباید در پوش/همگام‌سازی/زیپ انتشار بیاید
     * (وگرنه نصب تازه با جدول‌های نساخته، مایگریشن را رد می‌کند) — در
     * tools/push.py و tools/sync-live.py در فهرست EXCLUDE است.
     * @var array<string, string>
     */
    private array $migratedVersions = [];

    public function __construct(
        private readonly string $modulesDir,
        private readonly string $stateFile,
        private readonly string $metaFile,
        private readonly string $migrationsFile = '',
    ) {
        $this->loadState();
        $this->loadMigrated();
        $this->scan();
        $this->applyMetaOverrides();
        $this->checkDependencies();
    }

    private function loadMigrated(): void
    {
        if ($this->migrationsFile === '' || !is_file($this->migrationsFile)) {
            return;
        }
        $data = json_decode((string) file_get_contents($this->migrationsFile), true);
        if (!is_array($data)) {
            return;
        }
        foreach ($data as $slug => $version) {
            if (is_string($version) && $version !== '') {
                $this->migratedVersions[(string) $slug] = $version;
            }
        }
    }

    /**
     * مایگریشن این ماژول لازم است؟ نسخه خالی یعنی همیشه اجرا
     * (محافظه‌کارانه: ماژول بدون نسخه، کش نمی‌شود).
     */
    public function needsMigrate(string $slug, string $version): bool
    {
        if ($version === '') {
            return true;
        }
        return ($this->migratedVersions[$slug] ?? null) !== $version;
    }

    /**
     * ثبت موفقیت مایگریشن؛ فقط بعد از اجرای موفق صدا زده می‌شود تا
     * مایگریشنِ ناموفق، درخواست بعد دوباره تلاش شود. خطای نوشتن فایل،
     * اجرای بعدی را به‌خطر نمی‌اندازد (نبودِ کش یعنی اجرای دوباره).
     */
    public function markMigrated(string $slug, string $version): void
    {
        if ($this->migrationsFile === '' || $version === '') {
            return;
        }
        $this->migratedVersions[$slug] = $version;
        try {
            @file_put_contents(
                $this->migrationsFile,
                (string) json_encode($this->migratedVersions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                LOCK_EX
            );
        } catch (\Throwable) {
            // کش است؛ نبودنش فقط یعنی مایگریشن دفعه بعد دوباره اجرا می‌شود.
        }
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
        // ترتیب سفارشی گروه‌ها و آیتم‌ها از پنل (storage/menu-order.json)؛
        // گروه و آیتم فهرست‌نشده ترتیب پیش‌فرض خود را در انتها حفظ می‌کنند.
        // گروه یعنی ماژول صاحب آیتم، پس آیتم فقط داخل گروه خودش مرتب می‌شود.
        $data = $this->menuOrderData();
        $order = $data['order'];
        $groups = $data['groups'];
        if ($order !== [] || $groups !== []) {
            $pos = array_flip($order);
            $gpos = array_flip($groups);
            usort($items, static function (array $a, array $b) use ($pos, $gpos): int {
                $ga = $gpos[(string) ($a['module'] ?? '')] ?? PHP_INT_MAX;
                $gb = $gpos[(string) ($b['module'] ?? '')] ?? PHP_INT_MAX;
                if ($ga !== $gb) {
                    return $ga <=> $gb;
                }
                return ($pos[(string) ($a['path'] ?? '')] ?? PHP_INT_MAX) <=> ($pos[(string) ($b['path'] ?? '')] ?? PHP_INT_MAX);
            });
        }
        return $items;
    }

    /** ترتیب ذخیره‌شده منو: ['order' => مسیرها، 'groups' => نامک ماژول‌ها]. */
    public function menuOrderData(): array
    {
        $file = dirname($this->stateFile) . '/menu-order.json';
        $out = ['order' => [], 'groups' => []];
        if (!is_file($file)) {
            return $out;
        }
        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            return $out;
        }
        if (is_array($data['order'] ?? null)) {
            $out['order'] = array_values(array_map('strval', $data['order']));
        }
        if (is_array($data['groups'] ?? null)) {
            $out['groups'] = array_values(array_map('strval', $data['groups']));
        }
        return $out;
    }

    /** ذخیره ترتیب کامل گروه‌ها و آیتم‌های منو به‌صورت اتمیک. */
    public function saveMenuOrder(array $order, array $groups = []): void
    {
        $file = dirname($this->stateFile) . '/menu-order.json';
        $tmp = $file . '.tmp' . bin2hex(random_bytes(4));
        file_put_contents($tmp, json_encode(['order' => array_values(array_map('strval', $order)), 'groups' => array_values(array_map('strval', $groups))], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        rename($tmp, $file);
    }

    /**
     * حذف کامل یک ماژول غیرسیستمی: پوشه‌اش + ردش از state و meta.
     * ماژول سیستمی، خود admin و نامک نامعتبر هرگز حذف نمی‌شوند.
     * جدول‌های دیتابیس ماژول عمداً باقی می‌مانند.
     */
    public function deleteModule(string $slug): bool
    {
        if (preg_match('/^[a-z0-9_-]+$/', $slug) !== 1 || $slug === 'admin') {
            return false;
        }
        $definition = $this->definitions[$slug] ?? null;
        if ($definition === null || !empty($definition['system'])) {
            return false;
        }
        $dir = rtrim($this->modulesDir, '/') . '/' . $slug;
        $real = realpath($dir);
        $base = realpath($this->modulesDir);
        if ($real === false || $base === false || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) {
            return false;
        }
        $this->removeDir($real);
        $this->disabled = array_values(array_filter($this->disabled, static fn (string $d): bool => $d !== $slug));
        unset($this->metaOverrides[$slug]);
        unset($this->definitions[$slug]);
        file_put_contents($this->stateFile, json_encode(['disabled' => $this->disabled], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        file_put_contents($this->metaFile, json_encode($this->metaOverrides, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return true;
    }

    private function removeDir(string $dir): void
    {
        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $entry;
            if (is_dir($path) && !is_link($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
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
