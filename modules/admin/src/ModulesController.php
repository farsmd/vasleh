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
            return Response::forbidden('نصب ماژول فقط برای مالک است');
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
        return Response::redirect('/admin/modules');
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
        ], 'ویرایش ماژول');
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
