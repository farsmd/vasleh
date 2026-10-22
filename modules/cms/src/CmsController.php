<?php
declare(strict_types=1);

namespace Modules\Cms;

use Core\Container;
use Core\Request;
use Core\Response;

/** صفحات عمومی: ریشه (طبق تنظیم)، وبلاگ و برگه‌ها — همه با موتور رندر چیدمان‌ها. */
final class CmsController
{
    public function __construct(private readonly Container $container) {}

    private function posts(): Posts
    {
        return $this->container->get('cms.posts');
    }

    private function renderer(): Renderer
    {
        return $this->container->get('cms.render');
    }

    /** ریشه سایت — فقط وقتی در تنظیمات محتوا صفحه اصلی تعیین شده باشد ثبت می‌شود. */
    public function home(Request $request): Response
    {
        $home = $this->container->get('cms.settings')->get('homepage', '');
        if ($home === 'blog') {
            return Response::html($this->renderer()->renderListing($this->posts()->published('post'), 'وبلاگ'));
        }
        if (ctype_digit($home)) {
            $post = $this->posts()->findPublished((int) $home);
            if ($post !== null) {
                return Response::html($this->renderer()->renderPost($post));
            }
        }
        return Response::html($this->renderer()->renderListing($this->posts()->published('post'), 'وبلاگ'));
    }

    public function blogIndex(Request $request): Response
    {
        return Response::html($this->renderer()->renderListing($this->posts()->published('post'), 'وبلاگ'));
    }

    /** CSS کلی سایت — از تنظیمات ماژول خوانده و با نوع محتوای درست سرو می‌شود. */
    public function stylesheet(Request $request): Response
    {
        $css = $this->container->get('cms.settings')->get('site_css', '');
        return Response::html($css)->withHeader('Content-Type', 'text/css; charset=utf-8');
    }

    public function blogShow(Request $request, array $params): Response
    {
        // مسیر خام URL-encoded می‌رسد؛ نامک‌های فارسی باید دیکود شوند.
        $post = $this->posts()->findPublishedBySlug(rawurldecode((string) ($params['slug'] ?? '')), 'post');
        if ($post === null) {
            return Response::html($this->renderer()->renderNotFound(), 404);
        }
        return Response::html($this->renderer()->renderPost($post));
    }

    public function pageShow(Request $request, array $params): Response
    {
        $post = $this->posts()->findPublishedBySlug(rawurldecode((string) ($params['slug'] ?? '')), 'page');
        if ($post === null) {
            return Response::html($this->renderer()->renderNotFound(), 404);
        }
        return Response::html($this->renderer()->renderPost($post));
    }
}
