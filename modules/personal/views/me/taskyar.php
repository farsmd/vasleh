<?php
// صفحه اصلی /me — ساختار دقیق ماکاپ (قاب گوشی): ستون وسط‌چین، بدون
// هدر بالا، بدون تب نما، بدون جست‌وجوی سربرگ و بدون ردیف فیلتر.
// فقط همین بخش‌ها، به همین ترتیب: سلام، بنتو (پومودورو + پیشرفت)،
// تقویم، کارها، دوتایی (پروژه‌ها + یادداشت سریع)، داک پایین.
$__meStyle = (isset($settings['style']) && isset(\Modules\Personal\Personal::STYLES[$settings['style']])) ? $settings['style'] : 'darkPrism';
$__today = (string) ($today ?? date('Y-m-d'));
$__themeColors = ['darkPrism' => '#090A0F', 'zenCanvas' => '#F6F4F0', 'swissPop' => '#F2F3F7', 'monoGrid' => '#111215'];
$__themeColor = $__themeColors[$__meStyle] ?? '#090A0F';
// نسخه از version.json برای شکستن کش مرورگر روی me-shell.css (بدون این،
// سافاری بعد از آپدیت فایل CSS قدیمی را نگه می‌داشت و چیدمان تازه خراب دیده می‌شد).
$__assetV = '8.2.23';
$__vf = dirname(__DIR__, 4) . '/version.json';
if (is_file($__vf)) {
    $__vp = json_decode((string) file_get_contents($__vf), true);
    if (is_array($__vp)) { $__assetV = (int) ($__vp['core'] ?? 0) . '.' . (int) ($__vp['system'] ?? 0) . '.' . (int) ($__vp['other'] ?? 0); }
}
$__cap = (int) ($settings['day_capacity'] ?? 0);
$__load = (int) ($workload ?? 0);
$__overdue = array_values(array_filter($tasks, static fn ($t) => ($t['kind'] ?? '') === 'todo' && ($t['date'] ?? '') !== '' && ($t['date'] ?? '') < $__today && empty($t['done'])));
$__latestNote = (!empty($notes) && is_array($notes)) ? $notes[0] : null;
$__noteExcerpt = '';
if ($__latestNote !== null && !empty($__latestNote['body'])) {
    if (preg_match('/^.{0,120}/us', (string) $__latestNote['body'], $__m)) { $__noteExcerpt = $__m[0]; }
    if (preg_match('/^.{121,}/us', (string) $__latestNote['body'])) { $__noteExcerpt .= '…'; }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="<?= $__meStyle ?>">
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
<body class="me-has-dock">

    <main class="me-page">
        <?php if (!empty($flash)): ?><div class="me-card"><p class="me-alert-text"><?= e($flash) ?></p></div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="me-card"><p class="me-alert-text"><?= e($error) ?></p></div><?php endif; ?>

        <!-- ۱. فقط سلام: تاریخ جلالی بالا، «تمرکز امروز، نام کاربری» -->
        <section class="me-greeting">
            <p><?= \Modules\Personal\Personal::faWeekday($__today) ?>، <?= \Modules\Personal\Personal::faDate($__today) ?></p>
            <h2>تمرکز امروز<span class="me-greeting-accent">، <?= e($user['username'] ?? '') ?></span></h2>
        </section>

        <!-- ۲. بنتو دو ستونه: پومودورو + پیشرفت روز -->
        <section class="me-bento-grid">
            <div class="me-card me-pomo-card">
                <div class="me-card-top">
                    <span class="me-card-label"><i class="fa-regular fa-clock"></i> پومودورو</span>
                    <span class="me-pomo-tag" id="bentoPomoSessions">۰ جلسه تکمیل‌شده</span>
                </div>
                <div class="me-pomo-display" onclick="togglePomodoroModal()" title="باز کردن تایمر کامل">
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

        <!-- ۳. تقویم: نوار هفته شنبه تا جمعه + رویدادهای امروز -->
        <section class="me-card">
            <div class="me-section-head">
                <h3 class="me-section-title"><i class="fa-regular fa-calendar-days"></i> برنامه زمانی و تقویم</h3>
                <span class="me-section-action" id="bentoMonthLabel"></span>
            </div>
            <div class="me-cal-strip" id="bentoCalStrip"></div>
            <div class="me-event-list" id="bentoEventsList"></div>
        </section>

        <!-- ۴. کارها: تودوهای امروز با تیک، هشدار بار روز و انتقال ناتمام‌ها فقط دو خط فشرده -->
        <section class="me-card">
            <div class="me-section-head">
                <h3 class="me-section-title"><i class="fa-solid fa-list-check"></i> کارهای اولویت‌دار امروز</h3>
                <span class="me-tasks-actions">
                    <a href="/me/search" class="me-section-action">جستجو</a>
                    <button onclick="openAddTaskModal()" class="me-section-action me-action-btn">+ افزودن کار</button>
                </span>
            </div>
            <?php if ($__cap > 0 && $__load > $__cap): ?>
            <p class="me-tasks-line me-line-warn"><i class="fa-solid fa-triangle-exclamation"></i> بار امروز <?= $__load ?> دقیقه است؛ بیشتر از ظرفیت <?= $__cap ?> دقیقه‌ای روز.</p>
            <?php endif; ?>
            <?php if ($__overdue !== []): ?>
            <form method="post" action="/me/rollover" class="me-tasks-line">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="me-mini-btn"><i class="fa-solid fa-forward"></i> انتقال <?= count($__overdue) ?> کار ناتمام به امروز</button>
            </form>
            <?php endif; ?>
            <div id="bentoTodoList"></div>
        </section>

        <!-- ۵. دوتایی: پروژه‌ها + یادداشت سریع (تازه‌ترین یادداشت واقعی) -->
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
                <?php if ($__latestNote === null): ?>
                    <p class="me-empty">هنوز یادداشتی نیست.</p>
                <?php else: ?>
                    <div class="me-note-snippet">
                        <a href="/me/notes/<?= (int) $__latestNote['id'] ?>" class="me-note-snippet-title"><?= e($__latestNote['title']) ?></a>
                        <?php if ($__noteExcerpt !== ''): ?><p class="me-note-excerpt"><?= e($__noteExcerpt) ?></p><?php endif; ?>
                        <?php if (!empty($__latestNote['tags']) && is_array($__latestNote['tags'])): ?>
                        <div class="me-note-tags">
                            <?php foreach ($__latestNote['tags'] as $__tg): ?><span class="me-tag-chip">#<?= e($__tg) ?></span><?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- ۶. داک شناور پایین -->
    <nav class="me-dock" aria-label="ناوبری تسک‌یار">
        <a href="/me" class="me-dock-item me-dock-active">
            <i class="fa-solid fa-gauge-high"></i><span>داشبورد</span>
        </a>
        <a href="/me/calendar" class="me-dock-item">
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

    <!-- مودال افزودن/ویرایش تسک (همان فرم قبلی، با کلاس‌های توکن‌محور) -->
    <div id="taskModal" class="me-modal-overlay hidden">
        <div class="me-card me-modal-box animate-pop">
            <div class="me-section-head">
                <h3 id="modalTitle" class="me-section-title">افزودن تسک جدید</h3>
                <button onclick="closeAddTaskModal()" class="me-icon-btn"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="taskForm" onsubmit="handleTaskSubmit(event)" class="me-modal-form">
                <input type="hidden" id="taskId">
                <input type="hidden" id="taskKind">
                <div>
                    <label class="me-field-label">عنوان کار یا هدف *</label>
                    <input type="text" id="taskTitle" required placeholder="مثال: نوشتن پروپوزال پروژه، تمرین بدنسازی..." class="me-note-input">
                </div>
                <div class="me-form-grid">
                    <div>
                        <label class="me-field-label">دسته‌بندی</label>
                        <select id="taskCategory" class="me-select">
                            <?php foreach ($settings['categories'] as $c): ?>
                            <option value="<?= e($c['key']) ?>"><?= e($c['label']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="me-field-label">سطح اولویت</label>
                        <select id="taskPriority" class="me-select">
                            <option value="high">بالا و فوری</option>
                            <option value="medium" selected>متوسط</option>
                            <option value="low">پایین و روتین</option>
                        </select>
                    </div>
                </div>
                <div class="me-form-grid">
                    <div>
                        <div class="me-chip-row me-date-chips">
                            <button type="button" class="me-chip" onclick="setTaskDayFromChip('today')">امروز</button>
                            <button type="button" class="me-chip" onclick="setTaskDayFromChip('tomorrow')">فردا</button>
                            <button type="button" class="me-chip" onclick="setTaskDayFromChip('saturday')">شنبه</button>
                        </div>
                        <label class="me-field-label">روز هدف</label>
                        <select id="taskDay" class="me-select">
                            <option value="0">شنبه</option>
                            <option value="1">یکشنبه</option>
                            <option value="2">دوشنبه</option>
                            <option value="3">سه‌شنبه</option>
                            <option value="4">چهارشنبه</option>
                            <option value="5">پنج‌شنبه</option>
                            <option value="6">جمعه</option>
                        </select>
                        <span class="me-settings-hint" id="weekRangeHint">هفته: <?= \Modules\Personal\Personal::faDate($weekSat) ?> تا <?= \Modules\Personal\Personal::faDate($weekSun) ?></span>
                    </div>
                    <div>
                        <label class="me-field-label">زمان‌بندی ساعت (بلوک زمانی)</label>
                        <select id="taskHour" class="me-select">
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
                <div class="me-form-grid">
                    <div>
                        <label class="me-field-label">مدت تخمینی (دقیقه)</label>
                        <input type="number" id="taskDuration" min="5" step="5" value="30" class="me-note-input">
                    </div>
                    <div>
                        <label class="me-field-label">یادداشت تکمیلی (اختیاری)</label>
                        <input type="text" id="taskNote" placeholder="لینک‌ها یا جزییات کوچک..." class="me-note-input">
                    </div>
                </div>
                <div class="me-modal-actions">
                    <button type="button" onclick="closeAddTaskModal()" class="me-mini-btn">انصراف</button>
                    <button type="submit" class="me-note-btn">ثبت و ذخیره</button>
                </div>
            </form>
        </div>
    </div>

    <!-- مودال تایمر پومودورو (همان منطق قبلی) -->
    <div id="pomodoroModal" class="me-modal-overlay hidden">
        <div class="me-card me-modal-box me-modal-box-sm animate-pop">
            <button onclick="togglePomodoroModal()" class="me-icon-btn me-modal-close"><i class="fa-solid fa-xmark"></i></button>
            <div class="me-pomo-modes">
                <button onclick="setPomoMode('work')" id="pomoBtnWork" class="me-mini-btn">تمرکز (<?= strtr((string) (int) $settings['pomo_focus'], '0123456789', '۰۱۲۳۴۵۶۷۸۹') ?> د)</button>
                <button onclick="setPomoMode('shortBreak')" id="pomoBtnShort" class="me-mini-btn">استراحت کوتاه</button>
                <button onclick="setPomoMode('longBreak')" id="pomoBtnLong" class="me-mini-btn">استراحت بلند</button>
            </div>
            <div class="me-pomo-big">
                <span id="pomoDisplay" class="me-pomo-big-time"><?= sprintf('%02d:00', (int) $settings['pomo_focus']) ?></span>
            </div>
            <p id="pomoStatusText" class="me-pomo-status">آماده برای شروع جلسه تمرکز عمیق</p>
            <div class="me-pomo-controls">
                <button onclick="togglePomoTimer()" id="pomoStartBtn" class="me-note-btn"><i class="fa-solid fa-play"></i><span>شروع تمرکز</span></button>
                <button onclick="resetPomoTimer()" class="me-mini-btn"><i class="fa-solid fa-rotate-left"></i></button>
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

        let state = {
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
            document.getElementById('weekRangeHint').innerText = 'هفته جاری: ' + TASKYAR.weekSatFa + ' تا ' + TASKYAR.weekSunFa;
            renderBento();
            updateDashboardMetrics();
            // دکمه + صفحه‌های فرعی به /me#add می‌آید؛ فرم افزودن را مستقیم باز کن.
            if (window.location.hash === '#add') {
                try { history.replaceState(null, '', window.location.pathname + window.location.search); } catch (e) {}
                openAddTaskModal();
            }
        });

        function getCategoryMeta(cat) {
            const cats = TASKYAR.categories || [];
            for (let i = 0; i < cats.length; i++) {
                if (cats[i].key === cat) { return { label: cats[i].label }; }
            }
            return { label: 'عمومی' };
        }

        function getPriorityMeta(prio) {
            switch(prio) {
                case 'high': return { label: 'فوری' };
                case 'medium': return { label: 'متوسط' };
                case 'low': return { label: 'عادی' };
                default: return { label: 'عادی' };
            }
        }

        function renderBento() {
            // نوار هفته (شنبه تا جمعه) + ماه جلالی — عین ماکاپ، با دادهٔ واقعی.
            const strip = document.getElementById('bentoCalStrip');
            let stripHtml = '';
            for (let i = 0; i < 7; i++) {
                const d = dateAdd(TASKYAR.weekSat, i);
                const isToday = d === TASKYAR.today;
                let dayNum = d.slice(8, 10).replace(/^0/, '');
                try { dayNum = new Intl.DateTimeFormat('fa-IR', { day: 'numeric' }).format(new Date(d + 'T00:00:00Z')); } catch (e) {}
                stripHtml += '<a href="/me/calendar?date=' + d + '" class="me-cal-day' + (isToday ? ' selected' : '') + '"><span class="me-cal-day-name">' + DAYS_OF_WEEK[i] + '</span><span class="me-cal-day-num">' + dayNum + '</span></a>';
            }
            strip.innerHTML = stripHtml;
            try { document.getElementById('bentoMonthLabel').innerText = new Intl.DateTimeFormat('fa-IR', { month: 'long', year: 'numeric' }).format(new Date(TASKYAR.today + 'T00:00:00Z')); } catch (e) {}
            // رویدادهای امروز (ساعت‌دار)، به ترتیب ساعت.
            const eventsBox = document.getElementById('bentoEventsList');
            const events = state.tasks.filter(t => t.kind === 'event' && t.date === TASKYAR.today).sort((a, b) => (a.hour || '').localeCompare(b.hour || ''));
            eventsBox.innerHTML = events.length === 0
                ? '<p class="me-empty">رویدادی برای امروز نیست.</p>'
                : events.map(t => createTaskCardHTML(t)).join('');
            // کارهای اولویت‌دار امروز (تودوهای انجام‌نشدهٔ امروز، فوری اول) + بخش «آینده» برای شروع‌های آینده.
            const list = document.getElementById('bentoTodoList');
            const todos = state.tasks.filter(t => t.kind === 'todo' && !t.done && t.date === TASKYAR.today);
            todos.sort((a, b) => (a.priority === 'high' ? -1 : (b.priority === 'high' ? 1 : 0)));
            const todosNow = todos.filter(t => !(t.start_date && t.start_date > TASKYAR.today));
            const todosFuture = todos.filter(t => t.start_date && t.start_date > TASKYAR.today);
            let todosHtml = todosNow.length === 0
                ? '<div class="me-empty">کاری برای امروز نیست.</div>'
                : todosNow.slice(0, 8).map(t => createTaskCardHTML(t)).join('');
            if (todosFuture.length > 0) {
                todosHtml += '<h4 class="me-future-head"><i class="fa-regular fa-hourglass-half"></i> آینده</h4>' + todosFuture.map(t => createTaskCardHTML(t)).join('');
            }
            list.innerHTML = todosHtml;
        }

        function createTaskCardHTML(task) {
            const catMeta = getCategoryMeta(task.category);
            const prioMeta = getPriorityMeta(task.priority);
            const timeLabel = task.hour === 'all-day' ? 'تمام روز' : task.hour + (task.end ? ' تا ' + task.end : '');
            let tagsHtml = '';
            if (Array.isArray(task.tags)) {
                tagsHtml = task.tags.map(function (tg) { return '<span class="me-tag-chip">#' + esc(tg) + '</span>'; }).join('');
            }
            const recurLabels = { daily: 'روزانه', weekly: 'هفتگی', monthly: 'ماهانه' };
            const recurBadge = (task.recur && task.recur !== 'none') ? '<span class="me-badge me-badge-recur"><i class="fa-solid fa-repeat"></i> ' + (recurLabels[task.recur] || esc(task.recur)) + '</span>' : '';
            const remindHtml = task.remind_at ? '<span class="me-badge"><i class="fa-regular fa-bell"></i> یادآوری ' + esc(task.remind_at) + '</span>' : '';
            const startBadge = (task.start_date && task.start_date > TASKYAR.today) ? '<span class="me-badge me-badge-future">شروع: ' + faDateOf(task.start_date) + '</span>' : '';
            let subsHtml = '';
            if (task.kind === 'todo' && Array.isArray(task.subtasks)) {
                const items = task.subtasks.map(function (st) {
                    return '<div class="me-sub-row">'
                        + '<form method="post" action="/me/todos/subtasks/' + st.id + '/toggle"><input type="hidden" name="_csrf" value="' + TASKYAR.csrf + '"><input type="checkbox"' + (st.done ? ' checked' : '') + ' onchange="this.form.submit()" class="me-check"></form>'
                        + '<span class="' + (st.done ? 'me-done' : '') + '">' + esc(st.title) + '</span>'
                        + '<form method="post" action="/me/todos/subtasks/' + st.id + '/delete" onsubmit="return confirm(\'این زیرکار حذف شود؟\')"><input type="hidden" name="_csrf" value="' + TASKYAR.csrf + '"><button class="me-icon-btn" title="حذف"><i class="fa-regular fa-trash-can"></i></button></form>'
                        + '</div>';
                }).join('');
                subsHtml = '<div class="me-sub-list">' + items
                    + '<form method="post" action="/me/todos/' + task.id + '/subtasks" class="me-sub-add"><input type="hidden" name="_csrf" value="' + TASKYAR.csrf + '"><input type="text" name="title" required placeholder="زیرکار تازه..." class="me-note-input"><button class="me-mini-btn">افزودن</button></form></div>';
            }
            return '<div class="me-task-row">'
                + '<button onclick="toggleTaskStatus(\'' + task.kind + '\', ' + task.id + ')" class="me-task-check' + (task.done ? ' done' : '') + '" title="تغییر وضعیت">'
                + (task.done ? '<i class="fa-solid fa-check"></i>' : '') + '</button>'
                + '<div class="me-task-main"><div class="me-task-title' + (task.done ? ' done' : '') + '">' + esc(task.title) + '</div>'
                + '<div class="me-task-meta">'
                + '<span class="me-tag-chip">' + catMeta.label + '</span>'
                + '<span class="me-tag-chip">' + prioMeta.label + '</span>'
                + '<span><i class="fa-regular fa-clock"></i> ' + timeLabel + '</span>'
                + (task.duration ? '<span>(' + faNum(task.duration) + ' د)</span>' : '')
                + (task.date ? '<span>' + faDateOf(task.date) + '</span>' : '<span>بدون تاریخ</span>')
                + startBadge + recurBadge + remindHtml + tagsHtml
                + '</div>' + subsHtml + '</div>'
                + '<div class="me-task-actions">'
                + '<button onclick="openEditTaskModal(\'' + task.kind + '\', ' + task.id + ')" title="ویرایش" class="me-icon-btn"><i class="fa-regular fa-pen-to-square"></i></button>'
                + '<button onclick="deleteTask(\'' + task.kind + '\', ' + task.id + ')" title="حذف" class="me-icon-btn"><i class="fa-regular fa-trash-can"></i></button>'
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
                renderBento();
            });
        }

        function deleteTask(kind, id) {
            if (!confirm('این تسک حذف شود؟')) return;
            postTask('/me/task/' + kind + '/' + id + '/delete', {}).then(res => {
                if (!res.ok) return;
                state.tasks = state.tasks.filter(t => !(t.kind === kind && t.id === id));
                updateDashboardMetrics();
                renderBento();
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
                renderBento();
            });
        }

        // چیپ‌های تاریخ سریع در فرم افزودن: روز هدف (select روز هفته) را پر می‌کنند.
        function setTaskDayFromChip(which) {
            let d = TASKYAR.today;
            if (which === 'tomorrow') d = dateAdd(TASKYAR.today, 1);
            if (which === 'saturday') { document.getElementById('taskDay').value = '0'; return; }
            document.getElementById('taskDay').value = String(uiDayOf(d));
        }

        function openAddTaskModal() {
            document.getElementById('taskForm').reset();
            document.getElementById('taskId').value = '';
            document.getElementById('taskKind').value = '';
            document.getElementById('modalTitle').innerText = 'افزودن تسک جدید';
            document.getElementById('taskDay').value = String(uiDayOf(TASKYAR.date));
            document.getElementById('taskModal').classList.remove('hidden');
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

        // Pomodoro Timer Logic (همان منطق قبلی: تایمر کارت + مودال همگام‌اند)
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
            const modes = { work: 'pomoBtnWork', shortBreak: 'pomoBtnShort', longBreak: 'pomoBtnLong' };
            Object.keys(modes).forEach(m => {
                document.getElementById(modes[m]).className = 'me-mini-btn' + (m === mode ? ' me-mode-active' : '');
            });
            if (mode === 'work') {
                pomoTimeLeft = (TASKYAR.pomoFocus || 25) * 60;
                document.getElementById('pomoStatusText').innerText = 'زمان تمرکز عمیق و کار پربازده';
            } else if (mode === 'shortBreak') {
                pomoTimeLeft = (TASKYAR.pomoBreak || 5) * 60;
                document.getElementById('pomoStatusText').innerText = 'استراحت کوتاه: چند نفس عمیق بکشید و آب بنوشید';
            } else {
                pomoTimeLeft = 15 * 60;
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
                            // ثبت پومودورو در سرور: best-effort، خطا هرگز تایمر را نمی‌شکند.
                            try {
                                const __fd = new FormData();
                                __fd.append('_csrf', TASKYAR.csrf);
                                __fd.append('minutes', String(TASKYAR.pomoFocus || 25));
                                fetch('/me/pomo/log', { method: 'POST', body: __fd }).catch(function () {});
                            } catch (e) {}
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
            if (btn) {
                btn.innerHTML = pomoIsRunning ? '<i class="fa-solid fa-pause"></i> <span>توقف</span>' : '<i class="fa-solid fa-play"></i> <span>شروع</span>';
            }
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
    <!-- پالت فرمان Ctrl+K: فقط کیبورد، روی صفحه‌های موجود + ثبت سریع؛ بدون بک‌اند تازه -->
    <div id="cmdPalette" class="me-cmd-overlay hidden">
        <div class="me-cmd-box">
            <input type="text" id="cmdPaletteInput" oninput="meCmdFilter()" placeholder="بگردید یا دستوری بنویسید... (Ctrl+K برای باز و Esc برای بستن)" class="me-cmd-input">
            <div id="cmdPaletteList" class="me-cmd-list"></div>
        </div>
    </div>
    <script>
    const ME_CMD_LINKS = [
        ['داشبورد و تسک‌ها', '/me'], ['داشبورد شخصی', '/me/dashboard'], ['تقویم', '/me/calendar'],
        ['یادداشت‌ها', '/me/notes'], ['پروژه‌ها', '/me/projects'], ['عادت‌ها', '/me/habits'],
        ['اهداف', '/me/goals'], ['ژورنال', '/me/journal'], ['آمار', '/me/stats'],
        ['بورد', '/me/board'], ['ماتریس', '/me/matrix'], ['مرور هفتگی', '/me/review'],
        ['جست‌وجو', '/me/search'], ['اشتراک‌ها', '/me/shared'], ['تنظیمات', '/me/settings'],
    ];
    function meCmdOpen() {
        const ov = document.getElementById('cmdPalette');
        ov.classList.remove('hidden');
        meCmdFilter();
        document.getElementById('cmdPaletteInput').focus();
    }
    function meCmdClose() { document.getElementById('cmdPalette').classList.add('hidden'); }
    function meCmdFilter() {
        const q = (document.getElementById('cmdPaletteInput').value || '').toLowerCase();
        const list = document.getElementById('cmdPaletteList');
        let html = '<button type="button" class="me-cmd-item" onclick="meCmdClose();openAddTaskModal();"><i class="fa-solid fa-plus"></i> افزودن سریع تسک</button>';
        ME_CMD_LINKS.forEach(function (l) {
            if (!q || l[0].indexOf(q) !== -1 || l[1].indexOf(q) !== -1) {
                html += '<a href="' + l[1] + '" class="me-cmd-item">' + l[0] + '<span>' + l[1] + '</span></a>';
            }
        });
        list.innerHTML = html;
    }
    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const ov = document.getElementById('cmdPalette');
            if (ov.classList.contains('hidden')) { meCmdOpen(); } else { meCmdClose(); }
        } else if (e.key === 'Escape') {
            meCmdClose();
        } else if (e.key === 'Enter' && !document.getElementById('cmdPalette').classList.contains('hidden')) {
            const first = document.querySelector('#cmdPaletteList .me-cmd-item');
            if (first && document.activeElement === document.getElementById('cmdPaletteInput')) {
                e.preventDefault();
                first.click();
            }
        }
    });
    document.getElementById('cmdPalette').addEventListener('click', function (e) {
        if (e.target === this) { meCmdClose(); }
    });
    </script>
    <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', function () {
        navigator.serviceWorker.register('/me/sw.js?v=<?= e($__assetV) ?>', { scope: '/me/' }).catch(function () {});
      });
    }
    </script>
</body>
</html>
