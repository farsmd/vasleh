<?php
// فهرست اهداف (OKR): درصد از $goalProgress (نگاشت id=>درصد) اگر پاس شده،
// وگرنه از مایل‌استون‌های همراه هدف، وگرنه ۰ — هیچ عددی حدس زده نمی‌شود.
// داده: $goals (id, title, target_date, milestones?).
$__goals = (isset($goals) && is_array($goals)) ? $goals : [];
$__prog = (isset($goalProgress) && is_array($goalProgress)) ? $goalProgress : [];
?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-bullseye"></i> هدف تازه</h3>
    <form method="post" action="/me/goals" class="me-inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="text" name="title" required placeholder="عنوان هدف..." class="me-note-input">
        <label class="me-field-inline">تاریخ هدف
            <input type="date" name="target_date" class="me-input-sm">
        </label>
        <button class="me-note-btn">افزودن هدف</button>
    </form>
</div>
<?php if ($__goals === []): ?>
    <div class="me-card"><p class="me-empty">هنوز هدفی ثبت نشده است.</p></div>
<?php else: ?>
    <?php foreach ($__goals as $__g): ?>
    <?php
        $__pct = 0;
        if (isset($__prog[(int) ($__g['id'] ?? 0)])) { $__pct = (int) $__prog[(int) $__g['id']]; }
        elseif (!empty($__g['milestones']) && is_array($__g['milestones'])) {
            $__done = count(array_filter($__g['milestones'], static fn ($m) => !empty($m['done'])));
            $__pct = count($__g['milestones']) > 0 ? (int) round(100 * $__done / count($__g['milestones'])) : 0;
        } elseif (isset($__g['progress'])) { $__pct = (int) $__g['progress']; }
        $__pct = max(0, min(100, $__pct));
    ?>
    <div class="me-card me-goal-card">
        <div class="me-goal-head">
            <a href="/me/goals/<?= (int) ($__g['id'] ?? 0) ?>" class="me-goal-title"><?= e($__g['title'] ?? '') ?></a>
            <span class="me-goal-pct"><?= $__pct ?>٪</span>
        </div>
        <?php if (!empty($__g['target_date'])): ?><p class="me-goal-date">تاریخ هدف: <?= e($__g['target_date']) ?></p><?php endif; ?>
        <div class="me-progress-bar-bg"><div class="me-progress-bar-fill" style="width:<?= $__pct ?>%"></div></div>
        <form method="post" action="/me/goals/<?= (int) ($__g['id'] ?? 0) ?>/delete" onsubmit="return confirm('این هدف با مایل‌استون‌هایش حذف شود؟')" class="me-btn-row">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button class="me-mini-btn danger">حذف هدف</button>
        </form>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
