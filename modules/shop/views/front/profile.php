<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-6">ویرایش حساب</h1>
  <?php if (!empty($flash)): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl px-4 py-3 mb-6 max-w-xl"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-2xl px-4 py-3 mb-6 max-w-xl"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/shop/account/profile" class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4 max-w-xl">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div>
      <label class="text-xs font-bold text-slate-800 block mb-1.5" for="p-name">نام</label>
      <input id="p-name" type="text" name="name" required value="<?= e($customer['name']) ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-800 block mb-1.5" for="p-phone">شماره موبایل (غیرقابل تغییر)</label>
      <input id="p-phone" type="tel" dir="ltr" disabled value="<?= e($customer['phone']) ?>" class="w-full bg-slate-100 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm text-slate-500 text-left">
    </div>
    <div>
      <label class="text-xs font-bold text-slate-800 block mb-1.5" for="p-addr">آدرس پیش‌فرض (در تسویه از قبل پر می‌شود)</label>
      <textarea id="p-addr" name="address" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white"><?= e($customer['address']) ?></textarea>
    </div>
    <button class="px-6 py-3 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm" type="submit">ذخیره تغییرات</button>
    <a href="/shop/account/orders" class="text-brand-600 hover:underline text-sm mr-3">سفارش‌های من</a>
  </form>
</div>
