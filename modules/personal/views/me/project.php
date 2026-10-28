<div class="me-card">
    <?php
    // هدر پیشرفت پروژه: ٪ + انجام‌شده/کل نیازها + برآورد ساعت.
    $__needsAll = (isset($needs) && is_array($needs)) ? $needs : [];
    $__doneN = count(array_filter($__needsAll, static fn ($n) => (int) ($n['done'] ?? 0) === 1));
    $__pct = count($__needsAll) > 0 ? (int) round(100 * $__doneN / count($__needsAll)) : 0;
    ?>
    <div class="me-goal-head">
        <h3 class="me-section-title"><i class="fa-solid fa-diagram-project"></i> <?= e($project['title'] ?? '') ?></h3>
        <span class="me-goal-pct"><?= $__pct ?>٪</span>
    </div>
    <p class="me-goal-date"><?= $__doneN ?> از <?= count($__needsAll) ?> نیاز تکمیل شده<?php if (!empty($project['hours_needed'])): ?> · برآورد <?= (int) $project['hours_needed'] ?> ساعت<?php endif; ?></p>
    <div class="me-progress-bar-bg"><div class="me-progress-bar-fill" style="width:<?= $__pct ?>%"></div></div>
    <form method="post" action="/me/projects/<?= (int) $project['id'] ?>" class="me-settings-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="me-settings-field">
            <label>عنوان پروژه</label>
            <input type="text" name="title" required value="<?= e($project['title']) ?>">
        </div>
        <div class="me-settings-field">
            <label>توضیح</label>
            <input type="text" name="description" value="<?= e($project['description']) ?>">
        </div>
        <div class="me-inline-form">
            <label class="me-field-inline">شروع
                <input type="date" name="start_date" value="<?= e($project['start_date']) ?>" class="me-input-sm">
            </label>
            <label class="me-field-inline">زمان مورد نیاز (ساعت)
                <input type="number" name="hours_needed" min="0" value="<?= (int) $project['hours_needed'] ?>" class="me-input-sm">
            </label>
            <button class="me-note-btn">ذخیره</button>
        </div>
    </form>
    <span class="me-btn-row">
        <form method="post" action="/me/projects/<?= (int) $project['id'] ?>/duplicate">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button class="me-mini-btn"><i class="fa-regular fa-copy"></i> کپی پروژه</button>
        </form>
        <form method="post" action="/me/projects/<?= (int) $project['id'] ?>/delete" onsubmit="return confirm('این پروژه با همه نیازهایش حذف شود؟')">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button class="me-mini-btn danger">حذف پروژه</button>
        </form>
    </span>
</div>

<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-list-check"></i> استپر نیازها</h3>
    <?php if (empty($__needsAll)): ?>
        <p class="me-empty">هنوز نیازی ثبت نشده است.</p>
    <?php else: ?>
        <?php $__seenCurrent = false; ?>
        <div class="me-stepper">
        <?php foreach ($__needsAll as $__n): ?>
            <?php $__st = (int) ($__n['done'] ?? 0) === 1 ? 'done' : (!$__seenCurrent ? 'current' : 'upcoming'); if ($__st === 'current') { $__seenCurrent = true; } ?>
            <div class="me-step me-step-<?= $__st ?>">
                <span class="me-step-dot"><i class="fa-solid <?= $__st === 'done' ? 'fa-check' : ($__st === 'current' ? 'fa-play' : 'fa-circle') ?>"></i></span>
                <span class="me-step-title<?= $__st === 'done' ? ' me-done' : '' ?>"><?= e($__n['title']) ?></span>
                <span class="me-step-actions">
                    <form method="post" action="/me/projects/needs/<?= (int) $__n['id'] ?>/toggle">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                        <input type="checkbox" <?= (int) ($__n['done'] ?? 0) === 1 ? 'checked' : '' ?> onchange="this.form.submit()" class="me-check">
                    </form>
                    <form method="post" action="/me/projects/needs/<?= (int) $__n['id'] ?>/delete" onsubmit="return confirm('این نیاز حذف شود؟')">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                        <button class="me-icon-btn" title="حذف"><i class="fa-regular fa-trash-can"></i></button>
                    </form>
                </span>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/me/projects/<?= (int) $project['id'] ?>/needs" class="me-inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="text" name="title" required placeholder="نیاز تازه..." class="me-note-input">
        <button class="me-note-btn">افزودن نیاز</button>
    </form>
</div>

<?php if (isset($projectTasks) && is_array($projectTasks)): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-list-check"></i> کارهای پروژه</h3>
    <?php if ($projectTasks === []): ?>
        <p class="me-empty">کاری به این پروژه لینک نشده است.</p>
    <?php else: ?>
        <div class="me-search-list">
        <?php foreach ($projectTasks as $__t): ?>
            <div class="me-search-row">
                <span class="me-search-title<?= !empty($__t['done']) ? ' me-done' : '' ?>"><?= e($__t['title'] ?? '') ?></span>
                <?php if (!empty($__t['subtasks']) && is_array($__t['subtasks'])): ?><span class="me-badge"><?= count($__t['subtasks']) ?> زیرکار</span><?php endif; ?>
                <?php if (!empty($__t['duration'])): ?><span class="me-search-date"><?= (int) $__t['duration'] ?> دقیقه</span><?php endif; ?>
                <?php if (!empty($__t['date'])): ?><span class="me-search-date"><?= e($__t['date']) ?></span><?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if (isset($linkedNotes) && is_array($linkedNotes) && $linkedNotes !== []): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-regular fa-note-sticky"></i> یادداشت‌های لینک‌شده</h3>
    <div class="me-search-list">
    <?php foreach ($linkedNotes as $__n): ?>
        <p class="me-note-row"><a href="/me/notes/<?= (int) ($__n['id'] ?? 0) ?>"><?= e($__n['title'] ?? '') ?></a></p>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="me-card">
    <?= $shareBox ?>
</div>
