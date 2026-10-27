<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-6">حساب من</h1>
  <?php if (!empty($flash)): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl px-4 py-3 mb-6 max-w-xl"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-2xl px-4 py-3 mb-6 max-w-xl"><?= e($error) ?></div><?php endif; ?>
  <div class="grid md:grid-cols-2 gap-6 max-w-4xl">
    <form method="post" action="/shop/account/otp/request" class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
      <h2 class="font-black text-slate-900 dark:text-white text-lg">ورود / ثبت‌نام با کد پیامکی</h2>
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="o-name">نام</label>
        <input id="o-name" type="text" name="name" value="<?= e($old['name'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white">
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">برای شماره‌های تازه، نام الزامی است.</p>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="o-phone">شماره موبایل</label>
        <input id="o-phone" type="tel" name="phone" dir="ltr" required value="<?= e($old['phone'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white text-left">
      </div>
      <button class="px-6 py-3 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm" type="submit">ارسال کد تأیید</button>
    </form>
    <?php if (!empty($otpPhone)): ?>
    <form method="post" action="/shop/account/otp/verify" class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
      <h2 class="font-black text-slate-900 dark:text-white text-lg">کد تأیید</h2>
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="phone" value="<?= e($otpPhone) ?>">
      <input type="hidden" name="name" value="<?= e($otpName ?? '') ?>">
      <p class="text-xs text-slate-500 dark:text-slate-400">کد ۵ رقمی پیامک‌شده به <span dir="ltr"><?= e($otpPhone) ?></span> را وارد کنید.</p>
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="v-code">کد تأیید</label>
        <input id="v-code" type="text" name="code" dir="ltr" required inputmode="numeric" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white text-left">
      </div>
      <button class="px-6 py-3 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm" type="submit">تأیید و ورود</button>
    </form>
    <?php endif; ?>
  </div>
  <p class="text-xs text-slate-500 dark:text-slate-400 mt-6">سفارش‌های قبلی که بدون ورود ثبت شده‌اند، با کد سفارش و موبایل از صفحه <a class="text-brand-600 hover:underline" href="/shop/track">پیگیری سفارش</a> قابل مشاهده‌اند.</p>
</div>
