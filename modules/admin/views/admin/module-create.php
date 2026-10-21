<div class="card">
  <h1>ساخت ماژول تازه</h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/modules/create">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>نامک لاتین — نام پوشه ماژول و نشانی‌اش، مثل shop</label>
    <input name="slug" required pattern="[a-z][a-z0-9_-]*" placeholder="shop">
    <label>عنوان فارسی</label>
    <input name="title" required placeholder="فروشگاه">
    <label>توضیح</label>
    <textarea name="description" rows="3"></textarea>
    <button class="btn green">ساخت ماژول</button>
    <a class="btn gray" href="/admin/modules">برگشت</a>
  </form>
  <p><small>پوشه ماژول با فایل core.php و ویوی فرانت و مدیریت ساخته می‌شود و روت‌هایش همان لحظه زنده‌اند — بدون هیچ تغییری در هسته.</small></p>
</div>
