<?php
// جست‌وجوی سراسری /me: نتایج در گروه‌های تودو/رویداد/نوت/پروژه.
// داده: $q (عبارت جست‌وجو) + $results (آرایه گروه‌بندی‌شده یا فهرست تخت).
// هر گروهی که پاس نشده باشد، نمایش داده نمی‌شود؛ عددی ساخته نمی‌شود.
$__q = (string) ($q ?? '');
$__groups = [];
if (isset($results) && is_array($results)) {
    if (isset($results['todos']) || isset($results['events']) || isset($results['notes']) || isset($results['projects'])) {
        $__groups = $results;
    } else {
        foreach ($results as $__it) {
            if (!is_array($__it)) { continue; }
            $__k = (string) ($__it['kind'] ?? $__it['type'] ?? '');
            if ($__k === 'todo') { $__groups['todos'][] = $__it; }
            elseif ($__k === 'event') { $__groups['events'][] = $__it; }
            elseif ($__k === 'note') { $__groups['notes'][] = $__it; }
            elseif ($__k === 'project') { $__groups['projects'][] = $__it; }
        }
    }
}
$__total = 0;
foreach ($__groups as $__g) { if (is_array($__g)) { $__total += count($__g); } }
$__labels = ['todos' => 'کارها', 'events' => 'رویدادها', 'notes' => 'یادداشت‌ها', 'projects' => 'پروژه‌ها'];
$__icons = ['todos' => 'fa-list-check', 'events' => 'fa-regular fa-clock', 'notes' => 'fa-regular fa-note-sticky', 'projects' => 'fa-diagram-project'];
?>
<div class="me-card">
    <form method="get" action="/me/search" class="me-search-form">
        <input type="text" name="q" value="<?= e($__q) ?>" placeholder="جست‌وجو در کارها، رویدادها، یادداشت‌ها و پروژه‌ها..." class="me-search-input">
        <button class="me-note-btn"><i class="fa-solid fa-magnifying-glass"></i> جست‌وجو</button>
    </form>
    <?php if ($__q !== ''): ?>
        <p class="me-search-meta"><?= $__total ?> نتیجه برای «<?= e($__q) ?>»</p>
    <?php endif; ?>
</div>
<?php if ($__q !== '' && $__total === 0): ?>
    <div class="me-card"><p class="me-empty">چیزی پیدا نشد.</p></div>
<?php endif; ?>
<?php foreach (['todos', 'events', 'notes', 'projects'] as $__key): ?>
    <?php if (!empty($__groups[$__key]) && is_array($__groups[$__key])): ?>
    <div class="me-card">
        <div class="me-section-head">
            <h3 class="me-section-title"><i class="fa-solid <?= $__icons[$__key] ?>"></i> <?= $__labels[$__key] ?></h3>
            <span class="me-section-action"><?= count($__groups[$__key]) ?> مورد</span>
        </div>
        <div class="me-search-list">
        <?php foreach ($__groups[$__key] as $__it): ?>
            <?php if ($__key === 'notes'): ?>
                <p class="me-note-row"><a href="/me/notes/<?= (int) ($__it['id'] ?? 0) ?>"><?= e($__it['title'] ?? '') ?></a></p>
            <?php elseif ($__key === 'projects'): ?>
                <p class="me-note-row"><a href="/me/projects/<?= (int) ($__it['id'] ?? 0) ?>"><?= e($__it['title'] ?? '') ?></a></p>
            <?php else: ?>
                <div class="me-search-row">
                    <span class="me-search-title<?= !empty($__it['done']) ? ' me-done' : '' ?>"><?= e($__it['title'] ?? '') ?></span>
                    <?php if (!empty($__it['date'])): ?><span class="me-search-date"><?= e($__it['date']) ?></span><?php endif; ?>
                    <?php if (!empty($__it['priority'])): ?><span class="me-badge me-badge-prio-<?= e($__it['priority']) ?>"><?= e($__it['priority'] === 'high' ? 'فوری' : ($__it['priority'] === 'low' ? 'عادی' : 'متوسط')) ?></span><?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
<?php endforeach; ?>
