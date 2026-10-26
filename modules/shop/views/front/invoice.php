<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>فاکتور <?= e($order['code']) ?></title>
<link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
<link rel="stylesheet" href="/assets/shop/shop.css">
<style>body{font-family:'Vazirmatn',system-ui,sans-serif}@media print{.noprint{display:none}}</style>
</head>
<body class="bg-slate-50 text-slate-800">
<div class="max-w-3xl mx-auto px-4 py-8">
  <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
    <h1 class="font-black text-slate-900 text-xl"><?= e($shopTitle) ?> — فاکتور فروش</h1>
    <p class="text-sm text-slate-600 mt-3">کد پیگیری: <strong dir="ltr"><?= e($order['code']) ?></strong> — تاریخ: <?= e($order['created_at']) ?> — وضعیت: <?= e($statuses[$order['status']] ?? $order['status']) ?></p>
    <p class="text-sm text-slate-600 mt-1">خریدار: <?= e($order['customer_name']) ?> — موبایل: <span dir="ltr"><?= e($order['phone']) ?></span></p>
    <?php if (($order['delivery_slot'] ?? '') !== '' || ($order['payment_method'] ?? '') !== ''): ?><p class="text-sm text-slate-600 mt-1"><?php if (($order['delivery_slot'] ?? '') !== ''): ?>بازه‌ی تحویل: <?= e($order['delivery_slot']) ?> — <?php endif; ?><?php if (($order['payment_method'] ?? '') !== ''): ?>روش پرداخت: <?= e($order['payment_method']) ?><?php endif; ?></p><?php endif; ?>
    <div class="overflow-x-auto mt-6">
      <table class="w-full text-sm border-collapse">
        <tr class="text-right text-slate-500 border-b border-slate-100"><th class="py-2 pl-3 font-medium">کالا</th><th class="py-2 pl-3 font-medium">ویژگی‌ها</th><th class="py-2 pl-3 font-medium">قیمت واحد</th><th class="py-2 pl-3 font-medium">تعداد</th><th class="py-2 font-medium">جمع</th></tr>
        <?php foreach ($items as $it): ?>
        <tr class="border-b border-slate-50">
          <td class="py-2 pl-3"><?= e($it['title']) ?></td>
          <td class="py-2 pl-3 text-slate-500"><?= e($it['options_text']) ?></td>
          <td class="py-2 pl-3"><?= number_format((int) $it['unit_price']) ?></td>
          <td class="py-2 pl-3"><?= (int) $it['qty'] ?></td>
          <td class="py-2"><?= number_format((int) $it['line_total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><th colspan="4" class="py-2 pl-3 text-right">جمع ردیف‌ها</th><td class="py-2"><?= number_format((int) $order['subtotal']) ?></td></tr>
        <?php if ((int) ($order['discount'] ?? 0) > 0): ?><tr><th colspan="4" class="py-2 pl-3 text-right">تخفیف کوپن<?= ($order['coupon_code'] ?? '') !== '' ? ' (' . e($order['coupon_code']) . ')' : '' ?></th><td class="py-2">-<?= number_format((int) $order['discount']) ?></td></tr><?php endif; ?>
        <?php if ((int) ($order['gift_fee'] ?? 0) > 0): ?><tr><th colspan="4" class="py-2 pl-3 text-right">بسته‌بندی کادویی</th><td class="py-2"><?= number_format((int) $order['gift_fee']) ?></td></tr><?php endif; ?>
        <tr><th colspan="4" class="py-2 pl-3 text-right">ارسال</th><td class="py-2"><?= number_format((int) $order['shipping']) ?></td></tr>
        <tr><th colspan="4" class="py-2 pl-3 text-right">مالیات</th><td class="py-2"><?= number_format((int) $order['tax']) ?></td></tr>
        <tr><th colspan="4" class="py-2 pl-3 text-right">قابل پرداخت (<?= e($currency) ?>)</th><td class="py-2"><strong><?= number_format((int) $order['total']) ?></strong></td></tr>
      </table>
    </div>
    <p class="noprint mt-6 flex gap-3">
      <button onclick="window.print()" class="px-5 py-3 rounded-2xl bg-brand-600 text-white text-sm font-bold">چاپ</button>
      <a href="/shop/track" class="px-5 py-3 rounded-2xl bg-slate-100 text-slate-700 text-sm font-bold">بازگشت به پیگیری</a>
    </p>
  </div>
</div>
</body>
</html>
