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
            <label class="me-field-inline">زمان مورد نیاز (ساعت)
                <input type="number" name="hours_needed" min="0" value="0" class="me-input-sm">
            </label>
            <button class="me-note-btn">ساختن پروژه</button>
        </div>
    </form>
</div>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-diagram-project"></i> پروژه‌ها</h3>
    <?php if (empty($projects)): ?>
        <p class="me-empty">هنوز پروژه‌ای نیست.</p>
    <?php else: ?>
        <div class="me-search-list">
        <?php foreach ($projects as $p): ?>
            <div class="me-search-row">
                <a href="/me/projects/<?= (int) $p['id'] ?>" class="me-search-title"><?= e($p['title']) ?></a>
                <?php if ($p['start_date'] !== ''): ?><span class="me-search-date">شروع <?= \Modules\Personal\Personal::bothDates($p['start_date']) ?></span><?php endif; ?>
                <?php if ((int) $p['hours_needed'] > 0): ?><span class="me-search-date"><?= (int) $p['hours_needed'] ?> ساعت مورد نیاز</span><?php endif; ?>
                <form method="post" action="/me/projects/<?= (int) $p['id'] ?>/duplicate">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <button class="me-mini-btn" title="کپی پروژه"><i class="fa-regular fa-copy"></i> کپی</button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
