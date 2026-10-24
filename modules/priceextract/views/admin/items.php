<div class="card">
  <h1>فهرست قیمت‌های استخراج‌شده</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="get" action="/admin/priceextract/items">
    <div class="row">
      <label>منبع
        <select name="source">
          <option value="0">— همه منابع —</option>
          <?php foreach ($sources as $s): ?>
            <option value="<?= (int) $s['id'] ?>" <?= $filters['source_id'] === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <?php if (!empty($categories)): ?>
      <label>دسته
        <select name="category">
          <option value="">— همه دسته‌ها —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= e($c) ?>" <?= $filters['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <?php endif; ?>
      <label>جست‌وجو (نام یا کد)
        <input type="text" name="q" value="<?= e($filters['q']) ?>">
      </label>
    </div>
    <p><button class="btn gray">فیلتر</button></p>
  </form>

  <?php if (empty($rows)): ?>
    <p>آیتمی با این فیلتر نیست. از «منابع» یک منبع را اجرا کنید.</p>
  <?php else: ?>
  <p><strong><?= count($rows) ?></strong> ردیف</p>
  <table>
    <tr><th>نام محصول</th><th>کد</th><th>دسته</th><th>قیمت (تومان)</th><th>منبع</th><th>استخراج‌شده در</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td class="wrap"><?= e($r['title']) ?></td>
      <td><code dir="ltr"><?= e($r['code']) ?></code></td>
      <td><?= e($r['category']) ?></td>
      <td><?= $r['price'] > 0 ? number_format((int) $r['price']) : '۰' ?></td>
      <td><?= e($r['source_title']) ?></td>
      <td><?= e($r['fetched_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
