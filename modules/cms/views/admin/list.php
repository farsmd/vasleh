<div class="card">
  <h1>مدیریت محتوا</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p><a class="btn green" href="/admin/cms/create">+ محتوای تازه</a></p>
  <?php if (empty($posts)): ?>
    <p>هنوز محتوایی نیست.</p>
  <?php else: ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php foreach ($posts as $post): ?>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 flex flex-col gap-3">
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
          <div class="text-xs font-bold text-slate-900 dark:text-white truncate"><?= e($post['title']) ?><?= !empty($post['bare']) ? ' <span class="text-[10px] text-slate-500 dark:text-slate-400 font-normal">(بدون قالب)</span>' : '' ?></div>
          <div class="text-[10px] text-slate-500 dark:text-slate-400"><?= ($post['type'] ?? 'post') === 'page' ? 'برگه' : 'نوشته' ?> • <code dir="ltr"><?= e($post['slug']) ?></code></div>
        </div>
        <?php if (($post['status'] ?? '') === 'published'): ?>
          <span class="text-[10px] bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 px-2 py-0.5 rounded font-bold shrink-0">منتشرشده</span>
        <?php else: ?>
          <span class="text-[10px] bg-slate-500/10 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded font-bold shrink-0">پیش‌نویس</span>
        <?php endif; ?>
      </div>
      <div class="text-[11px] text-slate-500 dark:text-slate-400">به‌روزرسانی: <?= e($post['updated_at']) ?></div>
      <div class="flex items-center gap-2 pt-1 mt-auto">
        <a href="/admin/cms/edit/<?= (int) $post['id'] ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:border-slate-400 dark:hover:border-slate-500">ویرایش</a>
        <?php if (($post['status'] ?? '') === 'published'): ?>
          <a target="_blank" href="<?= ($post['type'] ?? 'post') === 'page' ? '/page/' : '/blog/' ?><?= e(rawurlencode((string) $post['slug'])) ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:border-slate-400 dark:hover:border-slate-500">مشاهده</a>
        <?php endif; ?>
        <form method="post" action="/admin/cms/delete/<?= (int) $post['id'] ?>" onsubmit="return confirm('این محتوا حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-500 dark:text-rose-400 text-[11px] font-bold hover:bg-rose-500/20">حذف</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
