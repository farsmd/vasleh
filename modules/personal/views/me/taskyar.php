<?php
// تم یکپارچه: متغیرهای قالب ادمین روی داشبورد تسک‌یار هم می‌نشیند؛
// حالت تاریک/روشن با همان کلید localStorage ادمین خوانده می‌شود
// (کلید قدیمی 'theme' در صورت نبود کلید مشترک، یک‌بار مهاجرت می‌کند).
$__theme = \Modules\Admin\AdminTheme::load(dirname(__DIR__, 4));
$__dark = !empty($settings['dark_default']) || ($__theme['default_theme'] ?? 'dark') === 'dark';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="<?= isset(\Modules\Personal\Personal::STYLES[$settings['style'] ?? '']) ? $settings['style'] : 'darkPrism' ?>"<?= $__dark ? ' class="dark"' : '' ?>>
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
    <title>تسک‌یار | مدیریت زمان و اهداف شخصی</title>
    <script>try{var _v=localStorage.getItem('vasleh_theme');if(!_v){var _o=localStorage.getItem('theme');if(_o){_v=_o;localStorage.setItem('vasleh_theme',_o);}}if(_v==='light'){document.documentElement.classList.remove('dark')}else if(_v==='dark'){document.documentElement.classList.add('dark')}}catch(e){}</script>
    <link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
    <link rel="stylesheet" href="/assets/taskyar/fa/css/all.min.css">
    <link rel="stylesheet" href="/assets/taskyar/tailwind.css">
    <style>
        <?= \Modules\Admin\AdminTheme::varsCss($__theme) ?>
        /* تم یکپارچه: رنگ‌های تسک‌یار با متغیرهای قالب ادمین هماهنگ می‌شود */
        .bg-white{background:var(--admin-panel-bg)}
        .bg-slate-50{background:var(--admin-main-bg)}
        .text-slate-800, .text-slate-900{color:var(--admin-text)}
        .text-slate-500, .text-slate-600{color:var(--admin-muted)}
        .border-slate-200, .border-slate-100{border-color:var(--admin-border)}
        .bg-indigo-600, .bg-indigo-700{background:var(--admin-accent)}
        .hover\:bg-indigo-700:hover{background:var(--admin-accent)}
        .bg-indigo-50{background:color-mix(in srgb, var(--admin-accent) 8%, transparent)}
        .text-indigo-600, .text-indigo-500, .text-indigo-400{color:var(--admin-accent)}
        .ring-indigo-500{--tw-ring-color:var(--admin-accent)}
        header.bg-white\/80{background:color-mix(in srgb, var(--admin-panel-bg) 80%, transparent)}
        nav.bg-slate-200\/60{background:color-mix(in srgb, var(--admin-panel-bg) 60%, transparent)}
        .dark .dark\:bg-slate-950{background:var(--admin-main-bg)}
        .dark .dark\:bg-slate-900, .dark .dark\:bg-slate-800, .dark .dark\:bg-slate-800\/80{background:var(--admin-panel-bg)}
        .dark .dark\:bg-slate-900\/80{background:color-mix(in srgb, var(--admin-panel-bg) 80%, transparent)}
        .dark .dark\:text-white, .dark .dark\:text-slate-100, .dark .dark\:text-slate-200{color:var(--admin-text)}
        .dark .dark\:text-slate-300, .dark .dark\:text-slate-400{color:var(--admin-muted)}
        .dark .dark\:border-slate-800, .dark .dark\:border-slate-700, .dark .dark\:border-slate-700\/60{border-color:var(--admin-border)}
        .dark .dark\:text-indigo-400{color:color-mix(in srgb, var(--admin-accent) 75%, white)}
        body { font-family: 'Vazirmatn', sans-serif; user-select: none; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.03); }
        ::-webkit-scrollbar-thumb { background: rgba(140, 140, 160, 0.25); border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(140, 140, 160, 0.45); }
        .task-completed { text-decoration: line-through; opacity: 0.6; }
        .animate-pop { animation: popIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes popIn { 0% { transform: scale(0.96); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-300 me-has-dock">

    <header class="sticky top-0 z-40 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center text-white shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-bolt-lightning text-lg"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-lg tracking-tight bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent dark:from-indigo-400 dark:to-purple-300">
                        تسک‌یار
                    </h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1 font-medium" id="currentDateDisplay">
                        در حال بارگذاری تاریخ...
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <span class="hidden sm:inline text-xs font-semibold text-slate-500 dark:text-slate-400"><?= e($user['username'] ?? '') ?></span>
                <button onclick="togglePomodoroModal()" class="flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-800/60 hover:bg-rose-100 transition shadow-sm">
                    <i class="fa-solid fa-stopwatch animate-pulse"></i>
                    <span id="headerPomoTimer"><?= strtr((string) (int) $settings['pomo_focus'], '0123456789', '۰۱۲۳۴۵۶۷۸۹') ?>:۰۰</span>
                </button>
                <button onclick="openAddTaskModal()" class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-500/25 active:scale-95 transition">
                    <i class="fa-solid fa-plus"></i>
                    <span class="hidden sm:inline">افزودن تسک</span>
                </button>
                <button onclick="toggleTheme()" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 border border-slate-200 dark:border-slate-800 transition">
                    <i id="themeIcon" class="fa-regular fa-moon"></i>
                </button>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div class="me-greeting">
            <?php $__todayG = date('Y-m-d'); ?>
            <p><?= \Modules\Personal\Personal::faWeekday($__todayG) ?>، <?= \Modules\Personal\Personal::faDate($__todayG) ?></p>
            <h2>تمرکز امروز<span class="me-greeting-accent">، <?= e($user['username'] ?? '') ?></span></h2>
        </div>
        <?php if (!empty($flash)): ?><div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 text-xs font-medium rounded-xl px-4 py-3"><?= e($flash) ?></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-medium rounded-xl px-4 py-3"><?= e($error) ?></div><?php endif; ?>

        <div class="flex items-center justify-between flex-wrap gap-3 pb-2 border-b border-slate-200 dark:border-slate-800">
            <nav class="flex items-center gap-1 sm:gap-2 p-1 bg-slate-200/60 dark:bg-slate-900 rounded-2xl overflow-x-auto">
                <button onclick="changeView('dashboard')" id="tab-dashboard" class="view-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                    داشبورد و آمار
                </button>
                <button onclick="changeView('hourly')" id="tab-hourly" class="view-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-regular fa-clock text-xs"></i>
                    تایم‌لاین ساعتی
                </button>
                <button onclick="changeView('daily')" id="tab-daily" class="view-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-list-check text-xs"></i>
                    روزانه
                </button>
                <button onclick="changeView('weekly')" id="tab-weekly" class="view-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-calendar-week text-xs"></i>
                    هفتگی
                </button>
                <button onclick="changeView('monthly')" id="tab-monthly" class="view-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-regular fa-calendar-days text-xs"></i>
                    ماهانه
                </button>
                <a href="/me/notes" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-regular fa-note-sticky text-xs"></i>
                    یادداشت‌ها
                </a>
                <a href="/me/projects" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-diagram-project text-xs"></i>
                    پروژه‌ها
                </a>
                <a href="/me/shared" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-share-nodes text-xs"></i>
                    اشتراک‌ها
                </a>
                <a href="/me/dashboard" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-gauge-high text-xs"></i>
                    داشبورد شخصی
                </a>
                <a href="/me/settings" class="px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-gear text-xs"></i>
                    تنظیمات
                </a>
            </nav>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" id="taskSearchInput" oninput="renderCurrentView()" placeholder="جستجوی تسک..." class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs rounded-xl px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-indigo-500 w-36 sm:w-52 transition">
                    <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2.5 text-xs text-slate-400"></i>
                </div>
                <select id="categoryFilter" onchange="renderCurrentView()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-600 dark:text-slate-300">
                    <option value="all">همه دسته‌ها</option>
                    <?php foreach ($settings['categories'] as $c): ?>
                    <option value="<?= e($c['key']) ?>"><?= e($c['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- VIEW 1: Dashboard (بنتو، عین ماکاپ) -->
        <div id="view-dashboard" class="space-y-4">
            <section class="me-bento-grid">
                <div class="me-card me-pomo-card">
                    <div class="me-card-top">
                        <span class="me-card-label"><i class="fa-regular fa-clock"></i> پومودورو</span>
                        <span class="me-pomo-tag" id="bentoPomoSessions">۰ جلسه تکمیل‌شده</span>
                    </div>
                    <div class="me-pomo-display">
                        <span id="pomoCardDisplay" class="me-pomo-time"><?= sprintf('%02d:00', (int) $settings['pomo_focus']) ?></span>
                        <svg class="me-pomo-ring" viewBox="0 0 36 36">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--me-border)" stroke-width="3.5"/>
                            <path id="pomoCardRing" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="var(--me-accent)" stroke-width="3.5" stroke-dasharray="0, 100" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div class="me-pomo-controls">
                        <button id="pomoCardBtn" onclick="togglePomoTimer()" class="me-pomo-btn"><i class="fa-solid fa-play"></i><span>شروع</span></button>
                        <button onclick="resetPomoTimer()" class="me-pomo-btn me-pomo-btn-reset" title="بازنشانی"><i class="fa-solid fa-rotate-left"></i></button>
                    </div>
                </div>
                <div class="me-card me-progress-card">
                    <div class="me-card-top">
                        <span class="me-card-label"><i class="fa-solid fa-chart-column"></i> پیشرفت روز</span>
                    </div>
                    <div>
                        <div class="me-progress-main"><span id="progressPercentageText" class="me-progress-percent">۰٪</span></div>
                        <p class="me-progress-caption" id="bentoProgressCaption">۰ از ۰ کار امروز تکمیل شده</p>
                    </div>
                    <div class="me-mini-bars" id="bentoProgressWeekBars"></div>
                </div>
            </section>

            <section class="me-card">
                <div class="me-section-head">
                    <h3 class="me-section-title"><i class="fa-regular fa-calendar-days"></i> برنامه زمانی و تقویم</h3>
                    <span class="me-section-action" id="bentoMonthLabel"></span>
                </div>
                <div class="me-cal-strip" id="bentoCalStrip"></div>
                <div class="me-event-list" id="bentoEventsList"></div>
            </section>

            <section class="me-card">
                <div class="me-section-head">
                    <h3 class="me-section-title"><i class="fa-solid fa-list-check"></i> کارهای اولویت‌دار امروز</h3>
                    <button onclick="openAddTaskModal()" class="me-section-action me-action-btn">+ افزودن کار</button>
                </div>
                <div id="bentoTodoList" class="space-y-2"></div>
            </section>

            <section class="me-dual">
                <div class="me-card">
                    <div class="me-section-head">
                        <h3 class="me-section-title"><i class="fa-solid fa-diagram-project"></i> پروژه‌ها</h3>
                        <a href="/me/projects" class="me-section-action">مشاهده همه</a>
                    </div>
                    <?php if (empty($projects)): ?>
                        <p class="me-empty">هنوز پروژه‌ای نیست.</p>
                    <?php else: ?>
                        <?php foreach (array_slice($projects, 0, 5) as $pr): ?>
                        <?php $pct = $pr['needs_total'] > 0 ? (int) round(100 * $pr['needs_done'] / $pr['needs_total']) : 0; ?>
                        <div class="me-project-item">
                            <div class="me-project-meta"><a href="/me/projects/<?= (int) $pr['id'] ?>"><?= e($pr['title']) ?></a><span><?= $pct ?>٪</span></div>
                            <div class="me-progress-bar-bg"><div class="me-progress-bar-fill" style="width:<?= $pct ?>%"></div></div>
                            <p class="me-project-needs"><?= (int) $pr['needs_done'] ?> از <?= (int) $pr['needs_total'] ?> نیاز تکمیل شده</p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="me-card">
                    <div class="me-section-head">
                        <h3 class="me-section-title"><i class="fa-regular fa-note-sticky"></i> یادداشت سریع</h3>
                        <a href="/me/notes" class="me-section-action">مشاهده همه</a>
                    </div>
                    <form method="post" action="/me/notes" class="me-note-form">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="text" name="title" required placeholder="یادداشت تازه..." class="me-note-input">
                        <button class="me-note-btn">افزودن</button>
                    </form>
                    <?php if (empty($notes)): ?>
                        <p class="me-empty">هنوز یادداشتی نیست.</p>
                    <?php else: ?>
                        <div class="space-y-2">
                        <?php foreach (array_slice($notes, 0, 5) as $n): ?>
                            <p class="me-note-row"><a href="/me/notes/<?= (int) $n['id'] ?>"><?= e($n['title']) ?></a> <span class="me-note-date"><?= e($n['updated_at']) ?></span></p>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- VIEW 2: Hourly Time Blocking -->
        <div id="view-hourly" class="hidden space-y-4 me-view">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                    <div>
                        <h3 class="font-bold text-base text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-timeline text-indigo-600"></i>
                            بلوک‌بندی ساعتی (Time Blocking)
                        </h3>
                        <p class="text-xs text-slate-400 mt-1" id="hourlySubtitle">برنامه‌ریزی دقیق امروز برای کنترل کامل بر زمان</p>
                    </div>
                    <button onclick="openAddTaskModal()" class="px-3.5 py-1.5 rounded-xl text-xs bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-semibold hover:bg-indigo-100 transition">
                        <i class="fa-solid fa-plus text-xs"></i> افزودن به ساعت خاص
                    </button>
                </div>
                <div id="hourlyAllDay" class="space-y-2 mb-4"></div>
                <div id="hourlyTimeline" class="space-y-3 relative max-h-[650px] overflow-y-auto pl-2 pr-1"></div>
            </div>
        </div>

        <!-- VIEW 3: Daily Tasks View -->
        <div id="view-daily" class="hidden space-y-4 me-view">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                    <div>
                        <h3 class="font-bold text-base text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-calendar-day text-indigo-600"></i>
                            چک‌لیست کارها
                        </h3>
                        <p class="text-xs text-slate-400 mt-1" id="dailySubtitle">وظایف روز انتخاب‌شده را تیک بزنید</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="goDay(-1)" class="px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium">روز قبل ▶</button>
                        <button onclick="goDay(1)" class="px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium">روز بعد ◀</button>
                        <button onclick="filterDailyStatus('all')" class="status-btn px-3 py-1 text-xs rounded-lg bg-indigo-600 text-white font-medium" data-filter="all">همه</button>
                        <button onclick="filterDailyStatus('pending')" class="status-btn px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium" data-filter="pending">در انتظار</button>
                        <button onclick="filterDailyStatus('done')" class="status-btn px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium" data-filter="done">تکمیل شده</button>
                    </div>
                </div>
                <div id="dailyTaskList" class="space-y-2.5"></div>
                <div id="dailyUndated" class="space-y-2.5 mt-4"></div>
            </div>
        </div>

        <!-- VIEW 4: Weekly Planner View -->
        <div id="view-weekly" class="hidden space-y-4 me-view">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                    <div>
                        <h3 class="font-bold text-base text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-table-columns text-indigo-600"></i>
                            برنامه هفتگی
                        </h3>
                        <p class="text-xs text-slate-400 mt-1" id="weeklySubtitle">تفکیک وظایف بر اساس روزهای هفته</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="goWeek(-1)" class="px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium">هفته قبل ▶</button>
                        <button onclick="goWeek(1)" class="px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium">هفته بعد ◀</button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3" id="weeklyGrid"></div>
            </div>
        </div>

        <!-- VIEW 5: Monthly View -->
        <div id="view-monthly" class="hidden space-y-4 me-view">
            <div class="bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
                    <div>
                        <h3 class="font-bold text-base text-slate-800 dark:text-white flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-indigo-600"></i>
                            نمای ماه
                        </h3>
                        <p class="text-xs text-slate-400 mt-1" id="monthlySubtitle">بررسی بار کاری و روزهای پرمشغله در ماه</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="goMonth(-1)" class="px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium">ماه قبل ▶</button>
                        <button onclick="goMonth(1)" class="px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium">ماه بعد ◀</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-7 gap-2.5" id="monthlyGrid"></div>
            </div>
        </div>
    </main>

    <button onclick="openAddTaskModal()" title="افزودن سریع تسک" class="fixed bottom-24 left-6 z-40 w-14 h-14 rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-xl shadow-indigo-600/30 flex items-center justify-center text-xl hover:scale-105 active:scale-95 transition-all">
        <i class="fa-solid fa-plus"></i>
    </button>

    <!-- Modal: Add / Edit Task -->
    <div id="taskModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm hidden">
        <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-lg p-6 shadow-2xl border border-slate-100 dark:border-slate-800 animate-pop">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800 mb-5">
                <h3 id="modalTitle" class="font-bold text-base text-slate-800 dark:text-white">افزودن تسک جدید</h3>
                <button onclick="closeAddTaskModal()" class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="taskForm" onsubmit="handleTaskSubmit(event)" class="space-y-4">
                <input type="hidden" id="taskId">
                <input type="hidden" id="taskKind">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">عنوان کار یا هدف *</label>
                    <input type="text" id="taskTitle" required placeholder="مثال: نوشتن پروپوزال پروژه، تمرین بدنسازی..." class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">دسته‌بندی</label>
                        <select id="taskCategory" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                            <?php foreach ($settings['categories'] as $c): ?>
                            <option value="<?= e($c['key']) ?>"><?= e($c['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">سطح اولویت</label>
                        <select id="taskPriority" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                            <option value="high">بالا و فوری</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="low">پایین و روتین</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">روز هدف</label>
                        <select id="taskDay" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                            <option value="0">شنبه</option>
                            <option value="1">یکشنبه</option>
                            <option value="2">دوشنبه</option>
                            <option value="3">سه‌شنبه</option>
                            <option value="4">چهارشنبه</option>
                            <option value="5">پنج‌شنبه</option>
                            <option value="6">جمعه</option>
                        </select>
                        <span class="text-[10px] text-slate-400 mt-0.5 block" id="weekRangeHint">هفته: <?= \Modules\Personal\Personal::faDate($weekSat) ?> تا <?= \Modules\Personal\Personal::faDate($weekSun) ?></span>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">زمان‌بندی ساعت (بلوک زمانی)</label>
                        <select id="taskHour" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                            <option value="all-day">تمام روز (شناور)</option>
                            <option value="07:00">۰۷:۰۰ - ۰۸:۰۰ صبح</option>
                            <option value="08:00">۰۸:۰۰ - ۰۹:۰۰ صبح</option>
                            <option value="09:00">۰۹:۰۰ - ۱۰:۰۰ صبح</option>
                            <option value="10:00">۱۰:۰۰ - ۱۱:۰۰ صبح</option>
                            <option value="11:00">۱۱:۰۰ - ۱۲:۰۰ ظهر</option>
                            <option value="12:00">۱۲:۰۰ - ۱۳:۰۰ ظهر</option>
                            <option value="14:00">۱۴:۰۰ - ۱۵:۰۰ بعد از ظهر</option>
                            <option value="15:00">۱۵:۰۰ - ۱۶:۰۰ بعد از ظهر</option>
                            <option value="16:00">۱۶:۰۰ - ۱۷:۰۰ عصر</option>
                            <option value="17:00">۱۷:۰۰ - ۱۸:۰۰ عصر</option>
                            <option value="18:00">۱۸:۰۰ - ۱۹:۰۰ عصر</option>
                            <option value="19:00">۱۹:۰۰ - ۲۰:۰۰ شب</option>
                            <option value="20:00">۲۰:۰۰ - ۲۱:۰۰ شب</option>
                            <option value="21:00">۲۱:۰۰ - ۲۲:۰۰ شب</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">مدت تخمینی</label>
                        <input type="number" id="taskDuration" min="5" step="5" value="30" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">به دقیقه</span>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">یادداشت تکمیلی (اختیاری)</label>
                        <input type="text" id="taskNote" placeholder="لینک‌ها یا جزییات کوچک..." class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-3">
                    <button type="button" onclick="closeAddTaskModal()" class="px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">انصراف</button>
                    <button type="submit" class="px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/30 transition">ثبت و ذخیره</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Pomodoro Focus Timer -->
    <div id="pomodoroModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden">
        <div class="bg-white dark:bg-slate-900 rounded-3xl w-full max-w-sm p-6 shadow-2xl border border-slate-100 dark:border-slate-800 text-center animate-pop relative">
            <button onclick="togglePomodoroModal()" class="absolute left-4 top-4 w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="flex justify-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800 rounded-xl mb-6 mt-2 max-w-xs mx-auto">
                <button onclick="setPomoMode('work')" id="pomoBtnWork" class="flex-1 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm transition">تمرکز (<?= strtr((string) (int) $settings['pomo_focus'], '0123456789', '۰۱۲۳۴۵۶۷۸۹') ?> د)</button>
                <button onclick="setPomoMode('shortBreak')" id="pomoBtnShort" class="flex-1 py-1 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition">استراحت کوتاه</button>
                <button onclick="setPomoMode('longBreak')" id="pomoBtnLong" class="flex-1 py-1 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition">استراحت بلند</button>
            </div>
            <div class="relative w-48 h-48 mx-auto flex items-center justify-center my-4">
                <div class="w-full h-full rounded-full border-4 border-indigo-100 dark:border-slate-800 flex items-center justify-center">
                    <span id="pomoDisplay" class="text-4xl font-black tracking-wider text-slate-800 dark:text-white font-mono"><?= sprintf('%02d:00', (int) $settings['pomo_focus']) ?></span>
                </div>
            </div>
            <p id="pomoStatusText" class="text-xs text-slate-500 dark:text-slate-400 mb-6 font-medium">آماده برای شروع جلسه تمرکز عمیق</p>
            <div class="flex items-center justify-center gap-3">
                <button onclick="togglePomoTimer()" id="pomoStartBtn" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-600/30 hover:bg-indigo-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-play"></i>
                    <span>شروع تمرکز</span>
                </button>
                <button onclick="resetPomoTimer()" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition">
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        // داده از سرور (دیتابیس) — نه localStorage
        const TASKYAR = <?= json_encode([
            'tasks' => $tasks,
            'csrf' => $csrf,
            'today' => $today,
            'date' => $date,
            'weekSat' => $weekSat,
            'weekSun' => $weekSun,
            'monthFirst' => $monthFirst,
            'monthLast' => $monthLast,
            'view' => $view,
            'weekSatFa' => \Modules\Personal\Personal::faDate($weekSat),
            'weekSunFa' => \Modules\Personal\Personal::faDate($weekSun),
            'categories' => $settings['categories'],
            'pomoFocus' => (int) $settings['pomo_focus'],
            'pomoBreak' => (int) $settings['pomo_break'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const DAYS_OF_WEEK = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'];
        const FA_MONTHS = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];

        let state = {
            currentView: TASKYAR.view,
            dailyStatusFilter: 'all',
            tasks: TASKYAR.tasks,
            pomoSessions: parseInt(localStorage.getItem('taskyar_pomo_sessions') || '0', 10) || 0,
        };

        const faNum = (n) => Number(n).toLocaleString('fa-IR');
        const esc = (s) => String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        function dateAdd(iso, days) {
            const d = new Date(iso + 'T00:00:00Z');
            d.setUTCDate(d.getUTCDate() + days);
            return d.toISOString().slice(0, 10);
        }
        function uiDayOf(iso) {
            return (new Date(iso + 'T00:00:00Z').getUTCDay() + 1) % 7;
        }
        function faDateOf(iso) {
            try {
                return new Intl.DateTimeFormat('fa-IR', { year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date(iso + 'T00:00:00Z'));
            } catch (e) { return iso; }
        }

        window.addEventListener('DOMContentLoaded', () => {
            initDateDisplay();
            initTheme();
            document.getElementById('weekRangeHint').innerText = 'هفته جاری: ' + TASKYAR.weekSatFa + ' تا ' + TASKYAR.weekSunFa;
            changeView(state.currentView);
            updateDashboardMetrics();
        });

        function initDateDisplay() {
            try {
                const now = new Date();
                const persian = new Intl.DateTimeFormat('fa-IR', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).format(now);
                const greg = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0') + '-' + String(now.getDate()).padStart(2, '0');
                document.getElementById('currentDateDisplay').innerHTML = '<i class="fa-regular fa-calendar-check text-indigo-500"></i> ' + persian + ' · ' + greg;
            } catch (e) {
                document.getElementById('currentDateDisplay').innerText = 'امروز';
            }
        }

        function initTheme() {
            // حالت از اسکریپت head (کلید مشترک vasleh_theme + پیش‌فرض ادمین) آمده؛ اینجا فقط آیکون هماهنگ می‌شود.
            const isDark = document.documentElement.classList.contains('dark');
            document.getElementById('themeIcon').className = isDark ? 'fa-regular fa-sun text-amber-400' : 'fa-regular fa-moon';
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('vasleh_theme', isDark ? 'dark' : 'light');
            document.getElementById('themeIcon').className = isDark ? 'fa-regular fa-sun text-amber-400' : 'fa-regular fa-moon';
        }

        function changeView(viewName) {
            state.currentView = viewName;
            document.querySelectorAll('.view-tab').forEach(tab => {
                tab.className = 'view-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-900 transition flex items-center gap-2 whitespace-nowrap';
            });
            const activeTab = document.getElementById('tab-' + viewName);
            if (activeTab) {
                activeTab.className = 'view-tab px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex items-center gap-2 whitespace-nowrap bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 shadow-sm';
            }
            ['dashboard', 'hourly', 'daily', 'weekly', 'monthly'].forEach(v => {
                const el = document.getElementById('view-' + v);
                if (el) el.classList.add('hidden');
            });
            const currentEl = document.getElementById('view-' + viewName);
            if (currentEl) currentEl.classList.remove('hidden');
            const url = new URL(window.location.href);
            url.searchParams.set('view', viewName);
            history.replaceState(null, '', url.toString());
            renderCurrentView();
        }

        const CATEGORY_COLORS = [
            'bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 border-blue-200 dark:border-blue-900',
            'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900',
            'bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 border-purple-200 dark:border-purple-900',
            'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-900',
            'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border-rose-200 dark:border-rose-900',
            'bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400 border-sky-200 dark:border-sky-900',
            'bg-teal-50 text-teal-600 dark:bg-teal-950/60 dark:text-teal-400 border-teal-200 dark:border-teal-900',
            'bg-orange-50 text-orange-600 dark:bg-orange-950/60 dark:text-orange-400 border-orange-200 dark:border-orange-900',
        ];
        function getCategoryMeta(cat) {
            const cats = TASKYAR.categories || [];
            for (let i = 0; i < cats.length; i++) {
                if (cats[i].key === cat) {
                    return { label: cats[i].label, color: CATEGORY_COLORS[i % CATEGORY_COLORS.length] };
                }
            }
            return { label: 'عمومی', color: 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border-slate-200' };
        }

        function getPriorityMeta(prio) {
            switch(prio) {
                case 'high': return { label: 'فوری', dot: 'bg-rose-500' };
                case 'medium': return { label: 'متوسط', dot: 'bg-amber-500' };
                case 'low': return { label: 'عادی', dot: 'bg-emerald-500' };
                default: return { label: 'عادی', dot: 'bg-slate-400' };
            }
        }

        function getFilteredTasks() {
            const search = (document.getElementById('taskSearchInput').value || '').toLowerCase().trim();
            const cat = document.getElementById('categoryFilter').value;
            return state.tasks.filter(t => {
                const matchesSearch = t.title.toLowerCase().includes(search) || (t.note && t.note.toLowerCase().includes(search));
                const matchesCategory = cat === 'all' || t.category === cat;
                return matchesSearch && matchesCategory;
            });
        }

        function renderCurrentView() {
            if (state.currentView === 'dashboard') renderDashboardView();
            if (state.currentView === 'hourly') renderHourlyView();
            if (state.currentView === 'daily') renderDailyView();
            if (state.currentView === 'weekly') renderWeeklyView();
            if (state.currentView === 'monthly') renderMonthlyView();
        }

        function renderDashboardView() {
            // نوار هفته (شنبه تا جمعه) + ماه جلالی — عین ماکاپ، با دادهٔ واقعی.
            const strip = document.getElementById('bentoCalStrip');
            let stripHtml = '';
            for (let i = 0; i < 7; i++) {
                const d = dateAdd(TASKYAR.weekSat, i);
                const isToday = d === TASKYAR.today;
                let dayNum = d.slice(8, 10).replace(/^0/, '');
                try { dayNum = new Intl.DateTimeFormat('fa-IR', { day: 'numeric' }).format(new Date(d + 'T00:00:00Z')); } catch (e) {}
                stripHtml += '<button onclick="window.location.href=\'/me?date=' + d + '&view=daily\'" class="me-cal-day' + (isToday ? ' selected' : '') + '"><span class="me-cal-day-name">' + DAYS_OF_WEEK[i] + '</span><span class="me-cal-day-num">' + dayNum + '</span></button>';
            }
            strip.innerHTML = stripHtml;
            try { document.getElementById('bentoMonthLabel').innerText = new Intl.DateTimeFormat('fa-IR', { month: 'long', year: 'numeric' }).format(new Date(TASKYAR.today + 'T00:00:00Z')); } catch (e) {}
            // رویدادهای امروز (ساعت‌دار)، به ترتیب ساعت.
            const eventsBox = document.getElementById('bentoEventsList');
            const events = getFilteredTasks().filter(t => t.kind === 'event' && t.date === TASKYAR.today).sort((a, b) => (a.hour || '').localeCompare(b.hour || ''));
            eventsBox.innerHTML = events.length === 0
                ? '<p class="me-empty">رویدادی برای امروز نیست.</p>'
                : events.map(t => createTaskCardHTML(t)).join('');
            // کارهای اولویت‌دار امروز (تودوهای انجام‌نشدهٔ امروز، فوری اول).
            const list = document.getElementById('bentoTodoList');
            const todos = getFilteredTasks().filter(t => t.kind === 'todo' && !t.done && t.date === TASKYAR.today);
            todos.sort((a, b) => (a.priority === 'high' ? -1 : (b.priority === 'high' ? 1 : 0)));
            list.innerHTML = todos.length === 0
                ? '<div class="me-empty">کاری برای امروز نیست.</div>'
                : todos.slice(0, 8).map(t => createTaskCardHTML(t)).join('');
        }

        function renderHourlyView() {
            document.getElementById('hourlySubtitle').innerText = 'بلوک‌بندی ساعتی ' + faDateOf(TASKYAR.date) + ' · ' + TASKYAR.date;
            const allDayBox = document.getElementById('hourlyAllDay');
            const dayTasks = getFilteredTasks().filter(t => t.date === TASKYAR.date);
            const allDay = dayTasks.filter(t => t.hour === 'all-day');
            allDayBox.innerHTML = allDay.length > 0
                ? '<h4 class="text-xs font-bold text-slate-500 mb-2">تمام‌روز</h4>' + allDay.map(t => createTaskCardHTML(t)).join('')
                : '';
            const container = document.getElementById('hourlyTimeline');
            const hours = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];
            let html = '';
            hours.forEach(hr => {
                const tasksInSlot = dayTasks.filter(t => t.hour === hr);
                html += '<div class="flex items-start gap-4 p-2.5 rounded-xl hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition border border-transparent hover:border-slate-200/50 dark:hover:border-slate-800">'
                    + '<div class="w-16 pt-1 text-xs font-mono font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">'
                    + '<span class="w-2 h-2 rounded-full bg-indigo-500/80"></span>' + hr + '</div>'
                    + '<div class="flex-1 space-y-2">'
                    + (tasksInSlot.length > 0 ? tasksInSlot.map(t => createTaskCardHTML(t)).join('') : ''
                        + '<div onclick="openAddTaskWithHour(\'' + hr + '\')" class="text-[11px] text-slate-400 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl py-2 px-3 cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-600 transition flex items-center justify-between">'
                        + '<span>ساعت آزاد - برای تنظیم کار کلیک کنید</span><i class="fa-solid fa-plus"></i></div>')
                    + '</div></div>';
            });
            container.innerHTML = html;
        }

        function renderDailyView() {
            document.getElementById('dailySubtitle').innerText = 'وظایف ' + faDateOf(TASKYAR.date) + ' · ' + TASKYAR.date;
            const container = document.getElementById('dailyTaskList');
            let tasks = getFilteredTasks().filter(t => t.date === TASKYAR.date);
            if (state.dailyStatusFilter === 'pending') tasks = tasks.filter(t => !t.done);
            if (state.dailyStatusFilter === 'done') tasks = tasks.filter(t => t.done);
            container.innerHTML = tasks.length === 0
                ? '<div class="text-center py-12 text-slate-400 text-xs">هیچ تسکی با این وضعیت برای این روز ثبت نشده است.</div>'
                : tasks.map(t => createTaskCardHTML(t)).join('');
            const undatedBox = document.getElementById('dailyUndated');
            const undated = getFilteredTasks().filter(t => t.date === '');
            undatedBox.innerHTML = undated.length > 0
                ? '<h4 class="text-xs font-bold text-slate-500 mb-2 pt-2 border-t border-slate-100 dark:border-slate-800">بدون تاریخ</h4>' + undated.map(t => createTaskCardHTML(t)).join('')
                : '';
        }

        function filterDailyStatus(filter) {
            state.dailyStatusFilter = filter;
            document.querySelectorAll('.status-btn').forEach(btn => {
                if (btn.getAttribute('data-filter') === filter) {
                    btn.className = 'status-btn px-3 py-1 text-xs rounded-lg bg-indigo-600 text-white font-medium';
                } else {
                    btn.className = 'status-btn px-3 py-1 text-xs rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-medium';
                }
            });
            renderDailyView();
        }

        function renderWeeklyView() {
            document.getElementById('weeklySubtitle').innerText = 'هفته ' + TASKYAR.weekSatFa + ' تا ' + TASKYAR.weekSunFa + ' · ' + TASKYAR.weekSat + ' تا ' + TASKYAR.weekSun;
            const grid = document.getElementById('weeklyGrid');
            const filtered = getFilteredTasks();
            let html = '';
            for (let i = 0; i < 7; i++) {
                const d = dateAdd(TASKYAR.weekSat, i);
                const dayTasks = filtered.filter(t => t.date === d);
                const isToday = d === TASKYAR.today;
                html += '<div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-3 border ' + (isToday ? 'border-indigo-400 dark:border-indigo-600 shadow-sm' : 'border-slate-200/60 dark:border-slate-800') + ' flex flex-col min-h-[300px]">'
                    + '<div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200/50 dark:border-slate-700/60">'
                    + '<div><span class="font-bold text-xs ' + (isToday ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-700 dark:text-slate-300') + '">' + DAYS_OF_WEEK[i] + '</span>'
                    + (isToday ? '<span class="mr-1 text-[9px] bg-indigo-100 text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300 px-1.5 py-0.5 rounded-md font-bold">امروز</span>' : '')
                    + '<div class="text-[10px] text-slate-400">' + faDateOf(d) + ' · ' + d + '</div></div>'
                    + '<span class="text-[10px] text-slate-400 font-bold">' + faNum(dayTasks.length) + '</span></div>'
                    + '<div class="space-y-2 flex-1 overflow-y-auto max-h-80 pr-0.5">'
                    + dayTasks.map(t => createTaskCardHTML(t, true)).join('')
                    + (dayTasks.length === 0 ? '<p class="text-[10px] text-slate-400 text-center py-4">برنامه‌ای نیست</p>' : '')
                    + '</div>'
                    + '<button onclick="openAddTaskWithDay(' + i + ')" class="mt-2 w-full py-1.5 rounded-lg border border-dashed border-slate-200 dark:border-slate-700 hover:border-indigo-500 text-[10px] text-slate-500 hover:text-indigo-600 transition flex items-center justify-center gap-1">'
                    + '<i class="fa-solid fa-plus text-[9px]"></i> افزودن</button></div>';
            }
            grid.innerHTML = html;
        }

        function renderMonthlyView() {
            const grid = document.getElementById('monthlyGrid');
            const filtered = getFilteredTasks();
            document.getElementById('monthlySubtitle').innerText = 'بار کاری ماه ' + TASKYAR.monthFirst + ' تا ' + TASKYAR.monthLast;
            let html = '';
            let d = TASKYAR.monthFirst;
            while (d <= TASKYAR.monthLast) {
                const count = filtered.filter(t => t.date === d).length;
                let densityColor = 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800';
                if (count >= 3) densityColor = 'bg-indigo-50/60 dark:bg-indigo-950/20 border-indigo-200 dark:border-indigo-900/60';
                const dayNum = parseInt(d.slice(8, 10), 10);
                html += '<div class="rounded-xl border p-2.5 transition hover:shadow-md ' + densityColor + ' min-h-[85px] flex flex-col justify-between cursor-pointer" onclick="window.location.href=\'/me?date=' + d + '&view=daily\'">'
                    + '<div class="flex justify-between items-center text-xs font-bold">'
                    + '<span class="text-slate-800 dark:text-slate-200">' + faNum(dayNum) + '</span>'
                    + '<span class="text-[10px] text-slate-400">' + DAYS_OF_WEEK[uiDayOf(d)] + '</span></div>'
                    + '<div class="space-y-1 my-1">'
                    + (count > 0 ? '<div class="flex items-center gap-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400"><span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span><span>' + faNum(count) + ' وظیفه</span></div>'
                        : '<span class="text-[10px] text-slate-400">آزاد</span>')
                    + '</div>'
                    + '<span class="text-[9px] text-slate-400">' + faDateOf(d) + '</span></div>';
                d = dateAdd(d, 1);
            }
            grid.innerHTML = html;
        }

        function createTaskCardHTML(task, isCompact = false) {
            const catMeta = getCategoryMeta(task.category);
            const prioMeta = getPriorityMeta(task.priority);
            const timeLabel = task.hour === 'all-day' ? 'تمام روز' : task.hour + (task.end ? ' تا ' + task.end : '');
            return '<div class="group bg-white dark:bg-slate-800/80 p-3 rounded-xl border border-slate-200/70 dark:border-slate-700/60 hover:shadow-sm transition flex items-center justify-between gap-3">'
                + '<div class="flex items-center gap-3 min-w-0">'
                + '<button onclick="toggleTaskStatus(\'' + task.kind + '\', ' + task.id + ')" class="w-5 h-5 rounded-lg border ' + (task.done ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 dark:border-slate-600 hover:border-indigo-500') + ' flex items-center justify-center transition flex-shrink-0">'
                + (task.done ? '<i class="fa-solid fa-check text-[10px]"></i>' : '') + '</button>'
                + '<div class="min-w-0"><h4 class="text-xs font-semibold ' + (task.done ? 'task-completed text-slate-400 dark:text-slate-500' : 'text-slate-800 dark:text-slate-200') + ' truncate">' + esc(task.title) + '</h4>'
                + '<div class="flex items-center gap-2 mt-1 flex-wrap text-[10px] text-slate-400">'
                + '<span class="px-2 py-0.5 rounded-md font-medium border ' + catMeta.color + '">' + catMeta.label + '</span>'
                + '<span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full ' + prioMeta.dot + '"></span> ' + prioMeta.label + '</span>'
                + '<span class="font-mono flex items-center gap-1"><i class="fa-regular fa-clock"></i>' + timeLabel + '</span>'
                + (task.duration ? '<span>(' + faNum(task.duration) + ' د)</span>' : '')
                + (task.date ? '<span>' + faDateOf(task.date) + '</span>' : '<span>بدون تاریخ</span>')
                + '</div></div></div>'
                + '<div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">'
                + '<button onclick="openEditTaskModal(\'' + task.kind + '\', ' + task.id + ')" title="ویرایش" class="w-7 h-7 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-xs"><i class="fa-regular fa-pen-to-square"></i></button>'
                + '<button onclick="deleteTask(\'' + task.kind + '\', ' + task.id + ')" title="حذف" class="w-7 h-7 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center justify-center text-xs"><i class="fa-regular fa-trash-can"></i></button>'
                + '</div></div>';
        }

        function updateDashboardMetrics() {
            // کارت پیشرفت روز: درصد امروز + میله‌های هفته + شمار جلسات پومودورو.
            const todayTasks = state.tasks.filter(t => t.date === TASKYAR.today);
            const todayDone = todayTasks.filter(t => t.done).length;
            const percentage = todayTasks.length > 0 ? Math.round((todayDone / todayTasks.length) * 100) : 0;
            document.getElementById('progressPercentageText').innerText = faNum(percentage) + '٪';
            document.getElementById('bentoProgressCaption').innerText = faNum(todayDone) + ' از ' + faNum(todayTasks.length) + ' کار امروز تکمیل شده';
            let bars = '';
            for (let i = 0; i < 7; i++) {
                const d = dateAdd(TASKYAR.weekSat, i);
                const dayTasks = state.tasks.filter(t => t.date === d);
                const dayDone = dayTasks.filter(t => t.done).length;
                const pct = dayTasks.length > 0 ? Math.round((dayDone / dayTasks.length) * 100) : 0;
                bars += '<div class="me-bar-col"><div class="me-bar-fill' + (d === TASKYAR.today ? ' active' : '') + '" style="height:' + Math.max(pct, 4) + '%"></div><span class="me-bar-day">' + DAYS_OF_WEEK[i].slice(0, 1) + '</span></div>';
            }
            document.getElementById('bentoProgressWeekBars').innerHTML = bars;
            document.getElementById('bentoPomoSessions').innerText = faNum(state.pomoSessions) + ' جلسه تکمیل‌شده';
        }

        function postTask(url, data) {
            const fd = new FormData();
            fd.append('_csrf', TASKYAR.csrf);
            Object.keys(data || {}).forEach(k => fd.append(k, data[k]));
            return fetch(url, { method: 'POST', body: fd }).then(r => r.json());
        }

        function toggleTaskStatus(kind, id) {
            postTask('/me/task/' + kind + '/' + id + '/toggle', {}).then(res => {
                if (!res.ok) return;
                const task = state.tasks.find(t => t.kind === kind && t.id === id);
                if (task) task.done = res.done ? 1 : 0;
                updateDashboardMetrics();
                renderCurrentView();
            });
        }

        function deleteTask(kind, id) {
            if (!confirm('این تسک حذف شود؟')) return;
            postTask('/me/task/' + kind + '/' + id + '/delete', {}).then(res => {
                if (!res.ok) return;
                state.tasks = state.tasks.filter(t => !(t.kind === kind && t.id === id));
                updateDashboardMetrics();
                renderCurrentView();
            });
        }

        function handleTaskSubmit(e) {
            e.preventDefault();
            const title = document.getElementById('taskTitle').value.trim();
            if (!title) return;
            const dayIdx = parseInt(document.getElementById('taskDay').value, 10);
            const date = dateAdd(TASKYAR.weekSat, dayIdx);
            const data = {
                title: title,
                category: document.getElementById('taskCategory').value,
                priority: document.getElementById('taskPriority').value,
                date: date,
                hour: document.getElementById('taskHour').value,
                duration: document.getElementById('taskDuration').value,
                note: document.getElementById('taskNote').value.trim(),
            };
            const id = document.getElementById('taskId').value;
            const kind = document.getElementById('taskKind').value;
            if (id) { data.id = id; data.kind = kind; }
            postTask('/me/task', data).then(res => {
                if (!res.ok) { alert(res.error || 'ذخیره ممکن نشد.'); return; }
                const task = res.task;
                const idx = state.tasks.findIndex(t => t.kind === task.kind && t.id === task.id);
                if (idx !== -1) {
                    // ویرایشی که نوع را عوض کرده باشد (تمام‌روز ↔ ساعت‌دار)
                    const oldIdx = state.tasks.findIndex(t => t.id === task.id && t.kind !== task.kind);
                    if (oldIdx !== -1) state.tasks.splice(oldIdx, 1);
                    state.tasks[idx] = task;
                } else {
                    const oldIdx = state.tasks.findIndex(t => t.id === task.id && t.kind !== task.kind);
                    if (oldIdx !== -1) state.tasks.splice(oldIdx, 1);
                    state.tasks.unshift(task);
                }
                closeAddTaskModal();
                updateDashboardMetrics();
                renderCurrentView();
            });
        }

        function openAddTaskModal() {
            document.getElementById('taskForm').reset();
            document.getElementById('taskId').value = '';
            document.getElementById('taskKind').value = '';
            document.getElementById('modalTitle').innerText = 'افزودن تسک جدید';
            document.getElementById('taskDay').value = String(uiDayOf(TASKYAR.date));
            document.getElementById('taskModal').classList.remove('hidden');
        }

        function openAddTaskWithHour(hour) {
            openAddTaskModal();
            document.getElementById('taskHour').value = hour;
        }

        function openAddTaskWithDay(dayIndex) {
            openAddTaskModal();
            document.getElementById('taskDay').value = dayIndex;
        }

        function openEditTaskModal(kind, id) {
            const task = state.tasks.find(t => t.kind === kind && t.id === id);
            if (!task) return;
            document.getElementById('taskId').value = task.id;
            document.getElementById('taskKind').value = task.kind;
            document.getElementById('taskTitle').value = task.title;
            document.getElementById('taskCategory').value = task.category;
            document.getElementById('taskPriority').value = task.priority;
            document.getElementById('taskDay').value = task.day !== null ? task.day : uiDayOf(TASKYAR.date);
            document.getElementById('taskHour').value = task.hour;
            document.getElementById('taskDuration').value = task.duration || 30;
            document.getElementById('taskNote').value = task.note || '';
            document.getElementById('modalTitle').innerText = 'ویرایش وظیفه';
            document.getElementById('taskModal').classList.remove('hidden');
        }

        function closeAddTaskModal() {
            document.getElementById('taskModal').classList.add('hidden');
        }

        function goDay(delta) {
            window.location.href = '/me?date=' + dateAdd(TASKYAR.date, delta) + '&view=daily';
        }

        function goWeek(delta) {
            window.location.href = '/me?date=' + dateAdd(TASKYAR.weekSat, delta * 7) + '&view=weekly';
        }

        function goMonth(delta) {
            const d = new Date(TASKYAR.monthFirst + 'T00:00:00Z');
            d.setUTCMonth(d.getUTCMonth() + delta);
            window.location.href = '/me?date=' + d.toISOString().slice(0, 10) + '&view=monthly';
        }

        // Pomodoro Timer Logic
        let pomoTimeLeft = (TASKYAR.pomoFocus || 25) * 60;
        let pomoInterval = null;
        let pomoIsRunning = false;
        let pomoMode = 'work';

        function togglePomodoroModal() {
            document.getElementById('pomodoroModal').classList.toggle('hidden');
        }

        function setPomoMode(mode) {
            pomoMode = mode;
            clearInterval(pomoInterval);
            pomoIsRunning = false;
            updatePomoPlayButtonUI();
            ['work', 'shortBreak', 'longBreak'].forEach(m => {
                const b = document.getElementById(m === 'work' ? 'pomoBtnWork' : (m === 'shortBreak' ? 'pomoBtnShort' : 'pomoBtnLong'));
                b.className = 'flex-1 py-1 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition';
            });
            if (mode === 'work') {
                pomoTimeLeft = (TASKYAR.pomoFocus || 25) * 60;
                document.getElementById('pomoBtnWork').className = 'flex-1 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm transition';
                document.getElementById('pomoStatusText').innerText = 'زمان تمرکز عمیق و کار پربازده';
            } else if (mode === 'shortBreak') {
                pomoTimeLeft = (TASKYAR.pomoBreak || 5) * 60;
                document.getElementById('pomoBtnShort').className = 'flex-1 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-sm transition';
                document.getElementById('pomoStatusText').innerText = 'استراحت کوتاه: چند نفس عمیق بکشید و آب بنوشید';
            } else {
                pomoTimeLeft = 15 * 60;
                document.getElementById('pomoBtnLong').className = 'flex-1 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-700 text-blue-600 dark:text-blue-400 shadow-sm transition';
                document.getElementById('pomoStatusText').innerText = 'استراحت طولانی و ریکاوری ذهن';
            }
            renderPomoDisplay();
        }

        function togglePomoTimer() {
            if (pomoIsRunning) {
                clearInterval(pomoInterval);
                pomoIsRunning = false;
            } else {
                pomoIsRunning = true;
                pomoInterval = setInterval(() => {
                    if (pomoTimeLeft > 0) {
                        pomoTimeLeft--;
                        renderPomoDisplay();
                    } else {
                        clearInterval(pomoInterval);
                        pomoIsRunning = false;
                        if (pomoMode === 'work') {
                            state.pomoSessions++;
                            localStorage.setItem('taskyar_pomo_sessions', String(state.pomoSessions));
                            updateDashboardMetrics();
                        }
                        playToneNotification();
                        setPomoMode(pomoMode === 'work' ? 'shortBreak' : 'work');
                    }
                }, 1000);
            }
            updatePomoPlayButtonUI();
        }

        function resetPomoTimer() {
            clearInterval(pomoInterval);
            pomoIsRunning = false;
            setPomoMode(pomoMode);
        }

        function updatePomoPlayButtonUI() {
            const btn = document.getElementById('pomoStartBtn');
            if (pomoIsRunning) {
                btn.innerHTML = '<i class="fa-solid fa-pause"></i> <span>توقف</span>';
                btn.className = 'px-6 py-2.5 rounded-xl bg-amber-600 text-white font-bold text-xs shadow-md shadow-amber-600/30 hover:bg-amber-700 transition flex items-center gap-2';
            } else {
                btn.innerHTML = '<i class="fa-solid fa-play"></i> <span>شروع</span>';
                btn.className = 'px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-xs shadow-md shadow-indigo-600/30 hover:bg-indigo-700 transition flex items-center gap-2';
            }
            // دکمهٔ کارت پومودورو روی داشبورد هم با همان وضعیت همگام می‌شود.
            const cardBtn = document.getElementById('pomoCardBtn');
            if (cardBtn) {
                cardBtn.innerHTML = pomoIsRunning ? '<i class="fa-solid fa-pause"></i><span>توقف</span>' : '<i class="fa-solid fa-play"></i><span>شروع</span>';
            }
        }

        function renderPomoDisplay() {
            const minutes = Math.floor(pomoTimeLeft / 60);
            const seconds = pomoTimeLeft % 60;
            const formatted = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            document.getElementById('pomoDisplay').innerText = formatted;
            document.getElementById('headerPomoTimer').innerText = formatted;
            // نمایش کارت پومودورو روی داشبورد + حلقهٔ پیشرفت نوبت.
            const cardDisplay = document.getElementById('pomoCardDisplay');
            if (cardDisplay) cardDisplay.innerText = formatted;
            const ring = document.getElementById('pomoCardRing');
            if (ring) {
                const total = pomoMode === 'work' ? (TASKYAR.pomoFocus || 25) * 60 : (pomoMode === 'shortBreak' ? (TASKYAR.pomoBreak || 5) * 60 : 15 * 60);
                const elapsed = total > 0 ? Math.round(100 * (total - pomoTimeLeft) / total) : 0;
                ring.setAttribute('stroke-dasharray', elapsed + ', 100');
            }
        }

        function playToneNotification() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, audioCtx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(880, audioCtx.currentTime + 0.3);
                gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.5);
            } catch (err) {
                console.log('Audio notification fallback:', err);
            }
        }

        window.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAddTaskModal();
                const pomo = document.getElementById('pomodoroModal');
                if (!pomo.classList.contains('hidden')) togglePomodoroModal();
            }
        });
    </script>
    <nav class="me-dock" aria-label="ناوبری تسک‌یار">
        <a href="/me" class="me-dock-item me-dock-active">
            <i class="fa-solid fa-gauge-high"></i><span>داشبورد</span>
        </a>
        <a href="/me/day" class="me-dock-item">
            <i class="fa-regular fa-calendar-days"></i><span>تقویم</span>
        </a>
        <span class="me-dock-fab-wrap">
            <button type="button" onclick="openAddTaskModal()" class="me-dock-fab" title="افزودن سریع تسک"><i class="fa-solid fa-plus"></i></button>
        </span>
        <a href="/me/notes" class="me-dock-item">
            <i class="fa-regular fa-note-sticky"></i><span>یادداشت</span>
        </a>
        <a href="/me/settings" class="me-dock-item">
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
</body>
</html>
