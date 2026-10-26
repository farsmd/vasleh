<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 mb-6">سبد خرید</h1>
  <?php if (empty($items)): ?>
    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm text-center">
      <p class="text-sm text-slate-500">سبد خرید خالی است.</p>
      <p class="mt-4"><a href="/shop" class="px-5 py-3 rounded-2xl bg-blue-600 text-white text-sm font-bold">رفتن به فروشگاه</a></p>
    </div>
  <?php else: ?>
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <div class="lg:col-span-8 space-y-4">
      <?php foreach ($items as $row): ?>
      <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-sm flex flex-wrap items-center gap-4">
        <div class="w-20 h-20 rounded-2xl bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
          <?php if (($row['product']['image'] ?? '') !== ''): ?><img src="<?= e($row['product']['image']) ?>" alt="" class="object-contain h-16">
          <?php else: ?><span class="text-slate-300 text-2xl"><i class="fa-regular fa-image"></i></span><?php endif; ?>
        </div>
        <div class="flex-1 min-w-[12rem]">
          <a href="/shop/product/<?= e(rawurlencode((string) $row['product']['slug'])) ?>" class="text-sm font-bold text-slate-800 hover:text-blue-600"><?= e($row['product']['title']) ?></a>
          <?php if ($row['options_text'] !== ''): ?><div class="text-xs text-slate-400 mt-0.5"><?= e($row['options_text']) ?></div><?php endif; ?>
          <?php if ($row['stock'] !== null): ?><div class="text-xs text-slate-400">موجودی: <?= (int) $row['stock'] ?></div><?php endif; ?>
          <div class="text-xs text-slate-500 mt-1"><?= number_format($row['unit_price']) ?> <?= e($currency) ?> × <?= (int) $row['qty'] ?></div>
        </div>
        <form method="post" action="/shop/cart/update" class="flex items-center border border-slate-200 rounded-2xl p-1">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="key" value="<?= e($row['key']) ?>">
          <input type="number" name="qty" value="<?= (int) $row['qty'] ?>" min="0" class="w-14 text-center font-bold text-sm outline-none border-0">
          <button class="px-3 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold" type="submit">به‌روزرسانی</button>
        </form>
        <div class="text-base font-black text-slate-900 w-32 text-left"><?= number_format($row['line_total']) ?> <?= e($currency) ?></div>
        <form method="post" action="/shop/cart/remove">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="key" value="<?= e($row['key']) ?>">
          <button class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-100 flex items-center justify-center" type="submit" title="حذف"><i class="fa-solid fa-trash text-xs"></i></button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-100 shadow-sm space-y-3 text-sm">
      <h3 class="font-black text-slate-900 text-base">خلاصه سفارش</h3>
      <div class="flex justify-between"><span class="text-slate-500">جمع ردیف‌ها</span><span class="font-bold"><?= number_format($subtotal) ?> <?= e($currency) ?></span></div>
      <div class="flex justify-between"><span class="text-slate-500">هزینه ارسال</span><span class="font-bold"><?= $shipping > 0 ? number_format($shipping) . ' ' . e($currency) : 'رایگان' ?></span></div>
      <?php if ($tax > 0): ?><div class="flex justify-between"><span class="text-slate-500">مالیات (<?= (int) $taxPercent ?>٪)</span><span class="font-bold"><?= number_format($tax) ?> <?= e($currency) ?></span></div><?php endif; ?>
      <div class="flex justify-between border-t border-slate-100 pt-3"><span class="font-bold">قابل پرداخت</span><span class="font-black text-base"><?= number_format($total) ?> <?= e($currency) ?></span></div>
      <p class="pt-2 flex flex-col gap-3">
        <a href="/shop/checkout" class="w-full py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm text-center">ثبت سفارش و تسویه</a>
        <a href="/shop" class="w-full py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm text-center">ادامه خرید</a>
      </p>
    </div>
  </div>
  <?php endif; ?>
</div>
