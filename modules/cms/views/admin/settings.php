<div class="card">
  <h1>تنظیمات محتوا</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <form method="post" action="/admin/cms/settings">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>نام سایت (در هدر و عنوان صفحات)</label>
    <input name="site_name" value="<?= e($siteName) ?>">
    <label>صفحه اصلی سایت (آدرس ریشه /)</label>
    <select name="homepage">
      <option value="" <?= $homepage === '' ? 'selected' : '' ?>>پیش‌فرض سیستم (صفحه ماژول فرانت)</option>
      <option value="blog" <?= $homepage === 'blog' ? 'selected' : '' ?>>وبلاگ — آخرین نوشته‌ها</option>
      <?php foreach ($published as $p): ?>
        <option value="<?= (int) $p['id'] ?>" <?= $homepage === (string) $p['id'] ? 'selected' : '' ?>>
          <?= ($p['type'] ?? 'post') === 'page' ? 'برگه' : 'نوشته' ?>: <?= e($p['title']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <p><small>وقتی یک محتوا را صفحه اصلی بگذارید، آدرس ریشه سایت را ماژول محتوا با همان هدر/فوتر/قالبِ انتخابیِ آن محتوا پوشش می‌دهد. برای برگشت، «پیش‌فرض سیستم» را انتخاب کنید. فقط محتواهای منتشرشده در فهرست‌اند.</small></p>
    <button class="btn green">ذخیره تنظیمات</button>
  </form>
</div>
