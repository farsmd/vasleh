<?php
declare(strict_types=1);

namespace Modules\Cms;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** مدیریت منوهای سایت و آیتم‌هایشان. */
final class AdminMenusController
{
    public function __construct(private readonly Container $container) {}

    private function menus(): Menus
    {
        return $this->container->get('cms.menus');
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

    public function index(Request $request): Response
    {
        $menus = $this->menus()->all();
        foreach ($menus as &$menu) {
            $menu['count'] = count($this->menus()->items((int) $menu['id']));
        }
        unset($menu);
        return $this->page('menus', [
            'menus' => $menus,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'منوهای سایت');
    }

    public function create(Request $request): Response
    {
        $name = trim((string) $request->input('name', ''));
        $slug = trim((string) $request->input('slug', ''));
        if ($name === '') {
            return Response::redirect('/admin/cms/menus?err=' . urlencode('نام منو را بنویسید'));
        }
        if ($slug === '') {
            $slug = Posts::slugify($name);
        }
        if (!preg_match('/^[a-z0-9_-]+$/i', $slug)) {
            return Response::redirect('/admin/cms/menus?err=' . urlencode('نامک منو باید لاتین باشد'));
        }
        if ($this->menus()->findBySlug($slug) !== null) {
            return Response::redirect('/admin/cms/menus?err=' . urlencode('این نامک قبلاً استفاده شده است'));
        }
        $id = $this->menus()->create($name, $slug);
        $this->log('ساخت منو', $name);
        return Response::redirect('/admin/cms/menus/' . $id . '?msg=' . urlencode('منو ساخته شد؛ آیتم‌هایش را اضافه کنید'));
    }

    public function delete(Request $request, array $params): Response
    {
        $menu = $this->menus()->find((int) ($params['id'] ?? 0));
        if ($menu !== null) {
            $this->menus()->delete((int) $menu['id']);
            $this->log('حذف منو', (string) $menu['name']);
        }
        return Response::redirect('/admin/cms/menus?msg=' . urlencode('منو حذف شد'));
    }

    public function items(Request $request, array $params): Response
    {
        $menu = $this->menus()->find((int) ($params['id'] ?? 0));
        if ($menu === null) {
            return Response::notFound('منو پیدا نشد');
        }
        $items = $this->menus()->items((int) $menu['id']);
        $tops = array_filter($items, static fn (array $i): bool => (int) $i['parent_id'] === 0);
        return $this->page('menu-items', [
            'menu'  => $menu,
            'items' => $items,
            'tops'  => $tops,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'آیتم‌های منو');
    }

    public function addItem(Request $request, array $params): Response
    {
        $menu = $this->menus()->find((int) ($params['id'] ?? 0));
        if ($menu === null) {
            return Response::notFound('منو پیدا نشد');
        }
        $label = trim((string) $request->input('label', ''));
        $url = trim((string) $request->input('url', '/')) ?: '/';
        if ($label === '') {
            return Response::redirect('/admin/cms/menus/' . $menu['id'] . '?err=' . urlencode('برچسب آیتم را بنویسید'));
        }
        $this->menus()->addItem(
            (int) $menu['id'],
            (int) $request->input('parent_id', 0),
            $label,
            $url,
            (int) $request->input('sort', 0)
        );
        $this->log('افزودن آیتم منو', (string) $menu['name'] . ': ' . $label);
        return Response::redirect('/admin/cms/menus/' . $menu['id'] . '?msg=' . urlencode('آیتم اضافه شد'));
    }

    public function updateItem(Request $request, array $params): Response
    {
        $menu = $this->menus()->find((int) ($params['id'] ?? 0));
        if ($menu === null) {
            return Response::notFound('منو پیدا نشد');
        }
        $itemId = (int) $request->input('item_id', 0);
        $label = trim((string) $request->input('label', ''));
        if ($itemId > 0 && $label !== '') {
            $this->menus()->updateItem(
                $itemId,
                (int) $request->input('parent_id', 0),
                $label,
                trim((string) $request->input('url', '/')) ?: '/',
                (int) $request->input('sort', 0)
            );
        }
        return Response::redirect('/admin/cms/menus/' . $menu['id'] . '?msg=' . urlencode('آیتم ذخیره شد'));
    }

    public function deleteItem(Request $request, array $params): Response
    {
        $menu = $this->menus()->find((int) ($params['id'] ?? 0));
        if ($menu === null) {
            return Response::notFound('منو پیدا نشد');
        }
        $itemId = (int) $request->input('item_id', 0);
        if ($itemId > 0) {
            $this->menus()->deleteItem($itemId);
        }
        return Response::redirect('/admin/cms/menus/' . $menu['id'] . '?msg=' . urlencode('آیتم حذف شد'));
    }
}
