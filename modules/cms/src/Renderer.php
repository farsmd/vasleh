<?php
declare(strict_types=1);

namespace Modules\Cms;

use Core\View;

/**
 * موتور نمایش فرانت محتوا:
 * هدر + قالب + فوترِ انتخابیِ هر محتوا (یا پیش‌فرض‌ها) با جانگهدارها:
 * {{content}} {{title}} {{excerpt}} {{date}} {{site_name}} {{menu:slug}}
 * استایل‌ها: CSS کلی سایت از /cms.css (قابل ویرایش در پنل) + CSS اختصاصی
 * چیدمان‌های استفاده‌شده در همان صفحه، به‌صورت درون‌خطی.
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
        // محتوای «بدون قالب»: فقط خود متن، بدون هدر، فوتر، قالب و استایل سایت —
        // برای لندینگ و محتوای مستقلی که سند HTML خودش را دارد.
        if (!empty($post['bare'])) {
            return (string) $post['body'];
        }
        return $this->compose($post, (string) $post['body'], (string) $post['title']);
    }

    public function renderListing(array $posts, string $title): string
    {
        $body = View::render('cms', 'front', 'index', ['posts' => $posts]);
        $fake = ['title' => $title, 'excerpt' => '', 'created_at' => '', 'header_id' => null, 'footer_id' => null, 'template_id' => null];
        return $this->compose($fake, $body, $title);
    }

    public function renderNotFound(): string
    {
        $body = View::render('cms', 'front', 'notfound', []);
        $fake = ['title' => 'پیدا نشد', 'excerpt' => '', 'created_at' => '', 'header_id' => null, 'footer_id' => null, 'template_id' => null];
        return $this->compose($fake, $body, 'پیدا نشد');
    }

    private function compose(array $post, string $body, string $title): string
    {
        $header = $this->pick('header', $post['header_id'] ?? null);
        $footer = $this->pick('footer', $post['footer_id'] ?? null);
        $template = $this->pick('template', $post['template_id'] ?? null);

        $main = $template !== null
            ? $this->fill((string) $template['html'], $post, $body)
            : $this->builtinTemplate($post, $body);

        $extraCss = '';
        foreach ([$header, $template, $footer] as $row) {
            if ($row !== null && trim((string) ($row['css'] ?? '')) !== '') {
                $extraCss .= "\n/* layout: " . (string) $row['slug'] . " */\n" . (string) $row['css'];
            }
        }

        return $this->shell(
            $title,
            $header !== null ? $this->fill((string) $header['html'], $post, '') : $this->builtinHeader(),
            $main,
            $footer !== null ? $this->fill((string) $footer['html'], $post, '') : $this->builtinFooter(),
            $extraCss,
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

    private function shell(string $title, string $header, string $main, string $footer, string $extraCss): string
    {
        $site = e($this->settings->get('site_name', 'وصله'));
        // تم یکپارچه: نسخه CSS با تغییر تم هم عوض می‌شود تا کش مرورگر متغیرهای تازه را بگیرد.
        $theme = \Modules\Admin\AdminTheme::load(dirname(__DIR__, 3));
        $cssVersion = substr(md5($this->settings->get('site_css', '') . json_encode($theme)), 0, 8);
        $darkClass = (($theme['default_theme'] ?? 'dark') === 'dark') ? ' class="dark"' : '';
        $style = $extraCss !== '' ? "\n<style>" . $extraCss . "\n</style>" : '';
        return '<!DOCTYPE html>
<html lang="fa" dir="rtl"' . $darkClass . '>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . e($title) . ' | ' . $site . '</title>
<script>try{var _t=localStorage.getItem(\'vasleh_theme\');if(_t===\'light\'){document.documentElement.classList.remove(\'dark\')}else if(_t===\'dark\'){document.documentElement.classList.add(\'dark\')}}catch(e){}</script>
<link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
<link rel="stylesheet" href="/cms.css?v=' . $cssVersion . '">' . $style . '
</head>
<body>
' . $header . '
' . $main . '
' . $footer . '
</body>
</html>';
    }
}
