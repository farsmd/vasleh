<?php
declare(strict_types=1);

namespace Modules\Admin;

use Core\Container;
use Core\Request;
use Core\Response;

/** مدیریت ماژول‌ها: فهرست، فعال/غیرفعال، ساخت و ویرایش مشخصات. */
final class ModulesController
{
    public function __construct(private readonly Container $container) {}

    private function admin(): AdminController
    {
        return new AdminController($this->container);
    }

    private function csrf(): string
    {
        return $this->container->has('session') ? $this->container->get('session')->csrfToken() : '';
    }

    /** ثبت رویداد در گزارشات، اگر ماژول reports حاضر باشد. */
    private function log(string $action, string $target): void
    {
        if (!$this->container->has('logger') || !$this->container->has('session')) {
            return;
        }
        $actor = (string) ($this->container->get('session')->user()['username'] ?? '');
        $this->container->get('logger')->user($actor, $action, $target);
    }

    private function ownerOnly(): ?Response
    {
        $user = $this->container->has('session') ? $this->container->get('session')->user() : null;
        if (($user['role'] ?? '') !== 'owner') {
            return Response::forbidden('این کار فقط برای مالک است');
        }
        return null;
    }

    public function installForm(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        return $this->admin()->page('install', [
            'error' => $request->input('err'),
            'csrf'  => $this->csrf(),
        ], 'نصب ماژول از ZIP');
    }

    public function install(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $base = (string) $this->container->get('base_path');
        $installer = new ModuleInstaller($base . '/modules', $base . '/storage');
        $result = $installer->install((array) ($_FILES['module_zip'] ?? []));
        if (empty($result['ok'])) {
            return Response::redirect('/admin/modules/install?err=' . urlencode((string) ($result['error'] ?? 'خطا')));
        }
        $this->log('نصب ماژول', (string) $result['slug']);
        return Response::redirect('/admin/modules?msg=' . urlencode('ماژول «' . $result['slug'] . '» نصب و فعال شد'));
    }

    public function index(Request $request): Response
    {
        $loader = $this->container->get('loader');
        $rows = [];
        foreach ($loader->all() as $slug => $definition) {
            $definition['enabled'] = $loader->isEnabled($slug);
            $definition['error'] = $loader->errors[$slug] ?? null;
            $rows[$slug] = $definition;
        }
        return $this->admin()->page('modules', [
            'modules' => $rows,
            'flash'   => $request->input('msg'),
            'csrf'    => $this->csrf(),
        ], 'مدیریت ماژول‌ها');
    }

    public function toggle(Request $request, array $params): Response
    {
        $loader = $this->container->get('loader');
        $slug = (string) ($params['slug'] ?? '');
        if ($loader->exists($slug)) {
            $enable = !$loader->isEnabled($slug);
            if ($loader->setEnabled($slug, $enable)) {
                $this->log($enable ? 'فعال‌کردن ماژول' : 'غیرفعال‌کردن ماژول', $slug);
            }
        }
        // بازگشت فقط به داشبورد مجاز است؛ بقیه به صفحه ماژول‌ها می‌روند.
        return Response::redirect((string) $request->input('return', '') === '/admin' ? '/admin' : '/admin/modules');
    }

    /** حذف کامل ماژول غیرسیستمی (فقط مالک). جدول‌های دیتابیس باقی می‌مانند. */
    public function delete(Request $request, array $params): Response
    {
        if (($denied = $this->ownerOnly()) !== null) {
            return $denied;
        }
        $loader = $this->container->get('loader');
        $slug = (string) ($params['slug'] ?? '');
        if ($loader->deleteModule($slug)) {
            $this->log('حذف ماژول', $slug);
            return Response::redirect('/admin/modules?msg=' . urlencode('ماژول حذف شد'));
        }
        return Response::redirect('/admin/modules?msg=' . urlencode('حذف ممکن نیست (ماژول سیستمی یا نامعتبر)'));
    }

    public function create(Request $request): Response
    {
        return $this->admin()->page('module-create', ['error' => $request->input('err'), 'csrf' => $this->csrf()], 'ساخت ماژول');
    }

    public function store(Request $request): Response
    {
        $slug = trim((string) $request->input('slug', ''));
        $title = trim((string) $request->input('title', ''));
        $description = trim((string) $request->input('description', ''));
        $loader = $this->container->get('loader');

        if ($title === '' || !ModuleBuilder::validSlug($slug) || $loader->exists($slug)) {
            return Response::redirect('/admin/modules/create?err=' . urlencode('نام لاتین نامعتبر یا تکراری است'));
        }
        (new ModuleBuilder($this->container->get('base_path') . '/modules'))->create($slug, $title, $description);
        $this->log('ساخت ماژول', $slug);
        return Response::redirect('/admin/modules?msg=' . urlencode('ماژول ساخته شد و روتش زنده است'));
    }

    public function edit(Request $request, array $params): Response
    {
        $loader = $this->container->get('loader');
        $slug = (string) ($params['slug'] ?? '');
        $module = $loader->get($slug);
        if ($module === null) {
            return Response::notFound('ماژولی با این نام نیست');
        }
        return $this->admin()->page('module-edit', [
            'module'  => $module,
            'csrf'    => $this->csrf(),
            'details' => $this->details($slug, $module),
            'files'   => !empty($module['dir']) ? $this->moduleFiles((string) $module['dir']) : [],
            'isOwner' => $this->isOwner(),
        ], 'ویرایش ماژول');
    }

    // ---------- ویرایشگر فایل ماژول (فقط مالک) ----------

    private const EDITABLE_EXT = ['php', 'css', 'js', 'html', 'json', 'md', 'txt'];
    private const MAX_EDIT_BYTES = 524288; // ۵۱۲ کیلوبایت

    private function isOwner(): bool
    {
        $user = $this->container->has('session') ? $this->container->get('session')->user() : null;
        return ($user['role'] ?? '') === 'owner';
    }

    /** فهرست بازگشتی فایل‌های یک ماژول برای بخش ویرایشگر در صفحه ویرایش. */
    private function moduleFiles(string $dir): array
    {
        $files = [];
        if (!is_dir($dir)) {
            return $files;
        }
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if (!$f->isFile()) {
                continue;
            }
            $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($dir) + 1));
            $files[] = [
                'path'     => $rel,
                'size'     => $f->getSize(),
                'editable' => in_array(strtolower(pathinfo($rel, PATHINFO_EXTENSION)), self::EDITABLE_EXT, true),
            ];
        }
        usort($files, static fn (array $a, array $b): int => strcmp($a['path'], $b['path']));
        return $files;
    }

    /**
     * اعتبارسنجی مسیر فایل داخل پوشه ماژول: بدون تراورس، پسوند در فهرست مجاز،
     * و مسیر واقعی حتماً زیر پوشه خود ماژول. خروجی null یعنی رد.
     */
    private function guardFile(string $slug, string $rel): ?array
    {
        $module = $this->container->get('loader')->get($slug);
        if ($module === null || empty($module['dir'])) {
            return null;
        }
        $rel = str_replace('\\', '/', trim($rel));
        if ($rel === '' || str_starts_with($rel, '/')) {
            return null;
        }
        foreach (explode('/', $rel) as $seg) {
            if ($seg === '..' || $seg === '') {
                return null;
            }
        }
        $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
        if (!in_array($ext, self::EDITABLE_EXT, true)) {
            return null;
        }
        $abs = $module['dir'] . '/' . $rel;
        if (!is_file($abs)) {
            return null;
        }
        $realDir = realpath((string) $module['dir']);
        $real = realpath($abs);
        if ($realDir === false || $real === false || !str_starts_with($real, $realDir . '/')) {
            return null;
        }
        return ['abs' => $real, 'ext' => $ext];
    }

    private function backupsDir(string $slug): string
    {
        return (string) $this->container->get('base_path') . '/storage/backups/files/' . $slug;
    }

    private function backupKey(string $rel): string
    {
        return str_replace('/', '__', $rel);
    }

    private function makeBackup(string $slug, string $rel, string $abs): void
    {
        $dir = $this->backupsDir($slug);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        // پسوند تصادفی: دو بکاپ در یک ثانیه (مثلاً ذخیره و بازیابی پشت سر هم) روی هم نیفتند.
        copy($abs, $dir . '/' . $this->backupKey($rel) . '.' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.bak');
    }

    private function listBackups(string $slug, string $rel): array
    {
        $out = [];
        foreach (glob($this->backupsDir($slug) . '/' . $this->backupKey($rel) . '.*.bak') ?: [] as $f) {
            $out[] = [
                'name' => basename($f),
                'time' => date('Y-m-d H:i:s', (int) filemtime($f)),
                'size' => (int) filesize($f),
            ];
        }
        usort($out, static fn (array $a, array $b): int => strcmp($b['name'], $a['name']));
        return array_slice($out, 0, 10);
    }

    public function fileEdit(Request $request, array $params): Response
    {
        if (!$this->isOwner()) {
            return Response::forbidden('ویرایش فایل ماژول فقط برای مالک است');
        }
        $slug = (string) ($params['slug'] ?? '');
        $rel = (string) $request->input('path', '');
        $guard = $this->guardFile($slug, $rel);
        if ($guard === null) {
            return Response::notFound('فایل پیدا نشد یا قابل ویرایش نیست');
        }
        if (filesize($guard['abs']) > self::MAX_EDIT_BYTES) {
            return Response::forbidden('این فایل بزرگ‌تر از ۵۱۲ کیلوبایت است و از پنل ویرایش نمی‌شود');
        }
        return $this->admin()->page('module-file', [
            'module'  => $this->container->get('loader')->get($slug),
            'slug'    => $slug,
            'path'    => $rel,
            'ext'     => $guard['ext'],
            'content' => (string) file_get_contents($guard['abs']),
            'backups' => $this->listBackups($slug, $rel),
            'flash'   => $request->input('msg'),
            'error'   => $request->input('err'),
            'csrf'    => $this->csrf(),
        ], 'ویرایش فایل ماژول');
    }

    public function fileSave(Request $request, array $params): Response
    {
        if (!$this->isOwner()) {
            return Response::forbidden('ویرایش فایل ماژول فقط برای مالک است');
        }
        $slug = (string) ($params['slug'] ?? '');
        $rel = (string) $request->input('path', '');
        $guard = $this->guardFile($slug, $rel);
        if ($guard === null) {
            return Response::notFound('فایل پیدا نشد یا قابل ویرایش نیست');
        }
        $back = '/admin/modules/file/' . $slug . '?path=' . urlencode($rel);

        if ((string) $request->input('action', 'save') === 'restore') {
            $name = basename((string) $request->input('backup', ''));
            $src = $this->backupsDir($slug) . '/' . $name;
            if (!str_starts_with($name, $this->backupKey($rel) . '.') || !str_ends_with($name, '.bak') || !is_file($src)) {
                return Response::redirect($back . '&err=' . urlencode('بکاپ پیدا نشد'));
            }
            $this->makeBackup($slug, $rel, $guard['abs']);
            copy($src, $guard['abs']);
            $this->log('بازیابی فایل ماژول', $slug . '/' . $rel);
            return Response::redirect($back . '&msg=' . urlencode('فایل از بکاپ بازیابی شد'));
        }

        $content = (string) $request->input('content', '');
        if (strlen($content) > self::MAX_EDIT_BYTES) {
            return Response::redirect($back . '&err=' . urlencode('محتوا بزرگ‌تر از ۵۱۲ کیلوبایت است'));
        }
        $this->makeBackup($slug, $rel, $guard['abs']);
        $tmp = $guard['abs'] . '.tmp-' . bin2hex(random_bytes(3));
        file_put_contents($tmp, $content);
        rename($tmp, $guard['abs']);
        $this->log('ویرایش فایل ماژول', $slug . '/' . $rel);
        return Response::redirect($back . '&msg=' . urlencode('ذخیره شد — قبل از ذخیره بکاپ گرفته شد'));
    }

    /**
     * جزئیات کامل ماژول برای صفحه ویرایش:
     * مقادیر تعریف، روت‌ها (با اجرای صرفِ ثبت روی یک روتر ضبط‌کننده —
     * هندلری اجرا نمی‌شود)، آدرس ویوها، سرویس‌ها، هوک‌ها و آیتم‌های منو.
     */
    private function details(string $slug, array $module): array
    {
        $loader = $this->container->get('loader');

        $routes = [];
        if (isset($module['routes']) && is_callable($module['routes'])) {
            try {
                $recorder = new \Core\Router();
                ($module['routes'])($recorder, $this->container, $slug);
                $routes = $recorder->all();
            } catch (\Throwable) {
                $routes = [];
            }
        }

        $views = [];
        $viewsDir = (string) ($module['dir'] ?? '') . '/views';
        if (is_dir($viewsDir)) {
            foreach (glob($viewsDir . '/*', GLOB_ONLYDIR) ?: [] as $areaDir) {
                $area = basename($areaDir);
                foreach (glob($areaDir . '/*.php') ?: [] as $file) {
                    $name = basename($file, '.php');
                    $views[] = [
                        'address' => $slug . '/' . $area . '/' . $name,
                        'area'    => $area,
                        'name'    => $name,
                        'path'    => 'modules/' . $slug . '/views/' . $area . '/' . $name . '.php',
                    ];
                }
            }
            usort($views, static fn (array $a, array $b): int => $a['address'] <=> $b['address']);
        }

        $menu = array_values(array_filter(
            $loader->adminMenu(),
            static fn (array $item): bool => ($item['module'] ?? '') === $slug,
        ));

        return [
            'enabled'  => $loader->isEnabled($slug),
            'error'    => $loader->errors[$slug] ?? null,
            'dir'      => 'modules/' . $slug,
            'requires' => array_values(array_map('strval', (array) ($module['requires'] ?? []))),
            'services' => array_keys((array) ($module['services'] ?? [])),
            'hooks'    => array_keys((array) ($module['hooks'] ?? [])),
            'routes'   => $routes,
            'views'    => $views,
            'menu'     => $menu,
        ];
    }

    public function update(Request $request, array $params): Response
    {
        $loader = $this->container->get('loader');
        $slug = (string) ($params['slug'] ?? '');
        $ok = $loader->updateMeta($slug, [
            'title'       => trim((string) $request->input('title', '')),
            'description' => trim((string) $request->input('description', '')),
            'version'     => trim((string) $request->input('version', '')),
        ]);
        if ($ok) {
            $this->log('ویرایش مشخصات ماژول', $slug);
        }
        return Response::redirect('/admin/modules?msg=' . urlencode('مشخصات ماژول ویرایش شد'));
    }
}
