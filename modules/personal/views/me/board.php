<?php
// بورد: ستون‌ها = دسته‌های کاربر؛ کارت‌ها همان تسک‌ها با همان POSTهای
// تیک/حذف (‎/me/task/{kind}/{id}/toggle|delete‎ که JSON برمی‌گردانند، پس
// با fetch فرستاده و صفحه تازه می‌شود). بدون درگ‌اند‌دراپ.
// داده: $tasks + $categories یا $settings['categories'].
$__tasks = (isset($tasks) && is_array($tasks)) ? $tasks : [];
$__cats = [];
if (isset($categories) && is_array($categories)) { $__cats = $categories; }
elseif (isset($settings['categories']) && is_array($settings['categories'])) { $__cats = $settings['categories']; }
$__labels = [];
foreach ($__cats as $__c) { if (is_array($__c) && isset($__c['key'])) { $__labels[(string) $__c['key']] = (string) $__c['label']; } }
$__cols = $__labels;
$__cols['__other'] = 'سایر';
?>
<div class="me-board">
<?php foreach ($__cols as $__key => $__label): ?>
    <?php $__colTasks = array_values(array_filter($__tasks, static function ($t) use ($__key, $__labels) {
        $__cat = (string) ($t['category'] ?? '');
        return $__key === '__other' ? !isset($__labels[$__cat]) : $__cat === $__key;
    })); ?>
    <?php if ($__key === '__other' && $__colTasks === []) { continue; } ?>
    <section class="me-card me-board-col">
        <div class="me-section-head">
            <h3 class="me-section-title"><?= e($__label) ?></h3>
            <span class="me-section-action"><?= count($__colTasks) ?> کار</span>
        </div>
        <div class="me-board-cards">
        <?php if ($__colTasks === []): ?>
            <p class="me-empty">کاری نیست.</p>
        <?php endif; ?>
        <?php foreach ($__colTasks as $__t): ?>
            <div class="me-board-card<?= !empty($__t['done']) ? ' is-done' : '' ?>">
                <span class="me-board-title<?= !empty($__t['done']) ? ' me-done' : '' ?>"><?= e($__t['title'] ?? '') ?></span>
                <span class="me-board-meta">
                    <?php if (!empty($__t['date'])): ?><span><?= e($__t['date']) ?></span><?php endif; ?>
                    <?php if (!empty($__t['priority'])): ?><span class="me-badge me-badge-prio-<?= e($__t['priority']) ?>"><?= e($__t['priority'] === 'high' ? 'فوری' : ($__t['priority'] === 'low' ? 'عادی' : 'متوسط')) ?></span><?php endif; ?>
                    <?php if (!empty($__t['duration'])): ?><span><?= (int) $__t['duration'] ?> دقیقه</span><?php endif; ?>
                </span>
                <span class="me-board-actions">
                    <button type="button" class="me-mini-btn" onclick="meBoardPost('/me/task/<?= e($__t['kind'] ?? 'todo') ?>/<?= (int) $__t['id'] ?>/toggle')"><i class="fa-solid fa-check"></i> <?= !empty($__t['done']) ? 'بازگردانی' : 'انجام' ?></button>
                    <button type="button" class="me-mini-btn danger" onclick="if(confirm('این تسک حذف شود؟'))meBoardPost('/me/task/<?= e($__t['kind'] ?? 'todo') ?>/<?= (int) $__t['id'] ?>/delete')"><i class="fa-regular fa-trash-can"></i></button>
                </span>
            </div>
        <?php endforeach; ?>
        </div>
    </section>
<?php endforeach; ?>
</div>
<script>
function meBoardPost(url) {
  var fd = new FormData();
  fd.append('_csrf', <?= json_encode($csrf ?? '') ?>);
  fetch(url, { method: 'POST', body: fd }).then(function () { window.location.reload(); }).catch(function () { window.location.reload(); });
}
</script>
