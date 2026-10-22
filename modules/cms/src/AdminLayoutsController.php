<?php
declare(strict_types=1);

namespace Modules\Cms;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** مدیریت هدرها، فوترها و قالب‌های نمایش (چیدمان‌ها). */
final class AdminLayoutsController
{
    private const KIND_LABELS = ['header' => 'هدر', 'footer' => 'فوتر', 'template' => 'قالب'];

    public function __construct(private readonly Container $container) {}

    private function layouts(): Layouts
    {
        return $this->container->get('cms.layouts');
    }

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('cms', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => $title,
            'content' => $content,
            'menu'    => $this->container->get('loader')->adminMenu(),
            'user'    => $session->user(),
            'csrf'    => $session->csrfToken(),
        ]));
    }

    private function log(string $action, string $target): void
    {
        if (!$this->container->has('logger') || !$this->container->has('session')) {
            return;
        }
        $actor = (string) ($this->container->get('session')->user()['username'] ?? '');
        if ($actor !== '') {
            $this->container->get('logger')->user($actor, $action, $target);
        }
    }

    private function kind(Request $request): string
    {
        $kind = (string) $request->input('kind', 'template');
        return in_array($kind, Layouts::KINDS, true) ? $kind : 'template';
    }

    public function index(Request $request): Response
    {
        return $this->page('layouts', [
            'headers'   => $this->layouts()->all('header'),
            'footers'   => $this->layouts()->all('footer'),
            'templates' => $this->layouts()->all('template'),
            'labels'    => self::KIND_LABELS,
            'flash'     => $request->input('msg'),
            'error'     => $request->input('err'),
        ], 'هدر، فوتر و قالب‌ها');
    }

    public function createForm(Request $request): Response
    {
        $kind = $this->kind($request);
        return $this->page('layout-form', [
            'layout' => ['name' => '', 'slug' => '', 'html' => '', 'kind' => $kind, 'is_default' => 0],
            'action' => '/admin/cms/layouts/create',
            'labels' => self::KIND_LABELS,
            'error'  => null,
        ], 'چیدمان تازه');
    }

    public function create(Request $request): Response
    {
        $kind = $this->kind($request);
        [$data, $error] = $this->validate($request, $kind, 0);
        if ($error !== null) {
            $data['kind'] = $kind;
            return $this->page('layout-form', [
                'layout' => $data,
                'action' => '/admin/cms/layouts/create',
                'labels' => self::KIND_LABELS,
                'error'  => $error,
            ], 'چیدمان تازه');
        }
        $this->layouts()->create($kind, $data['name'], $data['slug'], $data['html'], !empty($request->input('is_default')));
        $this->log('ساخت چیدمان', self::KIND_LABELS[$kind] . ': ' . $data['name']);
        return Response::redirect('/admin/cms/layouts?msg=' . urlencode('چیدمان ساخته شد'));
    }

    public function editForm(Request $request, array $params): Response
    {
        $layout = $this->layouts()->find((int) ($params['id'] ?? 0));
        if ($layout === null) {
            return Response::notFound('چیدمان پیدا نشد');
        }
        return $this->page('layout-form', [
            'layout' => $layout,
            'action' => '/admin/cms/layouts/edit/' . (int) $layout['id'],
            'labels' => self::KIND_LABELS,
            'error'  => null,
        ], 'ویرایش چیدمان');
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $layout = $this->layouts()->find($id);
        if ($layout === null) {
            return Response::notFound('چیدمان پیدا نشد');
        }
        [$data, $error] = $this->validate($request, (string) $layout['kind'], $id);
        if ($error !== null) {
            $data['kind'] = $layout['kind'];
            $data['is_default'] = $layout['is_default'];
            return $this->page('layout-form', [
                'layout' => $data,
                'action' => '/admin/cms/layouts/edit/' . $id,
                'labels' => self::KIND_LABELS,
                'error'  => $error,
            ], 'ویرایش چیدمان');
        }
        $this->layouts()->update($id, $data['name'], $data['slug'], $data['html']);
        if (!empty($request->input('is_default'))) {
            $this->layouts()->setDefault($id);
        }
        $this->log('ویرایش چیدمان', self::KIND_LABELS[(string) $layout['kind']] . ': ' . $data['name']);
        return Response::redirect('/admin/cms/layouts?msg=' . urlencode('چیدمان ذخیره شد'));
    }

    public function setDefault(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $layout = $this->layouts()->find($id);
        if ($layout !== null) {
            $this->layouts()->setDefault($id);
            $this->log('چیدمان پیش‌فرض', self::KIND_LABELS[(string) $layout['kind']] . ': ' . $layout['name']);
        }
        return Response::redirect('/admin/cms/layouts?msg=' . urlencode('پیش‌فرض شد'));
    }

    public function delete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $layout = $this->layouts()->find($id);
        if ($layout !== null) {
            $this->layouts()->delete($id);
            $this->log('حذف چیدمان', self::KIND_LABELS[(string) $layout['kind']] . ': ' . $layout['name']);
        }
        return Response::redirect('/admin/cms/layouts?msg=' . urlencode('چیدمان حذف شد'));
    }

    /** @return array{0: array<string, string>, 1: ?string} */
    private function validate(Request $request, string $kind, int $selfId): array
    {
        $data = [
            'name' => trim((string) $request->input('name', '')),
            'slug' => trim((string) $request->input('slug', '')),
            'html' => (string) $request->input('html', ''),
        ];
        if ($data['name'] === '') {
            return [$data, 'نام را بنویسید'];
        }
        if ($data['slug'] === '') {
            $data['slug'] = Posts::slugify($data['name']);
        }
        if ($data['slug'] === '' || !preg_match('/^[a-z0-9_-]+$/i', $data['slug'])) {
            return [$data, 'نامک چیدمان باید لاتین باشد (حرف، عدد، خط تیره)'];
        }
        if ($this->layouts()->slugTaken($kind, $data['slug'], $selfId)) {
            return [$data, 'این نامک برای همین نوع چیدمان قبلاً استفاده شده است'];
        }
        return [$data, null];
    }
}
