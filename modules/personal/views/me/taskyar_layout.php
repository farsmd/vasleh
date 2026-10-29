<?php
// پوسته صفحه‌های فرعی /me: سربرگ مینیمال (فقط عنوان + بازگشت)، بدون
// هدر چسبان قدیمی و بدون نوار تب‌ها. ناوبری اصلی همان داک پایین است.
$__theme = \Modules\Admin\AdminTheme::load(dirname(__DIR__, 4));
$__dark = ($__theme['default_theme'] ?? 'dark') === 'dark';
$__meStyle = (isset($me_style) && isset(\Modules\Personal\Personal::STYLES[$me_style])) ? $me_style : 'darkPrism';
$__themeColors = ['darkPrism' => '#090A0F', 'zenCanvas' => '#F6F4F0', 'swissPop' => '#F2F3F7', 'monoGrid' => '#111215'];
$__themeColor = $__themeColors[$__meStyle] ?? '#090A0F';
$__assetV = '8.2.23';
$__vf = dirname(__DIR__, 4) . '/version.json';
if (is_file($__vf)) {
    $__vp = json_decode((string) file_get_contents($__vf), true);
    if (is_array($__vp)) { $__assetV = (int) ($__vp['core'] ?? 0) . '.' . (int) ($__vp['system'] ?? 0) . '.' . (int) ($__vp['other'] ?? 0); }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="<?= $__meStyle ?>"<?= $__dark ? ' class="dark"' : '' ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/me/manifest.webmanifest">
<meta name="theme-color" content="<?= $__themeColor ?>">
<link rel="apple-touch-icon" href="/assets/pwa/apple-touch-icon.png">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="تسک‌یار">
<link rel="stylesheet" href="/assets/taskyar/me-shell.css?v=<?= e($__assetV) ?>">
<title><?= e($title) ?> | تسک‌یار</title>
<script>try{var _t=localStorage.getItem('vasleh_theme');if(_t==='light'){document.documentElement.classList.remove('dark')}else if(_t==='dark'){document.documentElement.classList.add('dark')}}catch(e){}</script>
<link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
<link rel="stylesheet" href="/assets/taskyar/fa/css/all.min.css">
<link rel="stylesheet" href="/assets/taskyar/tailwind.css">
<style>
    <?= \Modules\Admin\AdminTheme::varsCss($__theme) ?>
    body { font-family: 'Vazirmatn', sans-serif; }
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: rgba(0,0,0,0.03); }
    ::-webkit-scrollbar-thumb { background: rgba(140, 140, 160, 0.25); border-radius: 999px; }
    /* محتوای تسک‌یار با متغیرهای تم هماهنگ می‌شود (متغیرها با تاریک/روشن عوض می‌شوند) */
    main .bg-white, nav .bg-white{background:var(--admin-panel-bg)}
    main .bg-slate-50{background:var(--admin-main-bg)}
    main .text-slate-800, main .text-slate-900{color:var(--admin-text)}
    main .text-slate-500, main .text-slate-600, nav .text-slate-600{color:var(--admin-muted)}
    main .border-slate-200{border-color:var(--admin-border)}
</style>
</head>
<body class="min-h-screen flex flex-col<?= empty($public) ? ' me-has-dock' : '' ?>" style="background:var(--admin-main-bg);color:var(--admin-text)">
<header class="me-sub-header max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8">
    <?php if (empty($public)): ?>
    <a href="/me" class="me-sub-back">بازگشت ←</a>
    <?php endif; ?>
    <h1 class="me-sub-title"><?= e($title) ?></h1>
</header>
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <?php if (!empty($flash)): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium rounded-xl px-4 py-3"><?= e($flash) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl px-4 py-3"><?= e($error) ?></div><?php endif; ?>
    <?= $content ?>
</main>
<?php if (empty($public)): ?>
<nav class="me-dock" aria-label="ناوبری تسک‌یار">
    <a href="/me" class="me-dock-item<?= ($tab ?? '') === 'dashboard' ? ' me-dock-active' : '' ?>">
        <i class="fa-solid fa-gauge-high"></i><span>داشبورد</span>
    </a>
    <a href="/me/calendar" class="me-dock-item<?= ($tab ?? '') === 'calendar' ? ' me-dock-active' : '' ?>">
        <i class="fa-regular fa-calendar-days"></i><span>تقویم</span>
    </a>
    <span class="me-dock-fab-wrap">
        <a href="/me#add" class="me-dock-fab" title="افزودن سریع تسک"><i class="fa-solid fa-plus"></i></a>
    </span>
    <a href="/me/notes" class="me-dock-item<?= ($tab ?? '') === 'notes' ? ' me-dock-active' : '' ?>">
        <i class="fa-regular fa-note-sticky"></i><span>یادداشت</span>
    </a>
    <a href="/me/settings" class="me-dock-item<?= ($tab ?? '') === 'settings' ? ' me-dock-active' : '' ?>">
        <i class="fa-solid fa-gear"></i><span>تنظیمات</span>
    </a>
</nav>
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', function () {
    navigator.serviceWorker.register('/me/sw.js?v=<?= e($__assetV) ?>', { scope: '/me/' }).catch(function () {});
  });
}
</script>
<?php endif; ?>
</body>
</html>
