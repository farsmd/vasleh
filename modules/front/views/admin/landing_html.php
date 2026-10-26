<div class="card">
  <h1>فایل‌های HTML لندینگ</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>اینجا فایل‌های HTML دلخواه برای لندینگ می‌سازید؛ محتوای فایل پیش‌فرض، به‌عنوان یک بخش اضافه، زیر ماشین‌حساب قیمت در صفحه اصلی نمایش داده می‌شود. محتوا خام رندر می‌شود (HTML کامل مجاز است) چون فقط مالک به این صفحه دسترسی دارد. توکن‌های فیلدها (مثل <code dir="ltr">{{hero_title}}</code>) داخل این HTML هم جایگزین می‌شوند.</p>
  <?php if (empty($files)): ?>
    <p>هنوز فایلی نیست؛ تا وقتی فایل پیش‌فرضی انتخاب نشده، بخش اضافه‌ای در لندینگ نمایش داده نمی‌شود.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>عنوان</th><th>نام</th><th>پیش‌فرض</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($files as $f): ?>
          <tr>
            <td><?= e($f['title']) ?></td>
            <td dir="ltr" style="text-align: right;"><?= e($f['name']) ?></td>
            <td><?= (int) $f['is_default'] === 1 ? '<span class="btn green">پیش‌فرض</span>' : '—' ?></td>
            <td>
              <a class="btn yellow" href="/admin/landing/html?edit=<?= (int) $f['id'] ?>">ویرایش</a>
              <?php if ((int) $f['is_default'] !== 1): ?>
              <form method="post" action="/admin/landing/html/<?= (int) $f['id'] ?>/default" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn green">پیش‌فرض شود</button>
              </form>
              <?php endif; ?>
              <form method="post" action="/admin/landing/html/<?= (int) $f['id'] ?>/delete" onsubmit="return confirm('این فایل حذف شود؟');" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn red">حذف</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<div class="card">
  <h1><?= !empty($edit) ? 'ویرایش فایل' : 'افزودن فایل' ?></h1>
  <form method="post" action="/admin/landing/html/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <label>عنوان
      <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>" placeholder="مثلاً معرفی کوتاه">
    </label>
    <label>نام فایل (با حرف شروع شود؛ فقط حروف، رقم و زیرخط)
      <input type="text" name="name" dir="ltr" required value="<?= e($edit['name'] ?? '') ?>" placeholder="مثلاً intro">
    </label>
    <label>محتوای HTML
      <textarea name="content" rows="10" dir="ltr" style="text-align: left;"><?= e($edit['content'] ?? '') ?></textarea>
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت فایل' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/landing/html">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>
