<div class="space-y-6">
  <div class="glass-panel rounded-2xl p-6">
    <h1 class="text-xl font-black text-slate-900 dark:text-white">ترتیب منو</h1>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
      گروه‌ها را از دستگیره‌شان بکشید و جابه‌جا کنید؛ آیتم‌ها هم فقط داخل گروه خودشان کشیده می‌شوند (گروه یعنی ماژول صاحب آیتم). با رها کردن، ترتیب همان لحظه ذخیره می‌شود. دکمه‌های بالا/پایین هم برای جابه‌جایی دقیق باقی‌اند. داشبورد همیشه نخستین گزینه سایدبار می‌ماند و اینجا فهرست نمی‌شود.
    </p>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="flash"><?= e($flash) ?></div>
  <?php endif; ?>

  <div id="menuGroups" class="space-y-6">
    <?php foreach ($groups as $slug => $g): ?>
    <div class="glass-panel rounded-2xl p-5 menu-group-card" data-group="<?= e($slug) ?>">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base font-bold text-slate-900 dark:text-white"><?= e($g['title']) ?> <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">— <?= e($slug) ?></span></h3>
        <span class="drag-handle cursor-grab active:cursor-grabbing px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-500 dark:text-slate-400 text-xs font-bold select-none" draggable="true" data-drag="group" data-group="<?= e($slug) ?>" title="بکشید تا گروه جابه‌جا شود">⠿ بکشید</span>
      </div>
      <div class="space-y-2 menu-items" data-group="<?= e($slug) ?>">
        <?php $n = count($g['items']); ?>
        <?php foreach ($g['items'] as $i => $item): ?>
        <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80 menu-item-row" data-path="<?= e($item['path']) ?>" data-group="<?= e($slug) ?>">
          <div class="flex items-center gap-2 text-xs min-w-0">
            <span class="drag-handle cursor-grab active:cursor-grabbing px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-bold select-none shrink-0" draggable="true" data-drag="item" data-path="<?= e($item['path']) ?>" data-group="<?= e($slug) ?>" title="بکشید تا آیتم جابه‌جا شود">⠿</span>
            <div class="min-w-0">
              <span class="font-bold text-slate-900 dark:text-white block truncate"><?= e($item['label']) ?></span>
              <span class="text-slate-500 dark:text-slate-400 block font-mono text-[10px] truncate"><?= e($item['path']) ?></span>
            </div>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <form method="post" action="/admin/menu/move">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="path" value="<?= e($item['path']) ?>">
              <input type="hidden" name="dir" value="up">
              <button type="submit" <?= $i === 0 ? 'disabled' : '' ?> class="w-8 h-8 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-bold disabled:opacity-40">↑</button>
            </form>
            <form method="post" action="/admin/menu/move">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="path" value="<?= e($item['path']) ?>">
              <input type="hidden" name="dir" value="down">
              <button type="submit" <?= $i === $n - 1 ? 'disabled' : '' ?> class="w-8 h-8 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-bold disabled:opacity-40">↓</button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  <form id="menuOrderForm" method="post" action="/admin/menu/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  </form>
</div>

<script>
(function () {
  var form = document.getElementById('menuOrderForm');
  var groupsEl = document.getElementById('menuGroups');
  if (!form || !groupsEl) return;
  var dragged = null;
  function collectAndSubmit() {
    form.querySelectorAll('input[data-order-field]').forEach(function (el) { el.remove(); });
    groupsEl.querySelectorAll('.menu-group-card').forEach(function (card) {
      var g = document.createElement('input');
      g.type = 'hidden'; g.name = 'groups[]'; g.value = card.getAttribute('data-group');
      g.setAttribute('data-order-field', '1');
      form.appendChild(g);
    });
    groupsEl.querySelectorAll('.menu-item-row').forEach(function (row) {
      var p = document.createElement('input');
      p.type = 'hidden'; p.name = 'order[]'; p.value = row.getAttribute('data-path');
      p.setAttribute('data-order-field', '1');
      form.appendChild(p);
    });
    form.submit();
  }
  document.querySelectorAll('.drag-handle').forEach(function (h) {
    h.addEventListener('dragstart', function (ev) {
      dragged = { kind: h.getAttribute('data-drag'), group: h.getAttribute('data-group'), path: h.getAttribute('data-path') };
      try { ev.dataTransfer.setData('text/plain', 'menu-drag'); } catch (e) {}
      try { ev.dataTransfer.effectAllowed = 'move'; } catch (e) {}
    });
    h.addEventListener('dragend', function () { dragged = null; });
  });
  // رها کردن گروه روی گروه دیگر: جابه‌جایی کارت گروه‌ها.
  groupsEl.querySelectorAll('.menu-group-card').forEach(function (card) {
    card.addEventListener('dragover', function (ev) {
      if (dragged && dragged.kind === 'group') { ev.preventDefault(); }
    });
    card.addEventListener('drop', function (ev) {
      if (!dragged || dragged.kind !== 'group') return;
      ev.preventDefault();
      var src = groupsEl.querySelector('.menu-group-card[data-group="' + dragged.group + '"]');
      if (!src || src === card) return;
      var rect = card.getBoundingClientRect();
      var before = (ev.clientY - rect.top) < rect.height / 2;
      if (before) { groupsEl.insertBefore(src, card); } else { groupsEl.insertBefore(src, card.nextSibling); }
      collectAndSubmit();
    });
  });
  // رها کردن آیتم فقط داخل همان گروه.
  document.querySelectorAll('.menu-item-row').forEach(function (row) {
    row.addEventListener('dragover', function (ev) {
      if (dragged && dragged.kind === 'item' && dragged.group === row.getAttribute('data-group')) { ev.preventDefault(); }
    });
    row.addEventListener('drop', function (ev) {
      if (!dragged || dragged.kind !== 'item') return;
      if (dragged.group !== row.getAttribute('data-group')) return;
      ev.preventDefault();
      var list = row.parentNode;
      var src = list.querySelector('.menu-item-row[data-path="' + dragged.path + '"]');
      if (!src || src === row) return;
      var rect = row.getBoundingClientRect();
      var before = (ev.clientY - rect.top) < rect.height / 2;
      if (before) { list.insertBefore(src, row); } else { list.insertBefore(src, row.nextSibling); }
      collectAndSubmit();
    });
  });
})();
</script>
