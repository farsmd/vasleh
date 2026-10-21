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
            $loader->setEnabled($slug, !$loader->isEnabled($slug));
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
        return Response::redirect('/admin/modules?msg=' . urlencode('ماژول ساخته شد و روتش زنده است'));
    }

    public function edit(Request $request, array $params): Response
    {
        $module = $this->container->get('loader')->get((string) ($params['slug'] ?? ''));
        if ($module === null) {
            return Response::notFound('ماژولی با این نام نیست');
        }
        return $this->admin()->page('module-edit', ['module' => $module, 'csrf' => $this->csrf()], 'ویرایش ماژول');
    }

    public function update(Request $request, array $params): Response
    {
        $loader = $this->container->get('loader');
        $slug = (string) ($params['slug'] ?? '');
        $loader->updateMeta($slug, [
            'title'       => trim((string) $request->input('title', '')),
            'description' => trim((string) $request->input('description', '')),
            'version'     => trim((string) $request->input('version', '')),
        ]);
        return Response::redirect('/admin/modules?msg=' . urlencode('مشخصات ماژول ویرایش شد'));
    }
}
