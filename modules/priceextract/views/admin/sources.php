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
  <table>
    <tr><th>عنوان</th><th>آدرس</th><th>نوع</th><th>آخرین اجرا</th><th>آیتم</th><th>خطا</th><th>عملیات</th></tr>
    <?php foreach ($sources as $s): ?>
    <tr>
      <td class="wrap"><?= e($s['title']) ?><?= (int) $s['active'] !== 1 ? ' <small class="muted">(غیرفعال)</small>' : '' ?></td>
      <td class="wrap"><code dir="ltr"><?= e($s['url']) ?></code></td>
      <td><?= e($adapters[$s['adapter']] ?? $s['adapter']) ?></td>
      <td><?= e($s['last_run']) ?></td>
      <td><?= (int) $s['last_count'] ?></td>
      <td class="wrap"><?= e($s['last_error']) ?></td>
      <td>
        <form method="post" action="/admin/priceextract/sources/run/<?= (int) $s['id'] ?>" style="display:inline">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn green">اجرا</button>
        </form>
        <a class="btn gray" href="/admin/priceextract/items?source=<?= (int) $s['id'] ?>">قیمت‌ها</a>
        <a class="btn gray" href="/admin/priceextract/sources/runs/<?= (int) $s['id'] ?>">سوابق</a>
        <a class="btn yellow" href="/admin/priceextract?edit=<?= (int) $s['id'] ?>">ویرایش</a>
        <form method="post" action="/admin/priceextract/sources/delete/<?= (int) $s['id'] ?>" style="display:inline" onsubmit="return confirm('این منبع با همه آیتم‌هایش حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
