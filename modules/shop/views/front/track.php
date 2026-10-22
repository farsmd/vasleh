<h1>پیگیری سفارش</h1>
<form method="post" action="/shop/track" style="max-width:26rem;background:#fff;border:1px solid #e5e5e5;border-radius:.8rem;padding:1rem">
  <label>کد پیگیری
    <input type="text" name="code" dir="ltr" required value="<?= e($code) ?>" placeholder="VSL-123456">
  </label>
  <label>شماره موبایل
    <input type="tel" name="phone" dir="ltr" required value="<?= e($phone) ?>">
  </label>
  <button class="btn" type="submit">پیگیری</button>
</form>
<?php if (!empty($error)): ?><div class="error" style="max-width:26rem;margin-top:1rem"><?= e($error) ?></div><?php endif; ?>
<?php if (!empty($order)): ?>
<div style="background:#fff;border:1px solid #e5e5e5;border-radius:.8rem;padding:1rem;margin-top:1rem">
  <h2 style="margin-top:0">سفارش <span dir="ltr"><?= e($order['code']) ?></span></h2>
  <p>وضعیت: <strong><?= e($statuses[$order['status']] ?? $order['status']) ?></strong> — پرداخت: <?= $order['payment_status'] === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' ?></p>
  <p class="muted">ثبت: <?= e($order['created_at']) ?> — خریدار: <?= e($order['customer_name']) ?></p>
  <table>
    <tr><th>محصول</th><th>ویژگی‌ها</th><th>تعداد</th><th>جمع</th></tr>
    <?php foreach ($order['items'] as $it): ?>
    <tr>
      <td><?= e($it['title']) ?></td>
      <td><?= e($it['options_text']) ?></td>
      <td><?= (int) $it['qty'] ?></td>
      <td><?= number_format((int) $it['line_total']) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr><th colspan="3">جمع + ارسال + مالیات</th><td><strong><?= number_format((int) $order['total']) ?> <?= e($currency) ?></strong></td></tr>
  </table>
  <p><a class="btn light" href="/shop/invoice/<?= e(rawurlencode((string) $order['code'])) ?>?phone=<?= e(rawurlencode($phone)) ?>">مشاهده و چاپ فاکتور</a></p>
</div>
<?php endif; ?>
