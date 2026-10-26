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

    private function layoutsData(): array
    {
        $layouts = $this->container->get('cms.layouts');
        return [
            'headers'   => $layouts->all('header'),
            'footers'   => $layouts->all('footer'),
            'templates' => $layouts->all('template'),
        ];
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
            'post'    => ['title' => '', 'slug' => '', 'excerpt' => '', 'body' => '', 'type' => 'post', 'status' => 'draft', 'header_id' => null, 'footer_id' => null, 'template_id' => null, 'bare' => 0],
            'action'  => '/admin/cms/create',
            'error'   => null,
            'layouts' => $this->layoutsData(),
        ], 'محتوای تازه');
    }

    public function create(Request $request): Response
    {
        [$data, $error] = $this->validate($request, 0);
        if ($error !== null) {
            return $this->page('form', [
                'post'    => $data,
                'action'  => '/admin/cms/create',
                'error'   => $error,
                'layouts' => $this->layoutsData(),
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
            'post'    => $post,
            'action'  => '/admin/cms/edit/' . (int) $post['id'],
            'error'   => null,
            'layouts' => $this->layoutsData(),
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
                'post'    => $data,
                'action'  => '/admin/cms/edit/' . $id,
                'error'   => $error,
                'layouts' => $this->layoutsData(),
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

    // ---------- صفحات خطا ----------

    private function errors(): ErrorPages
    {
        return $this->container->get('cms.errors');
    }

    public function errorsList(Request $request): Response
    {
        return $this->page('errors', [
            'pages' => $this->errors()->all(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'صفحات خطا');
    }

    public function errorForm(Request $request, array $params): Response
    {
        $code = (int) ($params['code'] ?? 0);
        $row = $code > 0 ? $this->errors()->get($code) : null;
        return $this->page('error-form', [
            'code' => $code,
            'page' => $row ?? ['title' => ErrorPages::STANDARD[$code] ?? '', 'body' => ErrorPages::defaultBody($code), 'active' => 1],
            'isNew' => $row === null,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'ویرایش صفحه خطا');
    }

    public function errorSave(Request $request, array $params): Response
    {
        $code = (int) ($request->input('code', $params['code'] ?? 0));
        $title = trim((string) $request->input('title', ''));
        $body = trim((string) $request->input('body', ''));
        if ($code < 100 || $code > 599 || $title === '' || $body === '') {
            return $this->page('error-form', [
                'code' => $code,
                'page' => ['title' => $title, 'body' => $body, 'active' => 1],
                'isNew' => $this->errors()->get($code) === null,
                'error' => 'کد باید عدد سه‌رقمی (۱۰۰ تا ۵۹۹) و عنوان و بدنه نباید خالی باشند.',
            ], 'ویرایش صفحه خطا');
        }
        $this->errors()->save($code, $title, $body, (string) $request->input('active', '') === '1');
        $this->log('صفحه خطا', 'کد ' . $code);
        return Response::redirect('/admin/cms/errors?msg=' . urlencode('صفحه خطا ذخیره شد'));
    }

    public function errorDelete(Request $request, array $params): Response
    {
        $code = (int) ($params['code'] ?? 0);
        if ($code > 0) {
            $this->errors()->delete($code);
            $this->log('حذف صفحه خطا', 'کد ' . $code);
        }
        return Response::redirect('/admin/cms/errors?msg=' . urlencode('صفحه خطا به پیش‌فرض برگشت'));
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
        $data['header_id'] = $this->layoutId($request, 'header_id', 'header');
        $data['footer_id'] = $this->layoutId($request, 'footer_id', 'footer');
        $data['template_id'] = $this->layoutId($request, 'template_id', 'template');
        $data['bare'] = $request->input('bare') ? 1 : 0;
        return [$data, null];
    }

    /** آی‌دی چیدمان از فرم: خالی/نامعتبر = null (یعنی پیش‌فرض همان نوع). */
    private function layoutId(Request $request, string $field, string $kind): ?int
    {
        $id = (int) $request->input($field, 0);
        if ($id <= 0) {
            return null;
        }
        $row = $this->container->get('cms.layouts')->find($id);
        return ($row !== null && $row['kind'] === $kind) ? $id : null;
    }

    // ---------- تنظیمات محتوا ----------

    public function settingsForm(Request $request): Response
    {
        $settings = $this->container->get('cms.settings');
        return $this->page('settings', [
            'siteName'  => $settings->get('site_name', 'وصله'),
            'homepage'  => $settings->get('homepage', ''),
            'published' => array_merge($this->posts()->published('page'), $this->posts()->published('post')),
            'flash'     => $request->input('msg'),
        ], 'تنظیمات محتوا');
    }

    public function settingsSave(Request $request): Response
    {
        $settings = $this->container->get('cms.settings');
        $settings->set('site_name', trim((string) $request->input('site_name', '')) ?: 'وصله');
        $home = (string) $request->input('homepage', '');
        if ($home !== '' && $home !== 'blog' && !(ctype_digit($home) && $this->posts()->findPublished((int) $home) !== null)) {
            $home = '';
        }
        $settings->set('homepage', $home);
        $this->log('تنظیمات محتوا', 'صفحه اصلی: ' . ($home === '' ? 'پیش‌فرض سیستم' : $home));
        return Response::redirect('/admin/cms/settings?msg=' . urlencode('تنظیمات ذخیره شد'));
    }
}
