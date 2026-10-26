<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>فاکتور فروش <?= e($order['code']) ?></title>
<style>
  body{font-family:Tahoma,system-ui,sans-serif;margin:2rem auto;max-width:48rem;color:#222}
  header{display:flex;justify-content:space-between;align-items:start;border-bottom:2px solid #222;padding-bottom:1rem;margin-bottom:1rem}
  table{width:100%;border-collapse:collapse;margin:1rem 0}
  th,td{border:1px solid #bbb;padding:.45rem .6rem;text-align:right}
  th{background:#f2f2f2}
  .ltr{direction:ltr;unicode-bidi:embed}
  @media print{ .noprint{display:none} body{margin:0} }
</style>
</head>
<body>
<header>
  <div>
    <h1 style="margin:0"><?= e($shopTitle) ?></h1>
    <p>فاکتور فروش</p>
  </div>
  <div>
    <div>کد پیگیری: <strong class="ltr"><?= e($order['code']) ?></strong></div>
    <div>تاریخ: <?= e($order['created_at']) ?></div>
    <div>وضعیت: <?= e($statuses[$order['status']] ?? $order['status']) ?> — <?= $order['payment_status'] === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' ?></div>
  </div>
</header>
<table>
  <tr><th>خریدار</th><td><?= e($order['customer_name']) ?></td><th>موبایل</th><td class="ltr"><?= e($order['phone']) ?></td></tr>
  <tr><th>نشانی</th><td colspan="3"><?= e($order['address']) ?></td></tr>
</table>
<table>
  <tr><th>ردیف</th><th>کالا</th><th>ویژگی‌ها</th><th>قیمت واحد</th><th>تعداد</th><th>جمع</th></tr>
  <?php foreach ($items as $i => $it): ?>
  <tr>
    <td><?= $i + 1 ?></td>
    <td><?= e($it['title']) ?></td>
    <td><?= e($it['options_text']) ?></td>
    <td><?= number_format((int) $it['unit_price']) ?></td>
    <td><?= (int) $it['qty'] ?></td>
    <td><?= number_format((int) $it['line_total']) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr><th colspan="5">جمع ردیف‌ها</th><td><?= number_format((int) $order['subtotal']) ?></td></tr>
    <?php if ((int) ($order['discount'] ?? 0) > 0): ?><tr><th colspan="5">تخفیف کوپن<?= ($order['coupon_code'] ?? '') !== '' ? ' (' . e($order['coupon_code']) . ')' : '' ?></th><td>-<?= number_format((int) $order['discount']) ?></td></tr><?php endif; ?>
    <?php if ((int) ($order['gift_fee'] ?? 0) > 0): ?><tr><th colspan="5">بسته‌بندی کادویی</th><td><?= number_format((int) ($order['gift_fee'])) ?></td></tr><?php endif; ?>
  <tr><th colspan="5">هزینه ارسال</th><td><?= number_format((int) $order['shipping']) ?></td></tr>
  <tr><th colspan="5">مالیات</th><td><?= number_format((int) $order['tax']) ?></td></tr>
  <tr><th colspan="5">مبلغ قابل پرداخت (<?= e($currency) ?>)</th><td><strong><?= number_format((int) $order['total']) ?></strong></td></tr>
</table>
<?php if (($order['note'] ?? '') !== ''): ?><p>یادداشت مشتری: <?= e($order['note']) ?></p><?php endif; ?>
<p class="noprint"><button onclick="window.print()">چاپ فاکتور</button></p>
</body>
</html>
