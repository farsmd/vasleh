<div class="card">
  <h1>منابع استخراج قیمت</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    <span class="btn gray">منابع: <?= (int) $stats['sources'] ?></span>
    <span class="btn gray">آیتم‌های ذخیره‌شده: <?= (int) $stats['items'] ?></span>
    <a class="btn gray" href="/admin/priceextract/items">فهرست قیمت‌ها</a>
  </p>
  <form method="post" action="/admin/priceextract/sources/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
      <label>عنوان منبع
        <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>" placeholder="مثلاً لیست قیمت درمت">
      </label>
      <label>آدرس (URL داده)
        <input type="text" name="url" dir="ltr" required value="<?= e($edit['url'] ?? '') ?>" placeholder="https://...">
      </label>
      <label>نوع استخراج
        <select name="adapter">
          <?php foreach ($adapters as $k => $label): ?>
            <option value="<?= $k ?>" <?= ($edit['adapter'] ?? 'json') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <label>نگاشت فیلدها (JSON — فقط برای JSON API؛ مثلاً {"rows":"data.items","title":"name","code":"code","price":"price","category":"category"})
      <input type="text" name="mapping" dir="ltr" value="<?= e($edit['mapping'] ?? '') ?>">
    </label>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="active" value="1" <?= !isset($edit) || (int) ($edit['active'] ?? 1) === 1 ? 'checked' : '' ?>> فعال
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت منبع' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/priceextract">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<div class="card">
  <h2>وارد کردن از CSV</h2>
  <p>برای سایت‌هایی که API عمومی ندارند (مثل price.doormat.ir): فایل CSV با ستون‌های «دسته،نام،کد،قیمت» را انتخاب کنید. سطر اول اگر سرستون باشد خودکار رد می‌شود.</p>
  <form method="post" action="/admin/priceextract/import" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>منبع
        <select name="source_id" required>
          <?php foreach ($sources as $s): ?>
            <option value="<?= (int) $s['id'] ?>"><?= e($s['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>فایل CSV (حداکثر ۵ مگابایت)
        <input type="file" name="csv_file" accept=".csv,text/csv" required>
      </label>
    </div>
    <p><button class="btn green">وارد کردن</button></p>
  </form>
</div>

<div class="card">
  <?php if (empty($sources)): ?>
    <p>هنوز منبعی نیست. آدرس API یا صفحه جدول قیمت را بالا ثبت کنید و «اجرا» را بزنید.</p>
  <?php else: ?>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php foreach ($sources as $s): ?>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 flex flex-col gap-3">
      <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
          <div class="text-xs font-bold text-slate-900 dark:text-white truncate"><?= e($s['title']) ?></div>
          <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate"><code dir="ltr"><?= e($s['url']) ?></code></div>
        </div>
        <?php if ((int) $s['active'] === 1): ?>
          <span class="text-[10px] bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 px-2 py-0.5 rounded font-bold shrink-0">فعال</span>
        <?php else: ?>
          <span class="text-[10px] bg-slate-500/10 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded font-bold shrink-0">غیرفعال</span>
        <?php endif; ?>
      </div>
      <div class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">
        <?= e($adapters[$s['adapter']] ?? $s['adapter']) ?> • آخرین اجرا: <?= e($s['last_run']) ?> • آیتم: <?= (int) $s['last_count'] ?>
      </div>
      <?php if (!empty($s['last_error'])): ?>
        <p class="text-[11px] text-rose-500 dark:text-rose-400 leading-relaxed"><?= e($s['last_error']) ?></p>
      <?php endif; ?>
      <div class="flex flex-wrap items-center gap-2 pt-1 mt-auto">
        <form method="post" action="/admin/priceextract/sources/run/<?= (int) $s['id'] ?>">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-500 text-white text-[11px] font-bold hover:bg-emerald-600">اجرا</button>
        </form>
        <a href="/admin/priceextract/items?source=<?= (int) $s['id'] ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:border-slate-400 dark:hover:border-slate-500">قیمت‌ها</a>
        <a href="/admin/priceextract/sources/runs/<?= (int) $s['id'] ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:border-slate-400 dark:hover:border-slate-500">سوابق</a>
        <a href="/admin/priceextract?edit=<?= (int) $s['id'] ?>" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold hover:border-slate-400 dark:hover:border-slate-500">ویرایش</a>
        <form method="post" action="/admin/priceextract/sources/delete/<?= (int) $s['id'] ?>" onsubmit="return confirm('این منبع با همه آیتم‌هایش حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-500 dark:text-rose-400 text-[11px] font-bold hover:bg-rose-500/20">حذف</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
