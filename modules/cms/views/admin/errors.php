<div class="card">
  <h1>صفحات خطا</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>برای هر کد وضعیت، یک صفحه سفارشی بگذارید تا بازدیدکننده به‌جای صفحه خام پیش‌فرض، صفحه خودتان را ببیند — با همان کد وضعیت (مثلاً ۴۰۴ واقعاً ۴۰۴ می‌ماند). صفحه غیرفعال یا حذف‌شده، به پیش‌فرض برمی‌گردد. خطاهای پنل ادمین و API شامل این صفحات نمی‌شوند.</p>
  <p><a class="btn green" href="/admin/cms/errors/edit/0">+ کد دلخواه تازه</a></p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
  <?php foreach ($pages as $p): ?>
  <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 flex flex-col gap-3">
    <div class="flex items-start justify-between gap-2">
      <div class="min-w-0">
        <div class="text-base font-black text-slate-900 dark:text-white"><?= (int) $p['code'] ?></div>
        <div class="text-xs font-bold text-slate-900 dark:text-white truncate"><?= e($p['title']) ?></div>
      </div>
      <?php if ($p['custom']): ?>
        <?php if ($p['active']): ?>
          <span class="text-[10px] bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 px-2 py-0.5 rounded font-bold shrink-0">سفارشی و فعال</span>
        <?php else: ?>
          <span class="text-[10px] bg-slate-500/10 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded font-bold shrink-0">سفارشی و غیرفعال</span>
        <?php endif; ?>
      <?php else: ?>
        <span class="text-[10px] bg-slate-500/10 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded font-bold shrink-0">پیش‌فرض</span>
      <?php endif; ?>
    </div>
    <div class="flex items-center gap-2 pt-1 mt-auto">
      <a href="/admin/cms/errors/edit/<?= (int) $p['code'] ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:border-slate-400 dark:hover:border-slate-500">ویرایش</a>
      <?php if ($p['custom']): ?>
      <form method="post" action="/admin/cms/errors/delete/<?= (int) $p['code'] ?>" onsubmit="return confirm('صفحه سفارشی کد <?= (int) $p['code'] ?> حذف و به پیش‌فرض برگردد؟')">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-500 dark:text-rose-400 text-[11px] font-bold hover:bg-rose-500/20">حذف</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
