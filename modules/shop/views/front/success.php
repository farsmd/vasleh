<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
  <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] p-8 sm:p-12 border border-slate-200/80 dark:border-slate-800 shadow-2xl text-center space-y-6">
    <div class="w-20 h-20 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center text-3xl">
      <i class="fa-solid fa-check"></i>
    </div>
    <div class="space-y-2">
      <span class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-3.5 py-1 rounded-full text-xs font-extrabold">سفارش شما ثبت شد</span>
      <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white pt-1">از خرید شما سپاسگزاریم!</h2>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
        سفارش شما ثبت شد و برای دیدن وضعیت آن، از صفحه پیگیری با همین کد و شماره موبایلتان استفاده کنید.
      </p>
    </div>
    <div class="bg-slate-50 dark:bg-slate-800/70 rounded-3xl p-5 grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs border border-slate-100 dark:border-slate-800">
      <div>
        <span class="text-slate-400 block mb-1">کد پیگیری سفارش</span>
        <span class="font-mono font-black text-sm text-brand-600" dir="ltr"><?= e($order['code']) ?></span>
      </div>
      <div>
        <span class="text-slate-400 block mb-1">مبلغ قابل پرداخت</span>
        <span class="font-extrabold text-slate-800 dark:text-white"><?= number_format((int) $order['total']) ?> <?= e($currency) ?></span>
      </div>
      <div class="col-span-2 sm:col-span-1">
        <span class="text-slate-400 block mb-1">وضعیت فعلی</span>
        <span class="text-emerald-600 font-extrabold"><i class="fa-solid fa-box-open ml-1"></i>ثبت شده</span>
      </div>
      <?php if (($order['delivery_slot'] ?? '') !== ''): ?>
      <div>
        <span class="text-slate-400 block mb-1">بازه‌ی تحویل</span>
        <span class="font-extrabold text-slate-800 dark:text-white"><?= e($order['delivery_slot']) ?></span>
      </div>
      <?php endif; ?>
      <?php if (($methodLabel ?? '') !== ''): ?>
      <div>
        <span class="text-slate-400 block mb-1">روش پرداخت</span>
        <span class="font-extrabold text-slate-800 dark:text-white"><?= e($methodLabel) ?></span>
      </div>
      <?php endif; ?>
      <?php if ((int) ($order['gift_wrap'] ?? 0) === 1): ?>
      <div>
        <span class="text-slate-400 block mb-1">بسته‌بندی</span>
        <span class="font-extrabold text-slate-800 dark:text-white">کادویی</span>
      </div>
      <?php endif; ?>
    </div>
    <div class="flex flex-wrap items-center justify-center gap-3 pt-3">
      <a href="/shop" class="px-7 py-3.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-black text-xs sm:text-sm shadow-glow transition">
        بازگشت به فروشگاه
      </a>
      <a href="/shop/track" class="px-6 py-3.5 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs sm:text-sm hover:bg-slate-200 transition">
        پیگیری سفارش
      </a>
    </div>
  </div>
</div>
<script src="/assets/confetti/confetti.browser.min.js"></script>
<script>
(function(){
  if (typeof confetti === 'function') {
    confetti({ particleCount: 90, spread: 70, origin: { y: 0.6 } });
  }
})();
</script>
