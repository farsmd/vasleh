<?php
// تم یکپارچه: متغیرهای قالب ادمین روی بخش شخصی هم می‌نشیند؛
// حالت تاریک/روشن با همان کلید localStorage ادمین خوانده می‌شود.
$__theme = \Modules\Admin\AdminTheme::load(dirname(__DIR__, 4));
$__dark = ($__theme['default_theme'] ?? 'dark') === 'dark';
$__meStyle = (isset($me_style) && isset(\Modules\Personal\Personal::STYLES[$me_style])) ? $me_style : 'darkPrism';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="<?= $__meStyle ?>"<?= $__dark ? ' class="dark"' : '' ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/me/manifest.webmanifest">
<meta name="theme-color" content="#4f46e5">
<link rel="apple-touch-icon" href="/assets/pwa/apple-touch-icon.png">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="تسک‌یار">
<link rel="stylesheet" href="/assets/taskyar/me-shell.css">
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
    main .text-slate-500, main .text-slate-600, nav .text-slate-600, header .text-slate-500{color:var(--admin-muted)}
    main .border-slate-200{border-color:var(--admin-border)}
    main .bg-indigo-600, header .bg-indigo-600{background:var(--admin-accent)}
    main .bg-indigo-600:hover, header .bg-indigo-600:hover{filter:brightness(1.1)}
    main .text-indigo-600, nav .text-indigo-600{color:var(--admin-accent)}
</style>
</head>
<body class="min-h-screen flex flex-col<?= empty($public) ? ' me-has-dock' : '' ?>" style="background:var(--admin-main-bg);color:var(--admin-text)">
<header class="sticky top-0 z-40 backdrop-blur-md border-b" style="background:color-mix(in srgb, var(--admin-panel-bg) 80%, transparent);border-color:var(--admin-border)">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                <i class="fa-solid fa-bolt-lightning text-lg"></i>
            </div>
            <div>
                <h1 class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">تسک‌یار</h1>
                <?php $today = date('Y-m-d'); ?>
                <p class="text-xs text-slate-500 font-medium"><?= \Modules\Personal\Personal::faWeekday($today) ?> <?= \Modules\Personal\Personal::faDate($today) ?> · <?= e($today) ?></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <?php if (empty($public)): ?>
                <span class="hidden sm:inline text-xs font-semibold text-slate-500"><?= e($user['username'] ?? '') ?></span>
                <a href="/me" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-500/25">تسک‌ها</a>
            <?php else: ?>
                <span class="text-xs font-semibold text-emerald-600">مشاهده فقط‌خواندنی از طریق لینک عمومی</span>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <?php if (empty($public)): ?>
    <div class="me-greeting">
        <h2>سلام<span class="me-greeting-accent">، <?= e($user['username'] ?? '') ?></span></h2>
        <?php $__today = date('Y-m-d'); ?>
        <p><?= \Modules\Personal\Personal::faWeekday($__today) ?> <?= \Modules\Personal\Personal::faDate($__today) ?> · <?= e($__today) ?></p>
    </div>
    <?php endif; ?>
    <?php if (empty($public)): ?>
    <nav class="flex items-center gap-1 sm:gap-2 p-1 overflow-x-auto" style="background:color-mix(in srgb, var(--admin-panel-bg) 60%, transparent);border-radius:var(--admin-radius)">
        <a href="/me" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 whitespace-nowrap">داشبورد و تسک‌ها</a>
        <a href="/me/dashboard" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'dashboard' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">داشبورد شخصی</a>
        <a href="/me/notes" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'notes' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">یادداشت‌ها</a>
        <a href="/me/projects" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'projects' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">پروژه‌ها</a>
        <a href="/me/shared" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'shared' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">اشتراک‌ها</a>
        <a href="/me/settings" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'settings' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">تنظیمات</a>
        <a href="/me/calendar" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'calendar' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">تقویم</a>
        <a href="/me/habits" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'habits' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">عادت‌ها</a>
        <a href="/me/goals" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'goals' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">اهداف</a>
        <a href="/me/journal" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'journal' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">ژورنال</a>
        <a href="/me/stats" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'stats' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">آمار</a>
        <a href="/me/board" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'board' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">بورد</a>
        <a href="/me/matrix" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'matrix' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">ماتریس</a>
        <a href="/me/review" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'review' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">مرور هفتگی</a>
        <a href="/me/search" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'search' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">جست‌وجو</a>
    </nav>
    <?php endif; ?>
    <?php if (!empty($flash)): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium rounded-xl px-4 py-3"><?= e($flash) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl px-4 py-3"><?= e($error) ?></div><?php endif; ?>
    <?= $content ?>
</main>
<?php if (empty($public)): ?>
<nav class="me-dock" aria-label="ناوبری تسک‌یار">
    <a href="/me" class="me-dock-item<?= ($tab ?? '') === 'dashboard' ? ' me-dock-active' : '' ?>">
        <i class="fa-solid fa-gauge-high"></i><span>داشبورد</span>
    </a>
    <a href="/me/day" class="me-dock-item">
        <i class="fa-regular fa-calendar-days"></i><span>تقویم</span>
    </a>
    <span class="me-dock-fab-wrap">
        <a href="/me" class="me-dock-fab" title="افزودن سریع تسک"><i class="fa-solid fa-plus"></i></a>
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
    navigator.serviceWorker.register('/me/sw.js', { scope: '/me/' }).catch(function () {});
  });
}
</script>
<?php endif; ?>
</body>
</html>
