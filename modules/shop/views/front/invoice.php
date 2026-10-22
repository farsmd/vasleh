<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>فاکتور <?= e($order['code']) ?></title>
<style>
  body{font-family:Tahoma,system-ui,sans-serif;margin:2rem auto;max-width:48rem;color:#222}
  table{width:100%;border-collapse:collapse;margin:1rem 0}
  th,td{border:1px solid #bbb;padding:.45rem .6rem;text-align:right}
  th{background:#f2f2f2}
  .ltr{direction:ltr;unicode-bidi:embed}
  @media print{ .noprint{display:none} body{margin:0} }
</style>
</head>
<body>
<h1><?= e($shopTitle) ?> — فاکتور فروش</h1>
<p>کد پیگیری: <strong class="ltr"><?= e($order['code']) ?></strong> — تاریخ: <?= e($order['created_at']) ?> — وضعیت: <?= e($statuses[$order['status']] ?? $order['status']) ?></p>
<p>خریدار: <?= e($order['customer_name']) ?> — موبایل: <span class="ltr"><?= e($order['phone']) ?></span></p>
<table>
  <tr><th>کالا</th><th>ویژگی‌ها</th><th>قیمت واحد</th><th>تعداد</th><th>جمع</th></tr>
  <?php foreach ($items as $it): ?>
  <tr>
    <td><?= e($it['title']) ?></td>
    <td><?= e($it['options_text']) ?></td>
    <td><?= number_format((int) $it['unit_price']) ?></td>
    <td><?= (int) $it['qty'] ?></td>
    <td><?= number_format((int) $it['line_total']) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr><th colspan="4">جمع ردیف‌ها</th><td><?= number_format((int) $order['subtotal']) ?></td></tr>
  <tr><th colspan="4">ارسال</th><td><?= number_format((int) $order['shipping']) ?></td></tr>
  <tr><th colspan="4">مالیات</th><td><?= number_format((int) $order['tax']) ?></td></tr>
  <tr><th colspan="4">قابل پرداخت (<?= e($currency) ?>)</th><td><strong><?= number_format((int) $order['total']) ?></strong></td></tr>
</table>
<p class="noprint"><button onclick="window.print()">چاپ</button> <a href="/shop/track">بازگشت به پیگیری</a></p>
</body>
</html>
