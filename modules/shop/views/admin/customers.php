<div class="card">
  <h1>مشتریان فروشگاه</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="get" action="/admin/shop/customers">
    <div class="row">
      <label>جستجو (نام یا موبایل)
        <input type="text" name="q" value="<?= e($q) ?>">
      </label>
    </div>
    <p><button class="btn gray">جستجو</button><?php if ($q !== ''): ?> <a class="btn gray" href="/admin/shop/customers">همه</a><?php endif; ?></p>
  </form>
  <?php if (empty($customers)): ?>
    <p>هنوز مشتری ثبت‌شده‌ای نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>نام</th><th>موبایل</th><th>آدرس</th><th>تعداد سفارش</th><th>جمع خرید (بدون لغوشده‌ها)</th><th>آخرین سفارش</th></tr>
    <?php foreach ($customers as $c): ?>
    <tr>
      <td><?= e($c['name']) ?></td>
      <td dir="ltr"><?= e($c['phone']) ?></td>
      <td class="wrap"><?= e($c['address'] ?? '') ?></td>
      <td><?= (int) $c['orders_count'] ?></td>
      <td><?= number_format((int) $c['total_spent']) ?> <?= e($currency) ?></td>
      <td><?= e($c['last_order'] ?? '—') ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
