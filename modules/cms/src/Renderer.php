<?php
declare(strict_types=1);

namespace Modules\Cms;

use Core\View;

/**
 * موتور نمایش فرانت محتوا:
 * هدر + قالب + فوترِ انتخابیِ هر محتوا (یا پیش‌فرض‌ها) با جانگهدارها:
 * {{content}} {{title}} {{excerpt}} {{date}} {{site_name}} {{menu:slug}}
 */
final class Renderer
{
    public function __construct(
        private readonly Layouts $layouts,
        private readonly Menus $menus,
        private readonly Settings $settings,
    ) {}

    public function renderPost(array $post): string
    {
        $header = $this->pick('header', $post['header_id'] ?? null);
        $footer = $this->pick('footer', $post['footer_id'] ?? null);
        $template = $this->pick('template', $post['template_id'] ?? null);

        $main = $template !== null
            ? $this->fill((string) $template['html'], $post, (string) $post['body'])
            : $this->builtinTemplate($post, (string) $post['body']);

        return $this->shell(
            (string) $post['title'],
            $header !== null ? $this->fill((string) $header['html'], $post, '') : $this->builtinHeader(),
            $main,
            $footer !== null ? $this->fill((string) $footer['html'], $post, '') : $this->builtinFooter(),
        );
    }

    public function renderListing(array $posts, string $title): string
    {
        $body = View::render('cms', 'front', 'index', ['posts' => $posts]);
        $fake = ['title' => $title, 'excerpt' => '', 'created_at' => '', 'header_id' => null, 'footer_id' => null, 'template_id' => null];
        $header = $this->pick('header', null);
        $footer = $this->pick('footer', null);
        $template = $this->pick('template', null);
        $main = $template !== null
            ? $this->fill((string) $template['html'], $fake, $body)
            : '<main class="wrap">' . $body . '</main>';
        return $this->shell(
            $title,
            $header !== null ? $this->fill((string) $header['html'], $fake, '') : $this->builtinHeader(),
            $main,
            $footer !== null ? $this->fill((string) $footer['html'], $fake, '') : $this->builtinFooter(),
        );
    }

    public function renderNotFound(): string
    {
        $fake = ['title' => 'پیدا نشد', 'excerpt' => '', 'created_at' => '', 'header_id' => null, 'footer_id' => null, 'template_id' => null];
        $body = View::render('cms', 'front', 'notfound', []);
        $header = $this->pick('header', null);
        $footer = $this->pick('footer', null);
        $template = $this->pick('template', null);
        $main = $template !== null
            ? $this->fill((string) $template['html'], $fake, $body)
            : '<main class="wrap">' . $body . '</main>';
        return $this->shell(
            'پیدا نشد',
            $header !== null ? $this->fill((string) $header['html'], $fake, '') : $this->builtinHeader(),
            $main,
            $footer !== null ? $this->fill((string) $footer['html'], $fake, '') : $this->builtinFooter(),
        );
    }

    /** چیدمان انتخابی محتوا؛ اگر نبود یا حذف شده بود، پیش‌فرض همان نوع. */
    private function pick(string $kind, mixed $id): ?array
    {
        if (!empty($id)) {
            $row = $this->layouts->find((int) $id);
            if ($row !== null && $row['kind'] === $kind) {
                return $row;
            }
        }
        return $this->layouts->defaultOf($kind);
    }

    private function fill(string $html, array $post, string $content): string
    {
        $html = strtr($html, [
            '{{content}}'   => $content,
            '{{title}}'     => e((string) ($post['title'] ?? '')),
            '{{excerpt}}'   => e((string) ($post['excerpt'] ?? '')),
            '{{date}}'      => e((string) ($post['created_at'] ?? '')),
            '{{site_name}}' => e($this->settings->get('site_name', 'وصله')),
        ]);
        return (string) preg_replace_callback(
            '/\{\{menu:([a-z0-9_-]+)\}\}/',
            fn (array $m): string => $this->menus->render($m[1]),
            $html
        );
    }

    private function builtinHeader(): string
    {
        $name = e($this->settings->get('site_name', 'وصله'));
        return '<header class="site-header"><div class="wrap"><a class="brand" href="/">' . $name
            . '</a><nav>' . $this->menus->render('main') . '</nav></div></header>';
    }

    private function builtinFooter(): string
    {
        return '<footer class="site-footer"><div class="wrap"><small>ساخته‌شده با وصله</small></div></footer>';
    }

    private function builtinTemplate(array $post, string $body): string
    {
        return '<main class="wrap"><article class="post"><h1>' . e((string) $post['title'])
            . '</h1><div class="body">' . $body . '</div></article></main>';
    }

    private function shell(string $title, string $header, string $main, string $footer): string
    {
        $site = e($this->settings->get('site_name', 'وصله'));
        return '<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . e($title) . ' | ' . $site . '</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: Tahoma, system-ui, sans-serif; margin: 0; background: #f6f7f9; color: #1f2937; line-height: 1.9; }
  .wrap { max-width: 860px; margin: 0 auto; padding: 0 16px; }
  .wrap.wide { max-width: 1180px; }
  .site-header { background: #1d2327; color: #fff; }
  .site-header .wrap { display: flex; align-items: center; gap: 22px; padding-top: 12px; padding-bottom: 12px; flex-wrap: wrap; }
  .site-header .brand { font-weight: bold; font-size: 1.15rem; color: #fff; text-decoration: none; }
  .cms-menu { list-style: none; display: flex; gap: 4px; margin: 0; padding: 0; flex-wrap: wrap; }
  .cms-menu > li { position: relative; }
  .cms-menu a { color: #e5e7eb; text-decoration: none; padding: 5px 10px; border-radius: 8px; display: block; font-size: .95rem; }
  .cms-menu a:hover { background: #2c3338; color: #fff; }
  .cms-submenu { list-style: none; margin: 0; padding: 6px; position: absolute; top: 100%; inset-inline-start: 0; background: #2c3338; border-radius: 10px; min-width: 180px; display: none; z-index: 20; }
  .cms-menu > li:hover .cms-submenu { display: block; }
  main .post-card, main article.post { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px 22px; margin: 22px 0; }
  .post-card h2 { margin: 0 0 6px; font-size: 1.2rem; }
  .post-card h2 a { color: #1f2937; text-decoration: none; }
  .post-card p { margin: 0; color: #4b5563; }
  .post-card time, article.post time { color: #9ca3af; font-size: .85rem; }
  article.post h1 { margin-top: 0; font-size: 1.6rem; }
  .site-footer { background: #1d2327; color: #c3c4c7; margin-top: 30px; }
  .site-footer .wrap { padding-top: 18px; padding-bottom: 18px; text-align: center; }
  .site-footer .cms-menu { justify-content: center; }
  .site-footer a { color: #e5e7eb; }
</style>
</head>
<body>
' . $header . '
' . $main . '
' . $footer . '
</body>
</html>';
    }
}
