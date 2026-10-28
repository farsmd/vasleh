<?php
// یادداشت‌ها — دو ستون در دسکتاپ: فهرست (جست‌وجو + چیپ برچسب + آیتم‌ها)
// و ستون ویرایشگر وقتی $selected (یا $note) پاس شده باشد.
// ویرایشگر به همان POST ذخیره نوت (/me/notes/{id}) می‌رود؛ ذخیره خودکار
// با debounce همان POST را با fetch می‌فرستد (best-effort، ذخیره دستی سالم می‌ماند).
// صفحه تکی نوت (note.php) جداگانه همچنان کار می‌کند.
$__notes = (isset($notes) && is_array($notes)) ? $notes : [];
$__sel = $selected ?? $note ?? null;
$__tags = [];
if (isset($tags) && is_array($tags)) { $__tags = array_map('strval', $tags); }
else {
    foreach ($__notes as $__n) {
        if (!empty($__n['tags']) && is_array($__n['tags'])) {
            foreach ($__n['tags'] as $__tg) { $__tags[(string) $__tg] = (string) $__tg; }
        }
    }
    $__tags = array_values($__tags);
}
$__links = (isset($noteLinks) && is_array($noteLinks)) ? $noteLinks : [];
?>
<div class="me-notes-layout">
    <div class="me-card me-notes-list-col">
        <h3 class="me-section-title"><i class="fa-regular fa-note-sticky"></i> یادداشت‌ها</h3>
        <form method="post" action="/me/notes" class="me-note-form">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="text" name="title" required placeholder="یادداشت تازه..." class="me-note-input">
            <button class="me-note-btn">ساختن</button>
        </form>
        <input type="text" id="meNotesSearch" oninput="meNotesFilter()" placeholder="جست‌وجو در یادداشت‌ها..." class="me-search-input">
        <?php if ($__tags !== []): ?>
        <div class="me-chip-row" id="meNotesTags">
            <button type="button" class="me-chip active" data-tag="" onclick="meNotesTag(this)">همه</button>
            <?php foreach ($__tags as $__tg): ?>
            <button type="button" class="me-chip" data-tag="<?= e($__tg) ?>" onclick="meNotesTag(this)">#<?= e($__tg) ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="me-notes-items">
        <?php if ($__notes === []): ?>
            <p class="me-empty">هنوز یادداشتی نیست.</p>
        <?php endif; ?>
        <?php foreach ($__notes as $__n): ?>
            <?php $__excerpt = trim((string) ($__n['body'] ?? '')); $__chars = preg_split('//u', $__excerpt, -1, PREG_SPLIT_NO_EMPTY); if (is_array($__chars) && count($__chars) > 120) { $__excerpt = implode('', array_slice($__chars, 0, 120)) . '…'; } ?>
            <a href="/me/notes/<?= (int) $__n['id'] ?>" class="me-note-item<?= ($__sel && (int) ($__sel['id'] ?? 0) === (int) $__n['id']) ? ' active' : '' ?>"
               data-title="<?= e(strtolower((string) ($__n['title'] ?? ''))) ?>" data-body="<?= e(strtolower((string) ($__n['body'] ?? ''))) ?>"
               data-tags="<?= e(implode(' ', array_map('strval', (array) ($__n['tags'] ?? [])))) ?>">
                <span class="me-note-item-title"><?= e($__n['title'] ?? '') ?></span>
                <?php if ($__excerpt !== ''): ?><span class="me-note-item-excerpt"><?= e($__excerpt) ?></span><?php endif; ?>
                <span class="me-note-item-meta">
                    <span><?= e($__n['updated_at'] ?? '') ?></span>
                    <?php if (!empty($__n['tags']) && is_array($__n['tags'])): foreach ($__n['tags'] as $__tg): ?><span class="me-tag-chip">#<?= e((string) $__tg) ?></span><?php endforeach; endif; ?>
                </span>
            </a>
        <?php endforeach; ?>
        </div>
    </div>
    <div class="me-card me-notes-editor-col">
        <?php if ($__sel === null): ?>
            <p class="me-empty">یک یادداشت را از فهرست باز کنید تا اینجا ویرایش شود.</p>
        <?php else: ?>
            <form method="post" action="/me/notes/<?= (int) $__sel['id'] ?>" id="meNoteEditForm" class="me-editor-form">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="text" name="title" required value="<?= e($__sel['title'] ?? '') ?>" class="me-editor-title" id="meNoteTitle">
                <div class="me-toolbar">
                    <button type="button" onclick="meTb('heading')" title="تیتر">H</button>
                    <button type="button" onclick="meTb('bold')" title="بولد"><i class="fa-solid fa-bold"></i></button>
                    <button type="button" onclick="meTb('italic')" title="ایتالیک"><i class="fa-solid fa-italic"></i></button>
                    <button type="button" onclick="meTb('check')" title="چک‌لیست"><i class="fa-solid fa-list-check"></i></button>
                    <button type="button" onclick="meTb('code')" title="کد"><i class="fa-solid fa-code"></i></button>
                    <button type="button" onclick="meTb('link')" title="لینک"><i class="fa-solid fa-link"></i></button>
                </div>
                <textarea name="body" rows="14" class="me-textarea" id="meNoteBody"><?= e($__sel['body'] ?? '') ?></textarea>
                <input type="text" name="tags" value="<?= e(implode(', ', array_map('strval', (array) ($__sel['tags'] ?? [])))) ?>" placeholder="برچسب‌ها با کاما جدا شوند..." class="me-note-input">
                <span class="me-btn-row">
                    <button class="me-note-btn">ذخیره</button>
                    <span id="meAutoStatus" class="me-auto-status"></span>
                </span>
            </form>
            <form method="post" action="/me/notes/<?= (int) $__sel['id'] ?>/delete" onsubmit="return confirm('این یادداشت حذف شود؟')" class="me-btn-row">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="me-mini-btn danger">حذف یادداشت</button>
            </form>
            <?php if ($__links !== []): ?>
            <div class="me-backlinks">
                <h4 class="me-section-title"><i class="fa-solid fa-link"></i> آیتم‌های لینک‌شده</h4>
                <?php foreach ($__links as $__l): ?>
                    <?php $__lt = (string) ($__l['item_type'] ?? ''); $__href = $__lt === 'project' ? '/me/projects/' . (int) ($__l['item_id'] ?? 0) : '/me'; ?>
                    <p class="me-note-row"><?= e($__lt === 'todo' ? 'کار' : ($__lt === 'event' ? 'رویداد' : 'پروژه')) ?>: <a href="<?= e($__href) ?>"><?= e($__l['title'] ?? ('#' . (int) ($__l['item_id'] ?? 0))) ?></a></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<script>
var meNotesTagSel = '';
function meNotesFilter() {
  var q = (document.getElementById('meNotesSearch').value || '').toLowerCase();
  document.querySelectorAll('.me-note-item').forEach(function (el) {
    var okQ = !q || (el.getAttribute('data-title') || '').indexOf(q) !== -1 || (el.getAttribute('data-body') || '').indexOf(q) !== -1;
    var tags = (el.getAttribute('data-tags') || '').split(' ');
    var okT = !meNotesTagSel || tags.indexOf(meNotesTagSel) !== -1;
    el.style.display = (okQ && okT) ? '' : 'none';
  });
}
function meNotesTag(btn) {
  meNotesTagSel = btn.getAttribute('data-tag') || '';
  document.querySelectorAll('#meNotesTags .me-chip').forEach(function (c) { c.classList.remove('active'); });
  btn.classList.add('active');
  meNotesFilter();
}
function meTb(kind) {
  var ta = document.getElementById('meNoteBody');
  if (!ta) { return; }
  var s = ta.selectionStart || 0, e = ta.selectionEnd || 0, v = ta.value;
  var sel = v.slice(s, e), ins = sel;
  if (kind === 'heading') { ins = '# ' + sel; }
  else if (kind === 'bold') { ins = '**' + sel + '**'; }
  else if (kind === 'italic') { ins = '*' + sel + '*'; }
  else if (kind === 'check') { ins = '- [ ] ' + sel; }
  else if (kind === 'code') { ins = '`' + sel + '`'; }
  else if (kind === 'link') { ins = '[' + (sel || 'متن') + '](نشانی)'; }
  ta.value = v.slice(0, s) + ins + v.slice(e);
  ta.focus();
  meNoteAuto();
}
var meAutoTimer = null;
function meNoteAuto() {
  var form = document.getElementById('meNoteEditForm');
  var st = document.getElementById('meAutoStatus');
  if (!form || !st) { return; }
  if (meAutoTimer) { clearTimeout(meAutoTimer); }
  st.textContent = 'در حال ذخیره...';
  meAutoTimer = setTimeout(function () {
    try {
      fetch(form.action, { method: 'POST', body: new FormData(form) }).then(function () {
        st.textContent = 'ذخیره شد';
      }).catch(function () { st.textContent = ''; });
    } catch (err) { st.textContent = ''; }
  }, 1500);
}
(function () {
  var t = document.getElementById('meNoteTitle'), b = document.getElementById('meNoteBody');
  if (t) { t.addEventListener('input', meNoteAuto); }
  if (b) { b.addEventListener('input', meNoteAuto); }
})();
</script>
