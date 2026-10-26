<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-6">سبد خرید</h1>
  <?php if (!empty($flash)): ?><div class="bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm rounded-2xl px-4 py-3 mb-6"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="bg-rose-50 dark:bg-rose-950 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm rounded-2xl px-4 py-3 mb-6"><?= e($error) ?></div><?php endif; ?>
  <?php if (empty($items)): ?>
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-100 dark:border-slate-800 shadow-sm text-center">
      <p class="text-sm text-slate-500 dark:text-slate-400">سبد خرید خالی است.</p>
      <p class="mt-4"><a href="/shop" class="px-5 py-3 rounded-2xl bg-brand-600 text-white text-sm font-bold">رفتن به فروشگاه</a></p>
    </div>
  <?php else: ?>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <div class="lg:col-span-8 space-y-4">
      <?php if (($freeOver ?? 0) > 0): $pct = min(100, (int) round($subtotal * 100 / $freeOver)); ?>
      <div class="bg-gradient-to-l from-emerald-500/10 via-teal-500/10 to-brand-500/10 border border-emerald-500/30 rounded-3xl p-5">
        <div class="flex items-center justify-between text-xs font-extrabold mb-2">
          <span class="text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
            <i class="fa-solid fa-truck-fast text-base"></i>
            <?= $subtotal >= $freeOver ? 'تبریک! سفارش شما شامل ارسال رایگان شد.' : 'تا ارسال رایگان: ' . number_format($freeOver - $subtotal) . ' ' . e($currency) . ' دیگر' ?>
          </span>
          <span class="text-emerald-600 font-black"><?= $pct ?>٪</span>
        </div>
        <div class="w-full h-2.5 bg-white dark:bg-slate-800 rounded-full overflow-hidden">
          <div class="h-full bg-gradient-to-l from-emerald-500 to-teal-400 rounded-full transition-all duration-500" style="width: <?= $pct ?>%"></div>
        </div>
      </div>
      <?php endif; ?>
      <?php foreach ($items as $row): ?>
      <div class="bg-white dark:bg-slate-900 rounded-3xl p-4 border border-slate-100 dark:border-slate-800 shadow-sm flex flex-wrap items-center gap-4">
        <div class="w-20 h-20 rounded-2xl bg-slate-50 dark:bg-slate-800 flex items-center justify-center overflow-hidden shrink-0">
          <img src="<?= e(\Modules\Shop\Catalog::displayImage($row['product'])) ?>" alt="" class="object-contain h-16">
        </div>
        <div class="flex-1 min-w-[12rem]">
          <a href="/shop/product/<?= e(rawurlencode((string) $row['product']['slug'])) ?>" class="text-sm font-bold text-slate-800 dark:text-slate-100 hover:text-brand-600"><?= e($row['product']['title']) ?></a>
          <?php if ($row['options_text'] !== ''): ?><div class="text-xs text-slate-400 mt-0.5"><?= e($row['options_text']) ?></div><?php endif; ?>
          <?php if ($row['stock'] !== null): ?><div class="text-xs text-slate-400">موجودی: <?= (int) $row['stock'] ?></div><?php endif; ?>
          <div class="text-xs text-slate-500 dark:text-slate-400 mt-1"><?= number_format($row['unit_price']) ?> <?= e($currency) ?> × <?= (int) $row['qty'] ?></div>
        </div>
        <form method="post" action="/shop/cart/update" class="flex items-center border border-slate-200 dark:border-slate-700 rounded-2xl p-1">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="key" value="<?= e($row['key']) ?>">
          <input type="number" name="qty" value="<?= (int) $row['qty'] ?>" min="0" class="w-14 text-center font-bold text-sm outline-none border-0 bg-transparent">
          <button class="px-3 py-2 rounded-xl bg-slate-900 dark:bg-brand-600 text-white text-xs font-bold" type="submit">به‌روزرسانی</button>
        </form>
        <div class="text-base font-black text-slate-900 dark:text-white w-32 text-left"><?= number_format($row['line_total']) ?> <?= e($currency) ?></div>
        <form method="post" action="/shop/cart/remove">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="key" value="<?= e($row['key']) ?>">
          <button class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950 text-rose-500 hover:bg-rose-100 flex items-center justify-center" type="submit" title="حذف"><i class="fa-solid fa-trash text-xs"></i></button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-3 text-sm">
      <h3 class="font-black text-slate-900 dark:text-white text-base">خلاصه سفارش</h3>
      <form method="post" action="/shop/coupon/apply" class="flex gap-2">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="text" name="code" value="<?= $coupon !== null ? e($coupon['code']) : '' ?>" placeholder="کد تخفیف" class="flex-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-bold uppercase outline-none focus:border-brand-500">
        <button class="px-4 py-2.5 rounded-xl bg-slate-900 dark:bg-slate-700 text-white text-xs font-extrabold hover:bg-brand-600 transition">اعمال</button>
      </form>
      <?php if ($coupon !== null): ?>
      <form method="post" action="/shop/coupon/remove" class="flex items-center justify-between text-emerald-600 font-bold">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <span>کوپن «<?= e($coupon['code']) ?>» فعال است</span>
        <button class="text-rose-500 text-xs">حذف</button>
      </form>
      <?php endif; ?>
      <div class="flex justify-between pt-2 border-t border-slate-100 dark:border-slate-800"><span class="text-slate-500 dark:text-slate-400">جمع ردیف‌ها</span><span class="font-bold"><?= number_format($subtotal) ?> <?= e($currency) ?></span></div>
      <?php if (($discount ?? 0) > 0): ?><div class="flex justify-between text-emerald-600 font-bold"><span>تخفیف کوپن</span><span>-<?= number_format($discount) ?> <?= e($currency) ?></span></div><?php endif; ?>
      <div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">هزینه ارسال</span><span class="font-bold"><?= $shipping > 0 ? number_format($shipping) . ' ' . e($currency) : 'رایگان' ?></span></div>
      <?php if ($tax > 0): ?><div class="flex justify-between"><span class="text-slate-500 dark:text-slate-400">مالیات (<?= (int) $taxPercent ?>٪)</span><span class="font-bold"><?= number_format($tax) ?> <?= e($currency) ?></span></div><?php endif; ?>
      <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-3"><span class="font-bold">قابل پرداخت</span><span class="font-black text-base"><?= number_format($total) ?> <?= e($currency) ?></span></div>
      <p class="pt-2 flex flex-col gap-3">
        <a href="/shop/checkout" class="w-full py-3.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm text-center">ثبت سفارش و تسویه</a>
        <a href="/shop" class="w-full py-3.5 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 font-bold text-sm text-center">ادامه خرید</a>
      </p>
    </div>
  </div>
  <?php endif; ?>
</div>
