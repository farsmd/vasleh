<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-6">پیگیری سفارش</h1>
  <form method="post" action="/shop/track" class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4 max-w-xl">
    <div>
      <label class="text-xs font-bold text-slate-800 block mb-1.5" for="t-code">کد پیگیری</label>
      <input id="t-code" type="text" name="code" dir="ltr" required value="<?= e($code) ?>" placeholder="VSL-123456" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white text-left">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-800 block mb-1.5" for="t-phone">شماره موبایل</label>
      <input id="t-phone" type="tel" name="phone" dir="ltr" required value="<?= e($phone) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white text-left">
    </div>
    <button class="px-6 py-3 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm" type="submit">پیگیری</button>
  </form>
  <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-2xl px-4 py-3 mt-6 max-w-xl"><?= e($error) ?></div><?php endif; ?>
  <?php if (!empty($order)): ?>
  <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm mt-6">
    <h2 class="font-black text-slate-900 dark:text-white text-lg">سفارش <span dir="ltr"><?= e($order['code']) ?></span></h2>
    <p class="text-sm text-slate-600 mt-2">وضعیت: <strong><?= e($statuses[$order['status']] ?? $order['status']) ?></strong> — پرداخت: <?= $order['payment_status'] === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' ?></p>
    <p class="text-xs text-slate-400 mt-1">ثبت: <?= e($order['created_at']) ?> — خریدار: <?= e($order['customer_name']) ?></p>
    <div class="overflow-x-auto mt-4">
      <table class="w-full text-sm border-collapse">
        <tr class="text-right text-slate-500 border-b border-slate-100 dark:border-slate-800"><th class="py-2 pl-3 font-medium">محصول</th><th class="py-2 pl-3 font-medium">ویژگی‌ها</th><th class="py-2 pl-3 font-medium">تعداد</th><th class="py-2 font-medium">جمع</th></tr>
        <?php foreach ($order['items'] as $it): ?>
        <tr class="border-b border-slate-50">
          <td class="py-2 pl-3"><?= e($it['title']) ?></td>
          <td class="py-2 pl-3 text-slate-500"><?= e($it['options_text']) ?></td>
          <td class="py-2 pl-3"><?= (int) $it['qty'] ?></td>
          <td class="py-2"><?= number_format((int) $it['line_total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if ((int) ($order['discount'] ?? 0) > 0): ?><tr><td colspan="3" class="py-2 pl-3 text-right text-emerald-600">تخفیف کوپن</td><td class="py-2">-<?= number_format((int) $order['discount']) ?></td></tr><?php endif; ?>
        <?php if ((int) ($order['gift_fee'] ?? 0) > 0): ?><tr><td colspan="3" class="py-2 pl-3 text-right">بسته‌بندی کادویی</td><td class="py-2"><?= number_format((int) $order['gift_fee']) ?></td></tr><?php endif; ?>
        <tr><th colspan="3" class="py-2 pl-3 text-right">قابل پرداخت</th><td class="py-2"><strong><?= number_format((int) $order['total']) ?> <?= e($currency) ?></strong></td></tr>
      </table>
    </div>
    <p class="mt-4"><a href="/shop/invoice/<?= e(rawurlencode((string) $order['code'])) ?>?phone=<?= e(rawurlencode($phone)) ?>" class="px-5 py-3 rounded-2xl bg-slate-900 text-white text-sm font-bold inline-block">مشاهده و چاپ فاکتور</a></p>
  </div>
  <?php endif; ?>
</div>
