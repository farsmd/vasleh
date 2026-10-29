<?php
// فهرست پروژه‌ها: فیلتر وضعیت + بایگانی جدا + ددلاین و پیشرفت هر ردیف.
// داده: $projects (قابل‌نمایش)، $archived، $statusFilter، $statuses، $csrf
$__statuses = (isset($statuses) && is_array($statuses)) ? $statuses : ['active' => 'فعال'];
$__filter = (string) ($statusFilter ?? 'active');
$__csrf = (string) ($csrf ?? '');
$__fa = static fn ($n) => strtr((string) $n, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
$__row = static function ($p) use ($__fa, $__statuses, $__csrf) {
    $pid = (int) ($p['id'] ?? 0);
    $st = (string) ($p['status'] ?? 'active');
    $dl = $p['days_left'] ?? null;
    $pr = (isset($p['progress']) && is_array($p['progress'])) ? $p['progress'] : ['pct' => 0];
    $pct = (int) ($pr['pct'] ?? 0);
    ?>
    <div class="me-search-row">
        <a href="/me/projects/<?= $pid ?>" class="me-search-title"><?= e((string) ($p['title'] ?? '')) ?></a>
        <span class="me-badge"><?= e($__statuses[$st] ?? $st) ?></span>
        <?php if ($dl !== null): ?>
            <?php if ($dl < 0): ?><span class="me-badge" style="background:#fee;color:#b00"><?= $__fa(-$dl) ?> روز عقب</span>
            <?php elseif ($dl === 0): ?><span class="me-badge">ددلاین امروز</span>
            <?php elseif ($dl <= 7): ?><span class="me-badge"><?= $__fa($dl) ?> روز مانده</span><?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($p['end_date'])): ?><span class="me-search-date">پایان <?= \Modules\Personal\Personal::faDate((string) $p['end_date']) ?></span><?php endif; ?>
        <span class="me-search-date"><?= $__fa($pct) ?>٪</span>
        <form method="post" action="/me/projects/<?= $pid ?>/duplicate">
            <input type="hidden" name="_csrf" value="<?= e($__csrf) ?>">
            <button class="me-mini-btn" title="کپی پروژه"><i class="fa-regular fa-copy"></i> کپی</button>
        </form>
    </div>
    <?php
};
?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-diagram-project"></i> پروژه تازه</h3>
    <form method="post" action="/me/projects" class="me-settings-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="me-settings-field">
            <label>عنوان پروژه</label>
            <input type="text" name="title" required placeholder="عنوان پروژه">
        </div>
        <div class="me-settings-field">
            <label>توضیح (اختیاری)</label>
            <input type="text" name="description">
        </div>
        <div class="me-inline-form">
            <label class="me-field-inline">شروع (اختیاری)
                <input type="date" name="start_date" class="me-input-sm">
            </label>
            <label class="me-field-inline">پایان (اختیاری)
                <input type="date" name="end_date" class="me-input-sm">
            </label>
            <label class="me-field-inline">زمان مورد نیاز (ساعت)
                <input type="number" name="hours_needed" min="0" value="0" class="me-input-sm">
            </label>
            <button class="me-note-btn">ساختن پروژه</button>
        </div>
    </form>
</div>
<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-solid fa-diagram-project"></i> پروژه‌ها</h3>
    </div>
    <div class="me-chip-row" style="margin-bottom:10px">
        <a href="/me/projects?status=all" class="me-chip<?= $__filter === 'all' ? ' active' : '' ?>">همه</a>
        <?php foreach ($__statuses as $__sk => $__sl): ?>
            <?php if ($__sk === 'archived') continue; ?>
            <a href="/me/projects?status=<?= e($__sk) ?>" class="me-chip<?= $__filter === $__sk ? ' active' : '' ?>"><?= e($__sl) ?></a>
        <?php endforeach; ?>
    </div>
    <?php if (empty($projects)): ?>
        <p class="me-empty">پروژه‌ای با این وضعیت نیست.</p>
    <?php else: ?>
        <div class="me-search-list">
        <?php foreach ($projects as $p) { $__row($p); } ?>
        </div>
    <?php endif; ?>
</div>
<?php $__archived = (isset($archived) && is_array($archived)) ? $archived : []; ?>
<?php if ($__archived !== []): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-box-archive"></i> بایگانی‌شده‌ها (<?= $__fa(count($__archived)) ?>)</h3>
    <div class="me-search-list">
    <?php foreach ($__archived as $p) { $__row($p); } ?>
    </div>
</div>
<?php endif; ?>
