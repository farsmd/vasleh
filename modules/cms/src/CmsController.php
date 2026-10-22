<?php
declare(strict_types=1);

namespace Modules\Cms;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** صفحات عمومی وبلاگ و برگه‌ها. */
final class CmsController
{
    public function __construct(private readonly Container $container) {}

    private function posts(): Posts
    {
        return $this->container->get('cms.posts');
    }

    private function page(string $view, array $data, string $title, int $status = 200): Response
    {
        $content = View::render('cms', 'front', $view, $data);
        return Response::html(View::render('cms', 'front', 'layout', [
            'title'   => $title,
            'content' => $content,
        ]), $status);
    }

    public function blogIndex(Request $request): Response
    {
        return $this->page('index', ['posts' => $this->posts()->published('post')], 'وبلاگ');
    }

    public function blogShow(Request $request, array $params): Response
    {
        // مسیر خام URL-encoded می‌رسد؛ نامک‌های فارسی باید دیکود شوند.
        $post = $this->posts()->findPublishedBySlug(rawurldecode((string) ($params['slug'] ?? '')), 'post');
        if ($post === null) {
            return $this->page('notfound', [], 'پیدا نشد', 404);
        }
        return $this->page('show', ['post' => $post], (string) $post['title']);
    }

    public function pageShow(Request $request, array $params): Response
    {
        $post = $this->posts()->findPublishedBySlug(rawurldecode((string) ($params['slug'] ?? '')), 'page');
        if ($post === null) {
            return $this->page('notfound', [], 'پیدا نشد', 404);
        }
        return $this->page('show', ['post' => $post], (string) $post['title']);
    }
}
