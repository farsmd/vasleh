<?php
// مرور هفتگی: فقط کلیدهای آمار که واقعاً پاس شده‌اند نمایش داده می‌شوند.
// داده: $stats (کلیدهای اختیاری) + $week یا $weekSat (شنبه هفته، Y-m-d).
$__week = (string) ($week ?? $weekSat ?? '');
if ($__week === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $__week)) {
    $__week = date('Y-m-d', strtotime('last saturday', strtotime(date('Y-m-d') . ' +1 day')));
}
$__prev = date('Y-m-d', strtotime($__week . ' -7 days'));
$__next = date('Y-m-d', strtotime($__week . ' +7 days'));
$__end = date('Y-m-d', strtotime($__week . ' +6 days'));
$__s = (isset($stats) && is_array($stats)) ? $stats : [];
?>
<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-solid fa-calendar-week"></i> مرور هفته</h3>
        <span class="me-section-action"><?= e($__week) ?> تا <?= e($__end) ?></span>
    </div>
    <div class="me-nav-row">
        <a href="/me/review?week=<?= e($__prev) ?>" class="me-nav-btn">هفته قبل</a>
        <a href="/me/review?week=<?= e($__next) ?>" class="me-nav-btn">هفته بعد</a>
    </div>
</div>
<div class="me-stat-grid">
    <?php if (isset($__s['done'], $__s['total'])): ?>
    <div class="me-card me-stat-card">
        <span class="me-stat-num"><?= (int) $__s['done'] ?> از <?= (int) $__s['total'] ?></span>
        <span class="me-stat-label">کار انجام‌شده این هفته</span>
        <?php if (isset($__s['pct'])): ?><div class="me-progress-bar-bg"><div class="me-progress-bar-fill" style="width:<?= max(0, min(100, (int) $__s['pct'])) ?>%"></div></div><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if (isset($__s['pct']) && !isset($__s['done'], $__s['total'])): ?>
    <div class="me-card me-stat-card"><span class="me-stat-num"><?= (int) $__s['pct'] ?>٪</span><span class="me-stat-label">درصد تکمیل هفته</span></div>
    <?php endif; ?>
    <?php if (isset($__s['pomo_sessions'])): ?>
    <div class="me-card me-stat-card"><span class="me-stat-num"><?= (int) $__s['pomo_sessions'] ?></span><span class="me-stat-label">جلسه پومودورو این هفته</span></div>
    <?php endif; ?>
    <?php if (isset($__s['pomo_minutes'])): ?>
    <div class="me-card me-stat-card"><span class="me-stat-num"><?= (int) $__s['pomo_minutes'] ?></span><span class="me-stat-label">دقیقه تمرکز این هفته</span></div>
    <?php endif; ?>
    <?php if (isset($__s['overdue'])): ?>
    <div class="me-card me-stat-card"><span class="me-stat-num"><?= (int) $__s['overdue'] ?></span><span class="me-stat-label">کار ناتمام هفته</span></div>
    <?php endif; ?>
</div>
<?php if (!empty($__s['projects_in_progress']) && is_array($__s['projects_in_progress'])): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-diagram-project"></i> پروژه‌های در حال پیشرفت</h3>
    <div class="me-search-list">
    <?php foreach ($__s['projects_in_progress'] as $__p): ?>
        <p class="me-note-row"><?php if (is_array($__p)): ?><a href="/me/projects/<?= (int) ($__p['id'] ?? 0) ?>"><?= e($__p['title'] ?? '') ?></a><?php else: ?><?= e((string) $__p) ?><?php endif; ?></p>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php if (!empty($__s['habit_streaks']) && is_array($__s['habit_streaks'])): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-fire"></i> استریک عادت‌ها</h3>
    <div class="me-search-list">
    <?php foreach ($__s['habit_streaks'] as $__h): ?>
        <p class="me-search-row"><span class="me-search-title"><?= e(is_array($__h) ? ($__h['title'] ?? '') : (string) $__h) ?></span><?php if (is_array($__h) && isset($__h['streak'])): ?><span class="me-badge me-badge-streak"><?= (int) $__h['streak'] ?> روز</span><?php endif; ?></p>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php if ($__s === []): ?>
<div class="me-card"><p class="me-empty">آماری برای این هفته ثبت نشده است.</p></div>
<?php endif; ?>
