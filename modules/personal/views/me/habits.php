<?php
// صفحه عادت‌ها: هیروی استریک + ماتریس هفته (شنبه تا جمعه) + نمودار میله‌ای
// تکمیل هفتگی + کارت هر عادت (تیک امروز، استریک، هیت‌مپ ۱۲ هفته) + فرم افزودن.
// داده: $habits (id, title, target_per_week, streak, record_streak?, done_today,
// heatmap: تاریخ‌های انجام‌شده یا نگاشت تاریخ=>1) + $today (اختیاری).
// هر فیلدی که پاس نشده باشد، آن بخش بی‌صدا حذف می‌شود.
$__today = (string) ($today ?? date('Y-m-d'));
$__habits = (isset($habits) && is_array($habits)) ? $habits : [];
$__daysFa = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'];
$__sat = \Modules\Personal\Personal::weekSaturday($__today);
$__best = 0; $__cur = 0;
foreach ($__habits as $__h) {
    if (isset($__h['streak'])) { $__cur = max($__cur, (int) $__h['streak']); }
    if (isset($__h['record_streak'])) { $__best = max($__best, (int) $__h['record_streak']); }
}
?>
<?php if ($__habits !== [] && (isset($habits[0]['streak']) || isset($habits[0]['record_streak']))): ?>
<div class="me-stat-grid">
    <div class="me-card me-stat-card"><span class="me-stat-num"><?= $__cur ?> روز</span><span class="me-stat-label">بهترین استریک جاری</span></div>
    <?php if ($__best > 0): ?><div class="me-card me-stat-card"><span class="me-stat-num"><?= $__best ?> روز</span><span class="me-stat-label">رکورد استریک</span></div><?php endif; ?>
</div>
<?php endif; ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-repeat"></i> عادت تازه</h3>
    <form method="post" action="/me/habits" class="me-inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="text" name="title" required placeholder="عنوان عادت..." class="me-note-input">
        <label class="me-field-inline">هدف در هفته
            <input type="number" name="target_per_week" min="1" max="7" value="7" class="me-input-sm">
        </label>
        <button class="me-note-btn">افزودن عادت</button>
    </form>
</div>
<?php if ($__habits === []): ?>
    <div class="me-card"><p class="me-empty">هنوز عادتی ثبت نشده است.</p></div>
<?php else: ?>
    <?php foreach ($__habits as $__h): ?>
    <?php
        $__doneDates = [];
        $__heat = $__h['heatmap'] ?? $__h['logs'] ?? [];
        if (is_array($__heat)) {
            foreach ($__heat as $__k => $__v) {
                if (is_int($__k)) { $__doneDates[(string) $__v] = true; }
                elseif (!empty($__v)) { $__doneDates[(string) $__k] = true; }
            }
        }
        // نمودار هفتگی: تعداد روزهای انجام‌شده در هر یک از ۸ هفته اخیر.
        $__weeks = [];
        for ($__w = 7; $__w >= 0; $__w--) {
            $__ws = date('Y-m-d', strtotime($__sat . ' -' . ($__w * 7) . ' days'));
            $__n = 0;
            for ($__i = 0; $__i < 7; $__i++) {
                if (isset($__doneDates[date('Y-m-d', strtotime($__ws . ' +' . $__i . ' days'))])) { $__n++; }
            }
            $__weeks[] = $__n;
        }
    ?>
    <div class="me-card me-habit-card">
        <div class="me-habit-head">
            <form method="post" action="/me/habits/<?= (int) $__h['id'] ?>/toggle" class="me-habit-check">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="date" value="<?= e($__today) ?>">
                <input type="checkbox" <?= !empty($__h['done_today']) ? 'checked' : '' ?> onchange="this.form.submit()" class="me-check" title="انجام امروز">
            </form>
            <span class="me-habit-title<?= !empty($__h['done_today']) ? ' me-done' : '' ?>"><?= e($__h['title'] ?? '') ?></span>
            <?php if (isset($__h['streak'])): ?><span class="me-badge me-badge-streak"><i class="fa-solid fa-fire"></i> <?= (int) $__h['streak'] ?> روز پیاپی</span><?php endif; ?>
            <?php if (isset($__h['record_streak'])): ?><span class="me-badge">رکورد <?= (int) $__h['record_streak'] ?> روز</span><?php endif; ?>
            <?php if (isset($__h['target_per_week'])): ?><span class="me-habit-target"><?= (int) $__h['target_per_week'] ?> بار در هفته</span><?php endif; ?>
            <form method="post" action="/me/habits/<?= (int) $__h['id'] ?>/delete" onsubmit="return confirm('این عادت حذف شود؟')" class="me-habit-del">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="me-icon-btn" title="حذف"><i class="fa-regular fa-trash-can"></i></button>
            </form>
        </div>
        <div class="me-week-matrix">
            <?php for ($__i = 0; $__i < 7; $__i++): $__d = date('Y-m-d', strtotime($__sat . ' +' . $__i . ' days')); ?>
            <span class="me-matrix-cell<?= isset($__doneDates[$__d]) ? ' done' : '' ?>" title="<?= e($__d) ?>"><?= $__daysFa[$__i] ?></span>
            <?php endfor; ?>
        </div>
        <div class="me-week-bars">
            <?php foreach ($__weeks as $__n): ?>
            <div class="me-bar-col"><div class="me-bar-fill<?= $__n > 0 ? ' active' : '' ?>" style="height:<?= max(4, (int) round(100 * $__n / 7)) ?>%"></div><span class="me-bar-day"><?= $__n ?></span></div>
            <?php endforeach; ?>
        </div>
        <p class="me-heat-legend">میله‌ها: روزهای انجام‌شده در هر هفته (۸ هفته اخیر).</p>
        <div class="me-heatmap" aria-label="۱۲ هفته اخیر">
            <?php for ($__i = 83; $__i >= 0; $__i--): ?>
                <?php $__d = date('Y-m-d', strtotime($__today . ' -' . $__i . ' days')); ?>
                <span class="me-heat-cell<?= isset($__doneDates[$__d]) ? ' done' : '' ?>" title="<?= e($__d) ?>"></span>
            <?php endfor; ?>
        </div>
        <p class="me-heat-legend">۱۲ هفته اخیر — هر خانه یک روز است.</p>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
