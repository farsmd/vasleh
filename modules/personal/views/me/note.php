<div class="me-card">
    <form method="post" action="/me/notes/<?= (int) $note['id'] ?>" class="me-settings-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="me-settings-field">
            <label>عنوان</label>
            <input type="text" name="title" required value="<?= e($note['title']) ?>">
        </div>
        <div class="me-settings-field">
            <label>متن</label>
            <textarea name="body" rows="14"><?= e($note['body']) ?></textarea>
        </div>
        <div class="me-settings-field">
            <label>برچسب‌ها (با کاما جدا شوند)</label>
            <input type="text" name="tags" value="<?= e(implode(', ', array_map('strval', (array) ($note['tags'] ?? [])))) ?>" placeholder="مثلاً ایده، مطالعه">
        </div>
        <?php if (!empty($note['tags']) && is_array($note['tags'])): ?>
        <div class="me-chip-row">
            <?php foreach ($note['tags'] as $__tg): ?><span class="me-tag-chip">#<?= e((string) $__tg) ?></span><?php endforeach; ?>
        </div>
        <?php endif; ?>
        <button class="me-note-btn">ذخیره</button>
    </form>
    <form method="post" action="/me/notes/<?= (int) $note['id'] ?>/delete" onsubmit="return confirm('این یادداشت حذف شود؟')" class="me-btn-row">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="me-mini-btn danger">حذف یادداشت</button>
    </form>
</div>

<?php if (isset($noteLinks) && is_array($noteLinks)): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-link"></i> آیتم‌های لینک‌شده</h3>
    <?php if ($noteLinks === []): ?>
        <p class="me-empty">هنوز آیتمی لینک نشده است.</p>
    <?php else: ?>
        <div class="me-search-list">
        <?php foreach ($noteLinks as $__l): ?>
            <?php $__lt = (string) ($__l['item_type'] ?? ''); $__href = $__lt === 'project' ? '/me/projects/' . (int) ($__l['item_id'] ?? 0) : '/me'; ?>
            <div class="me-search-row">
                <span class="me-search-title"><?= e($__lt === 'todo' ? 'کار' : ($__lt === 'event' ? 'رویداد' : 'پروژه')) ?>: <a href="<?= e($__href) ?>"><?= e($__l['title'] ?? ('#' . (int) ($__l['item_id'] ?? 0))) ?></a></span>
                <form method="post" action="/me/notes/links/<?= (int) ($__l['id'] ?? 0) ?>/delete" onsubmit="return confirm('این لینک حذف شود؟')">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <button class="me-icon-btn" title="حذف لینک"><i class="fa-regular fa-trash-can"></i></button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/me/notes/<?= (int) $note['id'] ?>/links" class="me-inline-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <select name="item_type" class="me-input-sm">
            <option value="todo">کار</option>
            <option value="event">رویداد</option>
            <option value="project">پروژه</option>
        </select>
        <input type="number" name="item_id" min="1" required placeholder="شناسه آیتم" class="me-input-sm">
        <button class="me-note-btn">افزودن لینک</button>
    </form>
</div>
<?php endif; ?>

<div class="me-card">
    <?= $shareBox ?>
</div>
