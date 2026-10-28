<?php
// جزئیات هدف: درصد پیشرفت + چک‌لیست مایل‌استون‌ها (تیک/حذف/افزودن).
// داده: $goal (id, title, target_date, progress?) + $milestones (id, title, done).
$__ms = (isset($milestones) && is_array($milestones)) ? $milestones : [];
$__done = count(array_filter($__ms, static fn ($m) => !empty($m['done'])));
$__pct = 0;
if (isset($goalProgress)) { $__pct = (int) $goalProgress; }
elseif (isset($goal['progress'])) { $__pct = (int) $goal['progress']; }
elseif ($__ms !== []) { $__pct = (int) round(100 * $__done / count($__ms)); }
$__pct = max(0, min(100, $__pct));
?>
<div class="me-card">
    <div class="me-goal-head">
        <h3 class="me-section-title"><i class="fa-solid fa-bullseye"></i> <?= e($goal['title'] ?? '') ?></h3>
        <span class="me-goal-pct"><?= $__pct ?>٪</span>
    </div>
    <?php if (!empty($goal['target_date'])): ?><p class="me-goal-date">تاریخ هدف: <?= e($goal['target_date']) ?></p><?php endif; ?>
    <div class="me-progress-bar-bg"><div class="me-progress-bar-fill" style="width:<?= $__pct ?>%"></div></div>
    <p class="me-goal-date"><?= $__done ?> از <?= count($__ms) ?> مایل‌استون تکمیل شده</p>
    <form method="post" action="/me/goals/<?= (int) ($goal['id'] ?? 0) ?>/delete" onsubmit="return confirm('این هدف با مایل‌استون‌هایش حذف شود؟')" class="me-btn-row">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="me-mini-btn danger">حذف هدف</button>
    </form>
</div>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-list-check"></i> مایل‌استون‌ها</h3>
    <?php if ($__ms === []): ?>
        <p class="me-empty">هنوز مایل‌استونی نیست.</p>
    <?php else: ?>
        <div class="me-search-list">
        <?php foreach ($__ms as $__m): ?>
            <div class="me-search-row">
                <form method="post" action="/me/goals/milestones/<?= (int) $__m['id'] ?>/toggle" class="me-habit-check">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="checkbox" <?= !empty($__m['done']) ? 'checked' : '' ?> onchange="this.form.submit()" class="me-check">
                </form>
                <span class="me-search-title<?= !empty($__m['done']) ? ' me-done' : '' ?>"><?= e($__m['title'] ?? '') ?></span>
                <form method="post" action="/me/goals/milestones/<?= (int) $__m['id'] ?>/delete" onsubmit="return confirm('این مایل‌استون حذف شود؟')">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <button class="me-icon-btn" title="حذف"><i class="fa-regular fa-trash-can"></i></button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/me/goals/<?= (int) ($goal['id'] ?? 0) ?>/milestones" class="me-inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="text" name="title" required placeholder="مایل‌استون تازه..." class="me-note-input">
        <button class="me-note-btn">افزودن</button>
    </form>
</div>
<?php $__gt = (isset($goalTasks) && is_array($goalTasks)) ? $goalTasks : []; ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-link"></i> تسک‌های لینک‌شده</h3>
    <?php if ($__gt === []): ?>
        <p class="me-empty">هنوز تسکی به این هدف لینک نشده است.</p>
    <?php else: ?>
        <div class="me-search-list">
        <?php foreach ($__gt as $__t): ?>
            <div class="me-search-row">
                <span class="me-habit-check">
                    <input type="checkbox" <?= !empty($__t['done']) ? 'checked' : '' ?> onchange="meGoalTaskToggle('/me/task/todo/<?= (int) $__t['id'] ?>/toggle')" class="me-check">
                </span>
                <span class="me-search-title<?= !empty($__t['done']) ? ' me-done' : '' ?>"><?= e($__t['title'] ?? '') ?></span>
                <form method="post" action="/me/goals/tasks/<?= (int) ($goal['id'] ?? 0) ?>/delete" class="me-btn-row">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="goal_id" value="<?= (int) ($goal['id'] ?? 0) ?>">
                    <input type="hidden" name="todo_id" value="<?= (int) $__t['id'] ?>">
                    <button class="me-icon-btn" title="قطع لینک"><i class="fa-solid fa-link-slash"></i></button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/me/goals/<?= (int) ($goal['id'] ?? 0) ?>/tasks" class="me-inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="number" name="todo_id" required min="1" placeholder="شناسهٔ تسک..." class="me-note-input">
        <button class="me-note-btn">لینک تسک</button>
    </form>
</div>
<script>
function meGoalTaskToggle(url) {
  var fd = new FormData();
  fd.append('_csrf', <?= json_encode($csrf ?? '') ?>);
  fetch(url, { method: 'POST', body: fd }).then(function () { window.location.reload(); }).catch(function () { window.location.reload(); });
}
</script>
