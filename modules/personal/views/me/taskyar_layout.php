<?php
// تم یکپارچه: متغیرهای قالب ادمین روی بخش شخصی هم می‌نشیند؛
// حالت تاریک/روشن با همان کلید localStorage ادمین خوانده می‌شود.
$__theme = \Modules\Admin\AdminTheme::load(dirname(__DIR__, 4));
$__dark = ($__theme['default_theme'] ?? 'dark') === 'dark';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl"<?= $__dark ? ' class="dark"' : '' ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<body class="min-h-screen flex flex-col" style="background:var(--admin-main-bg);color:var(--admin-text)">
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
    <nav class="flex items-center gap-1 sm:gap-2 p-1 overflow-x-auto" style="background:color-mix(in srgb, var(--admin-panel-bg) 60%, transparent);border-radius:var(--admin-radius)">
        <a href="/me" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 hover:text-slate-900 whitespace-nowrap">داشبورد و تسک‌ها</a>
        <a href="/me/notes" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'notes' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">یادداشت‌ها</a>
        <a href="/me/projects" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'projects' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">پروژه‌ها</a>
        <a href="/me/shared" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold whitespace-nowrap <?= $tab === 'shared' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">اشتراک‌ها</a>
    </nav>
    <?php endif; ?>
    <?php if (!empty($flash)): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium rounded-xl px-4 py-3"><?= e($flash) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl px-4 py-3"><?= e($error) ?></div><?php endif; ?>
    <?= $content ?>
</main>
</body>
</html>
