<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسک‌یار | مدیریت زمان و اهداف شخصی</title>
    <link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
    <link rel="stylesheet" href="/assets/taskyar/fa/css/all.min.css">
    <link rel="stylesheet" href="/assets/taskyar/tailwind.css">
    <style>
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
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-300">

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
                    <span id="headerPomoTimer">25:00</span>
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
            </nav>
            <div class="flex items-center gap-2">
                <div class="relative">
                    <input type="text" id="taskSearchInput" oninput="renderCurrentView()" placeholder="جستجوی تسک..." class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs rounded-xl px-3 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-indigo-500 w-36 sm:w-52 transition">
                    <i class="fa-solid fa-magnifying-glass absolute right-2.5 top-2.5 text-xs text-slate-400"></i>
                </div>
                <select id="categoryFilter" onchange="renderCurrentView()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-600 dark:text-slate-300">
                    <option value="all">همه دسته‌ها</option>
                    <option value="work">💼 کاری</option>
                    <option value="personal">🏠 شخصی</option>
                    <option value="study">📚 یادگیری</option>
                    <option value="health">🏃 سلامت</option>
                </select>
            </div>
        </div>

        <!-- VIEW 1: Dashboard -->
        <div id="view-dashboard" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">کل وظایف</span>
                            <h3 id="statTotalTasks" class="text-3xl font-extrabold mt-1 text-slate-800 dark:text-white">۰</h3>
                        </div>
                        <span class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-layer-group"></i>
                        </span>
                    </div>
                    <div class="mt-4 flex items-center gap-2 text-xs text-slate-500">
                        <span id="statCompletionRate" class="font-bold text-emerald-500">۰٪</span> تکمیل شده
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">انجام شده امروز</span>
                            <h3 id="statCompletedToday" class="text-3xl font-extrabold mt-1 text-emerald-600 dark:text-emerald-400">۰</h3>
                        </div>
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>
                    <div class="mt-4 text-xs text-slate-500 flex items-center gap-1">
                        <span id="statRemainingToday" class="font-bold text-amber-500">۰</span> تسک باقی‌مانده امروز
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">فوری و مهم</span>
                            <h3 id="statUrgentTasks" class="text-3xl font-extrabold mt-1 text-rose-600 dark:text-rose-400">۰</h3>
                        </div>
                        <span class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </span>
                    </div>
                    <div class="mt-4 text-xs text-slate-500">نیازمند اقدام سریع</div>
                </div>
                <div class="bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">جلسات تمرکز (پومودورو)</span>
                            <h3 id="statPomodoroSessions" class="text-3xl font-extrabold mt-1 text-violet-600 dark:text-violet-400">۰</h3>
                        </div>
                        <span class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400 flex items-center justify-center text-lg">
                            <i class="fa-solid fa-fire"></i>
                        </span>
                    </div>
                    <div class="mt-4 text-xs text-slate-500">
                        معادل <span id="statPomoMinutes" class="font-bold text-violet-500">۰</span> دقیقه فوکوس عمیق
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                    <div>
                        <h4 class="font-bold text-sm text-slate-700 dark:text-slate-200 flex items-center gap-2 mb-4">
                            <i class="fa-solid fa-bullseye text-indigo-500"></i>
                            پیشرفت کارهای روز
                        </h4>
                        <div class="flex flex-col items-center justify-center py-4">
                            <div class="relative w-36 h-36 flex items-center justify-center">
                                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                                    <path class="text-slate-100 dark:text-slate-800" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                    <path id="progressCircle" class="text-indigo-600 dark:text-indigo-500 transition-all duration-700 stroke-current" stroke-dasharray="0, 100" stroke-linecap="round" stroke-width="3.5" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                </svg>
                                <div class="absolute flex flex-col items-center">
                                    <span id="progressPercentageText" class="text-2xl font-black text-slate-800 dark:text-white">۰٪</span>
                                    <span class="text-[10px] text-slate-400 font-medium">راندمان کاری</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-indigo-50 dark:bg-slate-800/60 p-3.5 rounded-xl text-xs text-indigo-900 dark:text-indigo-200 space-y-1">
                        <div class="flex justify-between font-medium">
                            <span>توصیه بهره‌وری:</span>
                            <span class="text-indigo-600 dark:text-indigo-400 font-bold">قانون ۲۰/۸۰</span>
                        </div>
                        <p class="text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">
                            روی ۲۰٪ از کارهای با اولویت بالا تمرکز کنید تا ۸۰٪ نتایج چشمگیر روز را بسازید.
                        </p>
                    </div>
                </div>
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-bold text-sm text-slate-700 dark:text-slate-200 flex items-center gap-2">
                            <i class="fa-solid fa-bell text-amber-500"></i>
                            تسک‌های مهم (در اولویت)
                        </h4>
                        <button onclick="changeView('daily')" class="text-xs text-indigo-600 hover:underline">مشاهده همه لیست</button>
                    </div>
                    <div id="dashboardUrgentList" class="space-y-2.5 flex-1 overflow-y-auto max-h-72 pr-1"></div>
                </div>
            </div>
        </div>

        <!-- VIEW 2: Hourly Time Blocking -->
        <div id="view-hourly" class="hidden space-y-4">
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
        <div id="view-daily" class="hidden space-y-4">
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
        <div id="view-weekly" class="hidden space-y-4">
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
        <div id="view-monthly" class="hidden space-y-4">
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

    <button onclick="openAddTaskModal()" title="افزودن سریع تسک" class="fixed bottom-6 left-6 z-40 w-14 h-14 rounded-full bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-xl shadow-indigo-600/30 flex items-center justify-center text-xl hover:scale-105 active:scale-95 transition-all">
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
                            <option value="work">💼 کاری</option>
                            <option value="personal">🏠 شخصی</option>
                            <option value="study">📚 یادگیری و مطالعه</option>
                            <option value="health">🏃 سلامت و ورزش</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">سطح اولویت</label>
                        <select id="taskPriority" class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-white">
                            <option value="high">🔴 بالا و فوری</option>
                            <option value="medium" selected>🟡 متوسط</option>
                            <option value="low">🟢 پایین و روتین</option>
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
                <button onclick="setPomoMode('work')" id="pomoBtnWork" class="flex-1 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm transition">تمرکز (۲۵ د)</button>
                <button onclick="setPomoMode('shortBreak')" id="pomoBtnShort" class="flex-1 py-1 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition">استراحت کوتاه</button>
                <button onclick="setPomoMode('longBreak')" id="pomoBtnLong" class="flex-1 py-1 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition">استراحت بلند</button>
            </div>
            <div class="relative w-48 h-48 mx-auto flex items-center justify-center my-4">
                <div class="w-full h-full rounded-full border-4 border-indigo-100 dark:border-slate-800 flex items-center justify-center">
                    <span id="pomoDisplay" class="text-4xl font-black tracking-wider text-slate-800 dark:text-white font-mono">25:00</span>
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
            if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.getElementById('themeIcon').className = 'fa-regular fa-sun text-amber-400';
            } else {
                document.documentElement.classList.remove('dark');
                document.getElementById('themeIcon').className = 'fa-regular fa-moon';
            }
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
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

        function getCategoryMeta(cat) {
            switch(cat) {
                case 'work': return { label: 'کاری', color: 'bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 border-blue-200 dark:border-blue-900' };
                case 'personal': return { label: 'شخصی', color: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900' };
                case 'study': return { label: 'یادگیری', color: 'bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400 border-purple-200 dark:border-purple-900' };
                case 'health': return { label: 'سلامت', color: 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 border-amber-200 dark:border-amber-900' };
                default: return { label: 'عمومی', color: 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-300 border-slate-200' };
            }
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
            const list = document.getElementById('dashboardUrgentList');
            const filtered = getFilteredTasks().filter(t => !t.done);
            filtered.sort((a, b) => (a.priority === 'high' ? -1 : 1));
            if (filtered.length === 0) {
                list.innerHTML = '<div class="text-center py-10 text-slate-400 text-xs">تبریک! هیچ تسک انجام‌نشده‌ای برای نمایش وجود ندارد. 🎉</div>';
                return;
            }
            list.innerHTML = filtered.slice(0, 5).map(task => createTaskCardHTML(task)).join('');
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
            const total = state.tasks.length;
            const completed = state.tasks.filter(t => t.done).length;
            const completedToday = state.tasks.filter(t => t.done && t.date === TASKYAR.today).length;
            const remainingToday = state.tasks.filter(t => !t.done && t.date === TASKYAR.today).length;
            const urgentCount = state.tasks.filter(t => t.priority === 'high' && !t.done).length;
            const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
            document.getElementById('statTotalTasks').innerText = faNum(total);
            document.getElementById('statCompletedToday').innerText = faNum(completedToday);
            document.getElementById('statRemainingToday').innerText = faNum(remainingToday);
            document.getElementById('statUrgentTasks').innerText = faNum(urgentCount);
            document.getElementById('statCompletionRate').innerText = faNum(percentage) + '٪';
            document.getElementById('progressCircle').setAttribute('stroke-dasharray', percentage + ', 100');
            document.getElementById('progressPercentageText').innerText = faNum(percentage) + '٪';
            document.getElementById('statPomodoroSessions').innerText = faNum(state.pomoSessions);
            document.getElementById('statPomoMinutes').innerText = faNum(state.pomoSessions * 25);
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
        let pomoTimeLeft = 25 * 60;
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
                pomoTimeLeft = 25 * 60;
                document.getElementById('pomoBtnWork').className = 'flex-1 py-1 text-xs font-bold rounded-lg bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm transition';
                document.getElementById('pomoStatusText').innerText = 'زمان تمرکز عمیق و کار پربازده';
            } else if (mode === 'shortBreak') {
                pomoTimeLeft = 5 * 60;
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
        }

        function renderPomoDisplay() {
            const minutes = Math.floor(pomoTimeLeft / 60);
            const seconds = pomoTimeLeft % 60;
            const formatted = String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
            document.getElementById('pomoDisplay').innerText = formatted;
            document.getElementById('headerPomoTimer').innerText = formatted;
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
</body>
</html>
