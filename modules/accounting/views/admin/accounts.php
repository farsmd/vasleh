<div class="card">
  <h1>حساب‌های پولی</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/accounting/accounts/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
      <label>عنوان حساب
        <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
      </label>
      <label>نوع
        <select name="type">
          <?php foreach ($types as $k => $label): ?>
            <option value="<?= $k ?>" <?= ($edit['type'] ?? 'bank') === $k ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>موجودی اول دوره
        <input type="number" name="opening_balance" value="<?= (int) ($edit['opening_balance'] ?? 0) ?>">
      </label>
      <label>یادداشت (مثلاً شماره حساب)
        <input type="text" name="note" value="<?= e($edit['note'] ?? '') ?>">
      </label>
    </div>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="active" value="1" <?= !isset($edit) || (int) ($edit['active'] ?? 1) === 1 ? 'checked' : '' ?>> فعال
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت حساب' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/accounting/accounts">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<?php if (empty($accounts)): ?>
  <p>هنوز حسابی نیست.</p>
<?php else: ?>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
  <?php foreach ($accounts as $a): ?>
  <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 flex flex-col gap-3">
    <div class="flex items-start justify-between gap-2">
      <div class="min-w-0">
        <div class="text-xs font-bold text-slate-900 dark:text-white truncate"><?= e($a['title']) ?></div>
        <div class="text-[10px] text-slate-500 dark:text-slate-400"><?= e($types[$a['type']] ?? $a['type']) ?><?php if (!empty($a['note'])): ?> • <?= e($a['note']) ?><?php endif; ?></div>
      </div>
      <?php if ((int) $a['active'] === 1): ?>
        <span class="text-[10px] bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 px-2 py-0.5 rounded font-bold shrink-0">فعال</span>
      <?php else: ?>
        <span class="text-[10px] bg-slate-500/10 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded font-bold shrink-0">غیرفعال</span>
      <?php endif; ?>
    </div>
    <div>
      <div class="text-base font-black text-slate-900 dark:text-white"><?= number_format((int) $a['balance']) ?> <span class="text-[11px] font-normal text-slate-500 dark:text-slate-400"><?= e($currency) ?></span></div>
      <div class="text-[11px] text-slate-500 dark:text-slate-400">اول دوره: <?= number_format((int) $a['opening_balance']) ?></div>
    </div>
    <div class="flex items-center gap-2 pt-1 mt-auto">
      <a href="/admin/accounting/accounts?edit=<?= (int) $a['id'] ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:border-slate-400 dark:hover:border-slate-500">ویرایش</a>
      <form method="post" action="/admin/accounting/accounts/delete/<?= (int) $a['id'] ?>" onsubmit="return confirm('این حساب حذف شود؟ (فقط حسابی که سند ندارد)')">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-500 dark:text-rose-400 text-[11px] font-bold hover:bg-rose-500/20">حذف</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
