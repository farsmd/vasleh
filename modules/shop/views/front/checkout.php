<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 mb-6">ثبت سفارش</h1>
  <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-2xl px-4 py-3 mb-6"><?= e($error) ?></div><?php endif; ?>
  <?php if (($orderNote ?? '') !== ''): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-2xl px-4 py-3 mb-6"><?= e($orderNote) ?></div><?php endif; ?>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <form method="post" action="/shop/checkout" class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="f-name">نام و نام خانوادگی *</label>
        <input id="f-name" type="text" name="name" required value="<?= e($old['name'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="f-phone">شماره موبایل *</label>
        <input id="f-phone" type="tel" name="phone" required dir="ltr" value="<?= e($old['phone'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white text-left">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="f-email">ایمیل (اختیاری)</label>
        <input id="f-email" type="email" name="email" dir="ltr" value="<?= e($old['email'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white text-left">
      </div>
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="f-address">نشانی ارسال</label>
        <textarea id="f-address" name="address" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white"><?= e($old['address'] ?? '') ?></textarea>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="f-note">یادداشت (اختیاری)</label>
        <textarea id="f-note" name="note" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white"><?= e($old['note'] ?? '') ?></textarea>
      </div>
      <button class="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg shadow-blue-500/25" type="submit">ثبت نهایی سفارش</button>
    </form>
    <div class="lg:col-span-5 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-3 text-sm">
      <h3 class="font-black text-slate-900 text-base">خلاصه سفارش</h3>
      <?php foreach ($items as $row): ?>
      <div class="flex justify-between gap-3">
        <span class="text-slate-600"><?= e($row['product']['title']) ?><?php if ($row['options_text'] !== ''): ?> <span class="text-slate-400">(<?= e($row['options_text']) ?>)</span><?php endif; ?> × <?= (int) $row['qty'] ?></span>
        <span class="font-bold shrink-0"><?= number_format($row['line_total']) ?></span>
      </div>
      <?php endforeach; ?>
      <div class="flex justify-between border-t border-slate-100 pt-3"><span class="text-slate-500">جمع</span><span class="font-bold"><?= number_format($subtotal) ?> <?= e($currency) ?></span></div>
      <div class="flex justify-between"><span class="text-slate-500">ارسال</span><span class="font-bold"><?= $shipping > 0 ? number_format($shipping) . ' ' . e($currency) : 'رایگان' ?></span></div>
      <?php if ($tax > 0): ?><div class="flex justify-between"><span class="text-slate-500">مالیات</span><span class="font-bold"><?= number_format($tax) ?> <?= e($currency) ?></span></div><?php endif; ?>
      <div class="flex justify-between border-t border-slate-100 pt-3"><span class="font-bold">قابل پرداخت</span><span class="font-black text-base"><?= number_format($total) ?> <?= e($currency) ?></span></div>
    </div>
  </div>
</div>
