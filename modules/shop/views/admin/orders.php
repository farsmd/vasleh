<div class="card">
  <h1>سفارش‌های فروشگاه</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    <span class="btn gray">کل سفارش‌ها: <?= (int) $stats['orders'] ?></span>
    <span class="btn gray">جدید: <?= (int) $stats['new'] ?></span>
    <span class="btn gray">جمع فروش: <?= number_format((int) $stats['sales']) ?> <?= e($currency) ?></span>
  </p>
  <p>
    <a class="btn <?= $activeStatus === '' ? 'green' : 'gray' ?>" href="/admin/shop/orders">همه</a>
    <?php foreach ($statuses as $k => $label): ?>
      <a class="btn <?= $activeStatus === $k ? 'green' : 'gray' ?>" href="/admin/shop/orders?status=<?= $k ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </p>
  <?php if (empty($orders)): ?>
    <p>سفارشی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>کد پیگیری</th><th>مشتری</th><th>موبایل</th><th>مبلغ</th><th>سود تقریبی</th><th>وضعیت</th><th>پرداخت</th><th>تاریخ</th><th>عملیات</th></tr>
    <?php foreach ($orders as $o): ?>
    <tr>
      <td><code dir="ltr"><?= e($o['code']) ?></code></td>
      <td><?= e($o['customer_name']) ?></td>
      <td dir="ltr"><?= e($o['phone']) ?></td>
      <td><?= number_format((int) $o['total']) ?></td>
      <td><?= isset($profits[(int) $o['id']]) ? number_format((int) $profits[(int) $o['id']]) : '—' ?></td>
      <td><?= e($statuses[$o['status']] ?? $o['status']) ?></td>
      <td><?= $o['payment_status'] === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' ?></td>
      <td><?= e($o['created_at']) ?></td>
      <td>
        <a class="btn yellow" href="/admin/shop/orders/<?= (int) $o['id'] ?>">جزئیات</a>
        <a class="btn gray" target="_blank" href="/admin/shop/invoice/<?= (int) $o['id'] ?>">فاکتور</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p class="muted">سود تقریبی = فروش − قیمت تمام‌شده ثبت‌شده در زمان فروش؛ برای سفارش‌های قدیمی که قیمت تمام‌شده ثبت نشده، سود برابر فروش نمایش داده می‌شود.</p>
  <?php endif; ?>
</div>
