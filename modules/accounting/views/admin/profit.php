<div class="card">
  <h1>سود سفارش‌ها</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <?php if (empty($hasShop)): ?>
    <p>ماژول فروشگاه فعال نیست؛ گزارش سود بدون سفارش معنا ندارد.</p>
  <?php elseif (empty($rows)): ?>
    <p>سفارشی (به‌جز لغوشده‌ها) نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>کد سفارش</th><th>مشتری</th><th>جمع ردیف‌ها</th><th>تخفیف</th><th>قیمت تمام‌شده</th><th>سود</th><th>تاریخ</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><code dir="ltr"><?= e($r['code']) ?></code></td>
      <td><?= e($r['customer_name']) ?></td>
      <td><?= number_format((int) $r['subtotal']) ?></td>
      <td><?= number_format((int) $r['discount']) ?></td>
      <td><?= number_format((int) $r['cost']) ?></td>
      <td><strong><?= number_format((int) $r['profit']) ?></strong></td>
      <td><?= e($r['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr>
      <th colspan="2">جمع کل</th>
      <th><?= number_format((int) $totals['revenue']) ?></th>
      <th><?= number_format((int) $totals['discount']) ?></th>
      <th><?= number_format((int) $totals['cost']) ?></th>
      <th><?= number_format((int) $totals['profit']) ?></th>
      <th></th>
    </tr>
  </table>
  <p class="muted">سود = مجموع (قیمت فروش − قیمت تمام‌شدهٔ ثبت‌شده در زمان فروش) × تعداد، برای سفارش‌های لغوشده محاسبه نمی‌شود. مبالغ به <?= e($currency) ?> است؛ هزینه ارسال و مالیات در سود لحاظ نشده‌اند.</p>
  <?php endif; ?>
</div>
