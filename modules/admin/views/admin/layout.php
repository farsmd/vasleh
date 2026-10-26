<?php
// لی‌اوت مدیریت وصله — پوسته شیشه‌ای تیره/روشن با Tailwind محلی (بدون CDN).
// قرارداد داده مثل قبل: $title, $content, $menu, $user, $csrf
$assetBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
$currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
if ($assetBase !== '' && str_starts_with($currentPath, $assetBase)) {
    $currentPath = substr($currentPath, strlen($assetBase)) ?: '/';
}
$appRoot = dirname(__DIR__, 4);
$appVersion = '';
$versionFile = $appRoot . '/version.json';
if (is_file($versionFile)) {
    $parts = json_decode((string) file_get_contents($versionFile), true);
    if (is_array($parts)) {
        $appVersion = (int) ($parts['core'] ?? 0) . '.' . (int) ($parts['system'] ?? 0) . '.' . (int) ($parts['other'] ?? 0);
    }
}
$isActive = static function (string $path) use ($currentPath): bool {
    if ($path === '/admin') {
        return $currentPath === '/admin';
    }
    if (str_starts_with($path, '/admin')) {
        return $currentPath === $path || str_starts_with($currentPath, rtrim($path, '/') . '/');
    }
    return $currentPath === $path;
};
// منو گروهی است: هر ماژول یک گروه بازشو با آیکون و عنوان خودش.
// گروهی که صفحه فعلی داخل آن است، باز شروع می‌شود (سرور تصمیم می‌گیرد).
$fallbackIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l9 9-9 9-9-9z"/></svg>';
$groups = [];
$spotlight = [];
foreach (($menu ?? []) as $item) {
    $spotlight[] = ['label' => (string) ($item['label'] ?? ''), 'path' => (string) ($item['path'] ?? '')];
    $slug = (string) ($item['module'] ?? 'misc');
    if (!isset($groups[$slug])) {
        $groups[$slug] = [
            'slug'  => $slug,
            'title' => (string) ($item['module_title'] ?? $slug),
            'icon'  => (string) ($item['module_icon'] ?? ''),
            'items' => [],
            'open'  => false,
        ];
    }
    if ($isActive((string) $item['path'])) {
        $groups[$slug]['open'] = true;
    }
    $groups[$slug]['items'][] = $item;
}
// تنظیمات قالب از پنل (storage/admin-theme.json)؛ خرابی فایل هرگز لی‌اوت را نمی‌خواباند.
$adminTheme = \Modules\Admin\AdminTheme::load($appRoot);
$defaultDark = ($adminTheme['default_theme'] ?? 'dark') === 'dark';
// اعداد فارسی برای ویجت‌های وضعیت هدر (strtr با آرایه — فرم رشته‌ای چندبایتی را خراب می‌کند).
$faDigits = static function (string $s): string {
    return strtr($s, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', '.' => '٫']);
};
$dbBytes = 0;
$dbFile = $appRoot . '/storage/database.sqlite';
if (is_file($dbFile)) {
    $dbBytes = (int) filesize($dbFile);
}
$dbSize = $faDigits(number_format($dbBytes / 1048576, 1, '.', ''));
$phpMem = $faDigits(number_format(memory_get_usage(true) / 1048576, 1, '.', ''));
// ویجت تاریخ هرگز نباید پوسته را بخواباند: ساخت IntlDateFormatter روی بعضی
// بیلدهای ICU خطا می‌دهد (محلی intl ندارد و این مسیر فقط روی هاست اجرا می‌شود).
$todayDate = $faDigits(date('Y-m-d'));
if (class_exists('IntlDateFormatter')) {
    try {
        $fmt = new IntlDateFormatter('fa-IR@calendar=persian', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Asia/Tehran', IntlDateFormatter::TRADITIONALLY);
        $formatted = $fmt->format(time());
        if (is_string($formatted) && $formatted !== '') {
            $todayDate = $formatted;
        }
    } catch (Throwable $e) {
        // همان تاریخ میلادی می‌ماند.
    }
}
?>
<!doctype html>
<html lang="fa" dir="rtl"<?= $defaultDark ? ' class="dark"' : '' ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — مدیریت وصله</title>
<script>try{var _t=localStorage.getItem('vasleh_theme');if(_t==='light'){document.documentElement.classList.remove('dark')}else if(_t==='dark'){document.documentElement.classList.add('dark')}}catch(e){}</script>
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/admin/admin.css?v=<?= e($appVersion) ?>">
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/taskyar/fonts/vazirmatn.css">
<style>
:root{--admin-accent:<?= e($adminTheme['accent']) ?>;--admin-radius:<?= (int) $adminTheme['radius'] ?>px}
.dark{--admin-panel-bg:<?= e($adminTheme['panel_dark']) ?>;--admin-main-bg:<?= e($adminTheme['main_dark']) ?>}
html:not(.dark){--admin-panel-bg:<?= e($adminTheme['panel_light']) ?>;--admin-main-bg:<?= e($adminTheme['main_light']) ?>}
html{font-size:<?= (int) $adminTheme['font_size'] ?>px}
body.admin-shell{background-color:var(--admin-main-bg)}
</style>
</head>
<body class="admin-shell text-slate-900 dark:text-slate-100 transition-colors duration-200 antialiased overflow-x-hidden">

<div class="flex h-screen w-screen overflow-hidden">

  <div id="mobileBackdrop" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden transition-opacity duration-300 opacity-0 lg:hidden"></div>

  <aside id="sidebar" class="fixed lg:static inset-y-0 right-0 z-50 flex flex-col w-72 lg:w-64 glass-panel border-l border-slate-800/80 transition-all duration-300 transform translate-x-full lg:translate-x-0 h-full shrink-0 select-none">

    <div class="h-20 flex items-center justify-between px-5 border-b border-slate-800/60">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 via-indigo-600 to-cyan-400 p-0.5 shadow-md shadow-blue-500/20 flex items-center justify-center">
          <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M12 2v4m0 12v4M2 12h4m12 0h4"/>
              <rect x="6" y="6" width="12" height="12" rx="3" stroke-width="2"/>
              <circle cx="12" cy="12" r="2" fill="currentColor"/>
            </svg>
          </div>
        </div>
        <div class="flex flex-col">
          <div class="flex items-center gap-1.5">
            <span class="font-black text-lg text-white dark:text-white tracking-tight">وصله</span>
            <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20">ادمین</span>
          </div>
          <?php if ($appVersion !== ''): ?><span class="text-[11px] text-slate-400 -mt-0.5">نسخه <?= e($appVersion) ?></span><?php endif; ?>
        </div>
      </div>
      <button id="closeSidebarBtn" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/60">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <div class="flex-1 overflow-y-auto px-3 space-y-6 py-2">
      <a href="<?= e($assetBase) ?>/admin" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm <?= $currentPath === '/admin' ? 'font-semibold text-white shadow-md' : 'font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all' ?>"<?= $currentPath === '/admin' ? ' style="background:var(--admin-accent)"' : '' ?>>
        <span class="g-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></span>
        <span>داشبورد</span>
      </a>
      <?php foreach ($groups as $g): ?>
      <div class="menu-group<?= $g['open'] ? ' open' : '' ?>" data-group="<?= e($g['slug']) ?>">
        <button type="button" class="group-head w-full flex items-center justify-between px-3 mb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 hover:text-slate-300 transition-colors" aria-expanded="<?= $g['open'] ? 'true' : 'false' ?>">
          <span class="flex items-center gap-2">
            <span class="g-icon"><?= $g['icon'] !== '' ? $g['icon'] : $fallbackIcon ?></span>
            <span class="g-title"><?= e($g['title']) ?></span>
          </span>
          <svg class="g-chev shrink-0" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="group-items space-y-1<?= $g['open'] ? '' : ' hidden' ?>">
          <?php foreach ($g['items'] as $item): ?>
            <?php if ((string) $item['path'] === '/admin') continue; ?>
            <?php $active = $isActive((string) $item['path']); ?>
            <a href="<?= e($item['path']) ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm <?= $active ? 'font-semibold text-white shadow-md' : 'font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all' ?>"<?= $active ? ' style="background:var(--admin-accent)"' : '' ?>>
              <span><?= e($item['label']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <a class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800/50 transition-all" title="مشاهده سایت" href="<?= e($assetBase === '' ? '/' : $assetBase . '/') ?>">
        <span class="g-icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg></span>
        <span>مشاهده سایت</span>
      </a>
    </div>

    <?php if (!empty($user)): ?>
    <div class="p-3 border-t border-slate-800/60 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div class="flex flex-col">
          <span class="text-xs font-bold text-white leading-tight"><?= e($user['username']) ?></span>
          <span class="text-[10px] text-emerald-400 font-mono">نقش: <?= e($user['role'] === 'owner' ? 'مالک' : 'مدیر') ?></span>
        </div>
      </div>
      <form method="post" action="<?= e($assetBase) ?>/admin/logout">
        <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
        <button type="submit" title="خروج از حساب" class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800/50 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </button>
      </form>
    </div>
    <?php endif; ?>

  </aside>

  <div class="flex-1 flex flex-col h-full overflow-hidden relative">

    <header class="h-20 glass-header px-4 sm:px-6 flex items-center justify-between gap-3 shrink-0 z-20">
      <div class="flex items-center gap-3 min-w-0">
        <button id="openSidebarBtn" class="lg:hidden p-2 rounded-xl text-slate-300 hover:bg-slate-800 transition-colors shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <h1 class="text-base font-bold text-slate-900 dark:text-white truncate"><?= e($title) ?></h1>
        <button id="spotlightTrigger" class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700/60 text-slate-500 dark:text-slate-400 hover:border-blue-500/50 hover:text-slate-900 dark:hover:text-slate-200 text-xs transition-all w-52 md:w-64 justify-between shrink-0">
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <span>جستجوی سریع منو...</span>
          </span>
          <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-mono text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">⌘K</kbd>
        </button>
      </div>

      <div class="hidden xl:flex items-center gap-3 text-xs shrink-0">
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>پایگاه داده: <strong class="font-mono text-slate-900 dark:text-white">SQLite</strong></span>
          <span class="text-slate-400">•</span>
          <span class="text-slate-500 dark:text-slate-400 font-mono"><?= e($dbSize) ?> مگابایت</span>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300">
          <svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          <span>مصرف رم PHP: <strong class="font-mono text-emerald-500 dark:text-emerald-400"><?= e($phpMem) ?> مگابایت</strong></span>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300">
          <svg class="w-3.5 h-3.5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
          <span><?= e($todayDate) ?></span>
        </div>
      </div>

      <div class="flex items-center gap-2.5 shrink-0">
        <button id="themeToggleBtn" title="تغییر تم (روشن/تاریک)" class="p-2.5 rounded-xl bg-white dark:bg-slate-900/80 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-700 transition-all">
          <svg id="themeIconSun" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          <svg id="themeIconMoon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>
      </div>
    </header>

    <main class="admin-content flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8"><?= $content ?></main>
  </div>

</div>

<div id="spotlightModal" class="fixed inset-0 z-50 flex items-start justify-center pt-20 p-4 bg-black/70 backdrop-blur-md hidden opacity-0 transition-opacity duration-200">
  <div class="glass-panel rounded-2xl max-w-xl w-full p-4 border border-slate-700 shadow-2xl text-right transform scale-95 transition-transform duration-200" id="spotlightCard">
    <div class="flex items-center gap-3 pb-3 border-b border-slate-800">
      <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" id="spotlightInput" placeholder="جستجوی سریع منوی مدیریت..." class="w-full bg-transparent text-sm text-slate-900 dark:text-white focus:outline-none placeholder-slate-500">
      <kbd class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-[10px] font-mono text-slate-500 dark:text-slate-400">ESC</kbd>
    </div>
    <div class="py-3 space-y-2 text-xs max-h-80 overflow-y-auto" id="spotlightResults"></div>
  </div>
</div>

<script>var VASLEH_SPOTLIGHT = <?= json_encode($spotlight, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;</script>
<script src="<?= e($assetBase) ?>/assets/admin/admin.js?v=<?= e($appVersion) ?>"></script>
</body>
</html>
