<div class="me-card">
    <?php
    // هدر پروژه: پیشرفت ترکیبی (نیازها + کارها)، بج وضعیت و شمارش روزهای مانده.
    $__needsAll = (isset($needs) && is_array($needs)) ? $needs : [];
    $__pr = (isset($progress) && is_array($progress)) ? $progress : ['total' => 0, 'done' => 0, 'pct' => 0, 'needs_total' => 0, 'needs_done' => 0, 'tasks_total' => 0, 'tasks_done' => 0];
    $__pct = (int) ($__pr['pct'] ?? 0);
    $__st = (string) ($project['status'] ?? 'active');
    $__statuses = (isset($statuses) && is_array($statuses)) ? $statuses : ['active' => 'فعال'];
    $__dl = $daysLeft ?? null;
    $__fa = static fn ($n) => strtr((string) $n, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    ?>
    <div class="me-goal-head">
        <h3 class="me-section-title"><i class="fa-solid fa-diagram-project"></i> <?= e($project['title'] ?? '') ?></h3>
        <span class="me-goal-pct"><?= $__fa($__pct) ?>٪</span>
    </div>
    <div class="me-chip-row" style="margin:8px 0">
        <span class="me-badge"><i class="fa-solid fa-circle-dot"></i> <?= e($__statuses[$__st] ?? $__st) ?></span>
        <?php if ($__dl !== null): ?>
            <?php if ($__dl < 0): ?><span class="me-badge" style="background:#fee;color:#b00"><i class="fa-solid fa-triangle-exclamation"></i> <?= $__fa(-$__dl) ?> روز عقب‌افتاده</span>
            <?php elseif ($__dl === 0): ?><span class="me-badge"><i class="fa-solid fa-bell"></i> ددلاین امروز است</span>
            <?php else: ?><span class="me-badge"><i class="fa-regular fa-calendar"></i> <?= $__fa($__dl) ?> روز مانده</span><?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($project['end_date'])): ?><span class="me-tag-chip">پایان: <?= e(\Modules\Personal\Personal::faDate((string) $project['end_date'])) ?></span><?php endif; ?>
    </div>
    <p class="me-goal-date"><?= $__fa((int) ($__pr['done'] ?? 0)) ?> از <?= $__fa((int) ($__pr['total'] ?? 0)) ?> مورد (نیاز <?= $__fa((int) ($__pr['needs_done'] ?? 0)) ?>/<?= $__fa((int) ($__pr['needs_total'] ?? 0)) ?> · کار <?= $__fa((int) ($__pr['tasks_done'] ?? 0)) ?>/<?= $__fa((int) ($__pr['tasks_total'] ?? 0)) ?>)<?php if (!empty($project['hours_needed'])): ?> · برآورد <?= $__fa((int) $project['hours_needed']) ?> ساعت<?php endif; ?><?php if (!empty($actualHours)): ?> · واقعی <?= $__fa((string) $actualHours) ?> ساعت<?php endif; ?></p>
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
            <label class="me-field-inline">پایان (ددلاین)
                <input type="date" name="end_date" value="<?= e((string) ($project['end_date'] ?? '')) ?>" class="me-input-sm">
            </label>
            <label class="me-field-inline">وضعیت
                <select name="status" class="me-input-sm">
                    <?php foreach ($__statuses as $__sk => $__sl): ?><option value="<?= e($__sk) ?>"<?= $__sk === $__st ? ' selected' : '' ?>><?= e($__sl) ?></option><?php endforeach; ?>
                </select>
            </label>
            <label class="me-field-inline">زمان مورد نیاز (ساعت)
                <input type="number" name="hours_needed" min="0" value="<?= (int) $project['hours_needed'] ?>" class="me-input-sm">
            </label>
            <button class="me-note-btn">ذخیره</button>
        </div>
    </form>
    <span class="me-btn-row">
        <a href="/me/projects/<?= (int) $project['id'] ?>/report" class="me-mini-btn"><i class="fa-regular fa-file-lines"></i> گزارش وضعیت</a>
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

<?php $__ms = (isset($milestones) && is_array($milestones)) ? $milestones : []; ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-flag-checkered"></i> مایل‌استون‌ها</h3>
    <?php if ($__ms === []): ?>
        <p class="me-empty">هنوز نقطه عطفی ثبت نشده است.</p>
    <?php else: ?>
        <?php $__seenCur = false; ?>
        <div class="me-stepper">
        <?php foreach ($__ms as $__m): ?>
            <?php $__mst = (int) ($__m['done'] ?? 0) === 1 ? 'done' : (!$__seenCur ? 'current' : 'upcoming'); if ($__mst === 'current') { $__seenCur = true; } ?>
            <div class="me-step me-step-<?= $__mst ?>">
                <span class="me-step-dot"><i class="fa-solid <?= $__mst === 'done' ? 'fa-check' : ($__mst === 'current' ? 'fa-play' : 'fa-circle') ?>"></i></span>
                <span class="me-step-title<?= $__mst === 'done' ? ' me-done' : '' ?>"><?= e($__m['title'] ?? '') ?><?php if (!empty($__m['date'])): ?> <small class="me-search-date"><?= e(\Modules\Personal\Personal::faDate((string) $__m['date'])) ?></small><?php endif; ?></span>
                <span class="me-step-actions">
                    <form method="post" action="/me/projects/milestones/<?= (int) $__m['id'] ?>/toggle">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                        <input type="checkbox" <?= (int) ($__m['done'] ?? 0) === 1 ? 'checked' : '' ?> onchange="this.form.submit()" class="me-check">
                    </form>
                    <form method="post" action="/me/projects/milestones/<?= (int) $__m['id'] ?>/delete" onsubmit="return confirm('این مایل‌استون حذف شود؟')">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                        <button class="me-icon-btn" title="حذف"><i class="fa-regular fa-trash-can"></i></button>
                    </form>
                </span>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/me/projects/<?= (int) $project['id'] ?>/milestones" class="me-inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="text" name="title" required placeholder="مایل‌استون تازه..." class="me-note-input">
        <input type="date" name="date" class="me-input-sm">
        <button class="me-note-btn">افزودن</button>
    </form>
</div>

<?php if (isset($projectTasks) && is_array($projectTasks)): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-list-check"></i> کارهای پروژه (<?= count($projectTasks) ?>)</h3>
    <form method="post" action="/me/task" class="me-settings-form" onsubmit="return submitProjectTask(this)">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
        <input type="hidden" name="hour" value="all-day">
        <div class="me-settings-field">
            <label>کار تازه در این پروژه</label>
            <input type="text" name="title" required placeholder="موضوع کار...">
        </div>
        <div class="me-inline-form">
            <input type="text" name="note" placeholder="توضیح (اختیاری)" class="me-note-input" style="flex:2">
            <input type="date" name="date" class="me-input-sm">
            <input type="text" name="tags" placeholder="تگ‌ها با ویرگول" class="me-note-input" style="flex:1">
            <button class="me-note-btn">افزودن کار</button>
        </div>
    </form>
    <?php if ($projectTasks === []): ?>
        <p class="me-empty">کاری به این پروژه لینک نشده است.</p>
    <?php else: ?>
        <?php foreach ($projectTasks as $__t): ?>
        <?php $__tid = (int) ($__t['id'] ?? 0); $__tdone = !empty($__t['done']); ?>
        <div class="me-task-row">
            <input type="checkbox" <?= $__tdone ? 'checked' : '' ?> class="me-check" title="تغییر وضعیت"
                onchange="projectTaskToggle(<?= $__tid ?>, this.checked)">
            <div class="me-task-main">
                <div class="me-task-title<?= $__tdone ? ' done' : '' ?>"><?= e($__t['title'] ?? '') ?></div>
                <div class="me-task-meta">
                    <?php if (!empty($__t['due_date'])): ?><span><?= e(\Modules\Personal\Personal::faDate((string) $__t['due_date'])) ?></span><?php endif; ?>
                    <?php if (!empty($__t['duration'])): ?><span><?= (int) $__t['duration'] ?> دقیقه</span><?php endif; ?>
                    <?php if (!empty($__t['tags']) && is_array($__t['tags'])): foreach ($__t['tags'] as $__tg): ?><span class="me-tag-chip">#<?= e($__tg) ?></span><?php endforeach; endif; ?>
                    <?php if (!empty($__t['note']) || !empty($__t['subtasks'])): ?><button type="button" class="me-mini-btn" onclick="this.closest('.me-task-row').querySelector('.me-task-detail').classList.toggle('me-hidden')">جزئیات</button><?php endif; ?>
                </div>
                <div class="me-task-detail me-hidden" style="margin-top:8px">
                    <?php if (!empty($__t['note'])): ?><p class="me-muted" style="font-size:0.85rem"><?= nl2br(e((string) $__t['note'])) ?></p><?php endif; ?>
                    <?php if (!empty($__t['subtasks']) && is_array($__t['subtasks'])): ?>
                    <div class="me-sub-list">
                        <?php foreach ($__t['subtasks'] as $__st): ?>
                        <div class="me-sub-row">
                            <form method="post" action="/me/todos/subtasks/<?= (int) $__st['id'] ?>/toggle"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="checkbox"<?= !empty($__st['done']) ? ' checked' : '' ?> onchange="this.form.submit()" class="me-check"></form>
                            <span class="<?= !empty($__st['done']) ? 'me-done' : '' ?>"><?= e($__st['title'] ?? '') ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <form method="post" action="/me/todos/<?= $__tid ?>/subtasks" class="me-sub-add"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="text" name="title" required placeholder="زیرکار تازه..." class="me-note-input"><button class="me-mini-btn">افزودن</button></form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<script>
var __pjCsrf = <?= json_encode($csrf ?? '') ?>;
function projectTaskToggle(id, checked) {
    var fd = new FormData();
    fd.append('_csrf', __pjCsrf);
    fetch('/me/task/todo/' + id + '/toggle', { method: 'POST', body: fd })
        .then(function () { window.location.reload(); })
        .catch(function () { window.location.reload(); });
}
function submitProjectTask(form) {
    if (!form.date.value) {
        var d = new Date(); form.date.value = d.toISOString().slice(0, 10);
    }
    var fd = new FormData(form);
    fetch('/me/task', { method: 'POST', body: fd })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && res.ok) { window.location.reload(); }
            else { alert((res && res.error) || 'ذخیره ممکن نشد.'); }
        })
        .catch(function () { alert('خطای شبکه.'); });
    return false;
}
</script>
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
