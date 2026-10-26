<div class="card">
  <h1>وضعیت دیتابیس SQLite</h1>
  <p>مسیر فایل: <code><?= e($info['file']) ?></code></p>
  <p>حجم: <strong><?= e(number_format($info['size'])) ?></strong> بایت — نسخه SQLite: <strong><?= e($info['version']) ?></strong></p>
  <?php if ($info['tables'] === []): ?>
    <p>هنوز جدولی ساخته نشده است.</p>
  <?php else: ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php foreach ($info['tables'] as $t): ?>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 flex flex-col gap-3">
      <div class="min-w-0">
        <div class="text-xs font-bold text-slate-900 dark:text-white truncate"><code dir="ltr"><?= e($t['name']) ?></code></div>
      </div>
      <div>
        <div class="text-base font-black text-slate-900 dark:text-white"><?= e($t['rows']) ?></div>
        <div class="text-[11px] text-slate-500 dark:text-slate-400">ردیف</div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
