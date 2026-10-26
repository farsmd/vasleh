<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-2">سفارش <span dir="ltr"><?= e($order['code']) ?></span></h1>
  <p class="text-sm text-slate-600 dark:text-slate-400 mb-6">وضعیت: <strong><?= e(\Modules\Shop\Orders::STATUSES[$order['status']] ?? $order['status']) ?></strong> — پرداخت: <?= $order['payment_status'] === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' ?> — ثبت: <?= e($order['created_at']) ?></p>
  <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <tr class="text-right text-slate-500 border-b border-slate-100 dark:border-slate-800"><th class="py-2 pl-3 font-medium">محصول</th><th class="py-2 pl-3 font-medium">ویژگی‌ها</th><th class="py-2 pl-3 font-medium">تعداد</th><th class="py-2 font-medium">جمع</th></tr>
        <?php foreach ($items as $it): ?>
        <tr class="border-b border-slate-50 dark:border-slate-800/50">
          <td class="py-2 pl-3"><?= e($it['title']) ?></td>
          <td class="py-2 pl-3 text-slate-500"><?= e($it['options_text']) ?></td>
          <td class="py-2 pl-3"><?= (int) $it['qty'] ?></td>
          <td class="py-2"><?= number_format((int) $it['line_total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if ((int) ($order['discount'] ?? 0) > 0): ?><tr><td colspan="3" class="py-2 pl-3 text-right text-emerald-600">تخفیف کوپن</td><td class="py-2">-<?= number_format((int) $order['discount']) ?></td></tr><?php endif; ?>
        <?php if ((int) ($order['gift_fee'] ?? 0) > 0): ?><tr><td colspan="3" class="py-2 pl-3 text-right">بسته‌بندی کادویی</td><td class="py-2"><?= number_format((int) $order['gift_fee']) ?></td></tr><?php endif; ?>
        <tr><td colspan="3" class="py-2 pl-3 text-right">هزینه ارسال</td><td class="py-2"><?= number_format((int) $order['shipping']) ?></td></tr>
        <tr><td colspan="3" class="py-2 pl-3 text-right">مالیات</td><td class="py-2"><?= number_format((int) $order['tax']) ?></td></tr>
        <tr><th colspan="3" class="py-2 pl-3 text-right">قابل پرداخت</th><td class="py-2"><strong><?= number_format((int) $order['total']) ?> <?= e($currency) ?></strong></td></tr>
      </table>
    </div>
    <p class="mt-4"><a href="/shop/account/orders" class="text-brand-600 hover:underline text-sm">بازگشت به سفارش‌های من</a></p>
  </div>
</div>
