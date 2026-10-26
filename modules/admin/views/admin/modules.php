<div class="space-y-6">
  <div class="glass-panel rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-xl font-black text-slate-900 dark:text-white">مدیریت ماژول‌ها</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">روشن/خاموش، ویرایش و حذف — دقیقاً با کارت‌های ویجت داشبورد.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3 shrink-0">
      <a href="/admin/modules/create" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">+ ساخت ماژول تازه</a>
      <a href="/admin/modules/install" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">نصب ماژول از ZIP</a>
    </div>
  </div>

  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php foreach ($modules as $slug => $m): ?>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 flex flex-col gap-3">
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
          <div class="text-xs font-bold text-slate-900 dark:text-white truncate"><?= e($m['title']) ?><?= !empty($m['system']) ? ' <span class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">(سیستمی)</span>' : '' ?></div>
          <div class="text-[10px] text-slate-500 dark:text-slate-400">نسخه <?= e($m['version']) ?> • <code><?= e($slug) ?></code></div>
        </div>
        <?php if (!empty($m['error'])): ?>
          <span class="text-[10px] bg-rose-500/10 text-rose-500 dark:text-rose-400 px-2 py-0.5 rounded font-bold shrink-0">خطا</span>
        <?php elseif ($m['enabled']): ?>
          <span class="text-[10px] bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 px-2 py-0.5 rounded font-bold shrink-0">فعال</span>
        <?php else: ?>
          <span class="text-[10px] bg-slate-500/10 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded font-bold shrink-0">غیرفعال</span>
        <?php endif; ?>
      </div>
      <?php if (!empty($m['description'])): ?>
        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed"><?= e($m['description']) ?></p>
      <?php endif; ?>
      <?php if (!empty($m['error'])): ?>
        <p class="text-[11px] text-rose-500 dark:text-rose-400 leading-relaxed"><?= e($m['error']) ?></p>
      <?php endif; ?>
      <div class="flex items-center justify-between gap-2 pt-1 mt-auto">
        <div class="flex items-center gap-2">
          <a href="/admin/modules/edit/<?= e($slug) ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:border-slate-400 dark:hover:border-slate-500">ویرایش</a>
          <?php if (empty($m['system']) && $slug !== 'admin'): ?>
          <form method="post" action="/admin/modules/delete/<?= e($slug) ?>" onsubmit="return confirm('ماژول «<?= e($m['title']) ?>» و همه فایل‌هایش حذف شود؟ جدول‌های دیتابیس باقی می‌مانند.')">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-500 dark:text-rose-400 text-[11px] font-bold hover:bg-rose-500/20">حذف</button>
          </form>
          <?php endif; ?>
        </div>
        <?php if (empty($m['system'])): ?>
        <form method="post" action="/admin/modules/toggle/<?= e($slug) ?>" class="flex items-center shrink-0">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button type="submit" title="<?= $m['enabled'] ? 'غیرفعال‌کردن' : 'فعال‌کردن' ?>" class="relative w-11 h-6 rounded-full transition-colors <?= $m['enabled'] ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' ?>">
            <span class="absolute top-0.5 <?= $m['enabled'] ? 'left-0.5' : 'right-0.5' ?> w-5 h-5 rounded-full bg-white shadow"></span>
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
