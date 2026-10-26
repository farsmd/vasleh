<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-2">سفارش‌های من</h1>
  <p class="text-sm text-slate-500 dark:text-slate-400 mb-6"><?= e($customer['name']) ?> — <span dir="ltr"><?= e($customer['phone']) ?></span></p>
  <?php if (!empty($flash)): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl px-4 py-3 mb-6 max-w-xl"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-2xl px-4 py-3 mb-6 max-w-xl"><?= e($error) ?></div><?php endif; ?>
  <div class="flex flex-wrap gap-2 mb-6">
    <a href="/shop/account/profile" class="px-5 py-2.5 rounded-2xl bg-slate-900 text-white text-sm font-bold">ویرایش حساب</a>
    <form method="post" action="/shop/account/logout" class="inline">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <button class="px-5 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 text-sm font-bold">خروج</button>
    </form>
  </div>
  <?php if (empty($orders)): ?>
  <p class="text-sm text-slate-500 dark:text-slate-400">هنوز سفارشی با این حساب ثبت نشده است.</p>
  <?php else: ?>
  <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <tr class="text-right text-slate-500 border-b border-slate-100 dark:border-slate-800"><th class="py-3 px-4 font-medium">کد</th><th class="py-3 px-4 font-medium">تاریخ</th><th class="py-3 px-4 font-medium">وضعیت</th><th class="py-3 px-4 font-medium">پرداخت</th><th class="py-3 px-4 font-medium">مبلغ</th></tr>
      <?php foreach ($orders as $o): ?>
      <tr class="border-b border-slate-50 dark:border-slate-800/50">
        <td class="py-3 px-4"><a class="text-brand-600 hover:underline" dir="ltr" href="/shop/account/orders/<?= e(rawurlencode((string) $o['code'])) ?>"><?= e($o['code']) ?></a></td>
        <td class="py-3 px-4"><?= e(substr((string) $o['created_at'], 0, 10)) ?></td>
        <td class="py-3 px-4"><?= e(\Modules\Shop\Orders::STATUSES[$o['status']] ?? $o['status']) ?></td>
        <td class="py-3 px-4"><?= $o['payment_status'] === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' ?></td>
        <td class="py-3 px-4"><?= number_format((int) $o['total']) ?> <?= e($currency) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>
</div>
