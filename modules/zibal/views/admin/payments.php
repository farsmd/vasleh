<div class="card">
  <h1>پرداخت‌های زیبال</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <?php if (!empty($rows)): ?>
  <table>
    <tr><th>سفارش</th><th>trackId</th><th>مبلغ (ریال)</th><th>مبلغ (<?= e($currency) ?>)</th><th>وضعیت</th><th>مرجع</th><th>کارت</th><th>تاریخ</th></tr>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><a href="/admin/shop/orders"><?= e($r['order_code']) ?></a></td>
      <td dir="ltr"><?= e($r['track_id']) !== '' ? e($r['track_id']) : '—' ?></td>
      <td><?= number_format((int) $r['amount_rial']) ?></td>
      <td><?= number_format((int) $r['amount_rial'] / 10) ?></td>
      <td><?= $r['status'] === 'paid' ? 'پرداخت‌شده' : ($r['status'] === 'cancelled' ? 'لغوشده' : ($r['status'] === 'failed' ? 'ناموفق' : 'در انتظار')) ?></td>
      <td dir="ltr"><?= e($r['ref_number']) !== '' ? e($r['ref_number']) : '—' ?></td>
      <td dir="ltr"><?= e($r['card_number']) !== '' ? e($r['card_number']) : '—' ?></td>
      <td dir="ltr"><?= e($r['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?>
  <p>هنوز پرداختی ثبت نشده است.</p>
  <?php endif; ?>
</div>
