<div class="card">
  <h1>ویرایش ماژول «<?= e($module['title']) ?>»</h1>
  <form method="post" action="/admin/modules/edit/<?= e($module['slug']) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان</label>
    <input name="title" required value="<?= e($module['title']) ?>">
    <label>توضیح</label>
    <textarea name="description" rows="3"><?= e($module['description'] ?? '') ?></textarea>
    <label>نسخه</label>
    <input name="version" required value="<?= e($module['version']) ?>">
    <button class="btn">ذخیره</button>
    <a class="btn gray" href="/admin/modules">برگشت</a>
  </form>
  <p><small>ویرایش روی لایه مشخصات اعمال می‌شود؛ کد ماژول از پنل دست نمی‌خورد.</small></p>
</div>
