<?php
declare(strict_types=1);

namespace Modules\Cms;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** مدیریت محتواها در پنل (ورود و CSRF با گاردهای سراسری /admin است). */
final class AdminCmsController
{
    public function __construct(private readonly Container $container) {}

    private function posts(): Posts
    {
        return $this->container->get('cms.posts');
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
        return $this->page('list', [
            'posts' => $this->posts()->allAdmin(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'مدیریت محتوا');
    }

    public function createForm(Request $request): Response
    {
        return $this->page('form', [
            'post'  => ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'type' => 'post', 'status' => 'draft'],
            'action' => '/admin/cms/create',
            'error' => null,
        ], 'محتوای تازه');
    }

    public function create(Request $request): Response
    {
        [$data, $error] = $this->validate($request, 0);
        if ($error !== null) {
            return $this->page('form', [
                'post'   => $data,
                'action' => '/admin/cms/create',
                'error'  => $error,
            ], 'محتوای تازه');
        }
        $id = $this->posts()->create($data);
        if ($data['slug'] === '') {
            $data['slug'] = 'content-' . $id;
            $this->posts()->update($id, $data);
        }
        $this->log('ساخت محتوا', (string) $data['title']);
        return Response::redirect('/admin/cms?msg=' . urlencode('محتوا ساخته شد'));
    }

    public function editForm(Request $request, array $params): Response
    {
        $post = $this->posts()->find((int) ($params['id'] ?? 0));
        if ($post === null) {
            return Response::notFound('محتوا پیدا نشد');
        }
        return $this->page('form', [
            'post'   => $post,
            'action' => '/admin/cms/edit/' . (int) $post['id'],
            'error'  => null,
        ], 'ویرایش محتوا');
    }

    public function update(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $post = $this->posts()->find($id);
        if ($post === null) {
            return Response::notFound('محتوا پیدا نشد');
        }
        [$data, $error] = $this->validate($request, $id);
        if ($error !== null) {
            $data['id'] = $id;
            return $this->page('form', [
                'post'   => $data,
                'action' => '/admin/cms/edit/' . $id,
                'error'  => $error,
            ], 'ویرایش محتوا');
        }
        if ($data['slug'] === '') {
            $data['slug'] = (string) $post['slug'];
        }
        $this->posts()->update($id, $data);
        $this->log('ویرایش محتوا', (string) $data['title']);
        return Response::redirect('/admin/cms?msg=' . urlencode('محتوا ذخیره شد'));
    }

    public function delete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $post = $this->posts()->find($id);
        if ($post !== null) {
            $this->posts()->delete($id);
            $this->log('حذف محتوا', (string) $post['title']);
        }
        return Response::redirect('/admin/cms?msg=' . urlencode('محتوا حذف شد'));
    }

    /** @return array{0: array<string, string>, 1: ?string} داده پاک + خطا (اگر هست) */
    private function validate(Request $request, int $selfId): array
    {
        $data = [
            'title'   => trim((string) $request->input('title', '')),
            'slug'    => trim((string) $request->input('slug', '')),
            'excerpt' => trim((string) $request->input('excerpt', '')),
            'body'    => (string) $request->input('body', ''),
            'type'    => (string) $request->input('type', 'post'),
            'status'  => (string) $request->input('status', 'draft'),
        ];
        if ($data['title'] === '') {
            return [$data, 'عنوان را بنویسید'];
        }
        if (!in_array($data['type'], ['post', 'page'], true)) {
            $data['type'] = 'post';
        }
        if (!in_array($data['status'], ['draft', 'published'], true)) {
            $data['status'] = 'draft';
        }
        if ($data['slug'] === '') {
            $data['slug'] = Posts::slugify($data['title']);
        }
        if ($data['slug'] !== '' && $this->posts()->slugTaken($data['slug'], $selfId)) {
            return [$data, 'این نامک قبلاً برای محتوای دیگری استفاده شده است'];
        }
        return [$data, null];
    }
}
