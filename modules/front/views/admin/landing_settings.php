<div class="card">
  <h1>تنظیمات لندینگ</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <p>قیمت‌های صفحه اصلی (تومان/ماه). تغییرها بلافاصله روی لندینگ اعمال می‌شوند.</p>
  <form method="post" action="/admin/landing">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>اشتراک پایه
      <input type="number" name="price_base" min="0" required value="<?= (int) $prices['base'] ?>">
    </label>
    <?php foreach ($modules as $slug => $m): ?>
      <label><?= e($m['title']) ?>
        <input type="number" name="price_<?= e($slug) ?>" min="0" required value="<?= (int) $prices[$slug] ?>">
      </label>
    <?php endforeach; ?>
    <p><button class="btn green">ذخیره قیمت‌ها</button> <a class="btn gray" href="/admin/leads">درخواست‌های آزمایشی</a></p>
  </form>
</div>
