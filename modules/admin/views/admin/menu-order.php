<div class="space-y-6">
  <div class="glass-panel rounded-2xl p-6">
    <h1 class="text-xl font-black text-slate-900 dark:text-white">ترتیب منو</h1>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
      با دکمه‌های بالا/پایین، ترتیب آیتم‌ها را داخل هر گروه عوض کنید. داشبورد همیشه نخستین گزینه سایدبار می‌ماند و اینجا فهرست نمی‌شود.
    </p>
  </div>

  <?php foreach ($groups as $slug => $g): ?>
  <div class="glass-panel rounded-2xl p-5">
    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4"><?= e($g['title']) ?> <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">— <?= e($slug) ?></span></h3>
    <div class="space-y-2">
      <?php $n = count($g['items']); ?>
      <?php foreach ($g['items'] as $i => $item): ?>
      <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80">
        <div class="text-xs">
          <span class="font-bold text-slate-900 dark:text-white"><?= e($item['label']) ?></span>
          <span class="text-slate-500 dark:text-slate-400 block font-mono text-[10px]"><?= e($item['path']) ?></span>
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
