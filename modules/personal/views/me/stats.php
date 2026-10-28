<?php
// صفحه آمار: فقط کلیدهایی که واقعاً در $stats پاس شده‌اند رندر می‌شوند؛
// هیچ عددی ساخته یا حدس زده نمی‌شود.
// کلیدهای پشتیبانی‌شده: focus_7, focus_30, done_7, done_30, done_total,
// habit_streaks (فهرست)، by_category (نگاشت دسته=>تعداد انجام‌شده).
$__s = (isset($stats) && is_array($stats)) ? $stats : [];
$__cards = [
    'focus_7' => ['دقیقه تمرکز ۷ روز اخیر', 'fa-stopwatch'],
    'focus_30' => ['دقیقه تمرکز ۳۰ روز اخیر', 'fa-hourglass-half'],
    'done_7' => ['کار انجام‌شده ۷ روز اخیر', 'fa-list-check'],
    'done_30' => ['کار انجام‌شده ۳۰ روز اخیر', 'fa-calendar-check'],
    'done_total' => ['مجموع کارهای انجام‌شده', 'fa-circle-check'],
];
?>
<div class="me-stat-grid">
    <?php foreach ($__cards as $__k => $__meta): ?>
        <?php if (isset($__s[$__k])): ?>
        <div class="me-card me-stat-card">
            <span class="me-stat-icon"><i class="fa-solid <?= $__meta[1] ?>"></i></span>
            <span class="me-stat-num"><?= (int) $__s[$__k] ?></span>
            <span class="me-stat-label"><?= $__meta[0] ?></span>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php if (!empty($__s['by_category']) && is_array($__s['by_category'])): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-chart-column"></i> انجام‌شده‌ها بر اساس دسته</h3>
    <?php $__max = max(array_map('intval', $__s['by_category'])) ?: 1; ?>
    <div class="me-cat-bars">
    <?php foreach ($__s['by_category'] as $__cat => $__n): ?>
        <div class="me-cat-bar-row">
            <span class="me-cat-bar-label"><?= e((string) $__cat) ?></span>
            <div class="me-progress-bar-bg"><div class="me-progress-bar-fill" style="width:<?= (int) round(100 * (int) $__n / $__max) ?>%"></div></div>
            <span class="me-cat-bar-num"><?= (int) $__n ?></span>
        </div>
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
<div class="me-card"><p class="me-empty">هنوز آماری ثبت نشده است.</p></div>
<?php endif; ?>
