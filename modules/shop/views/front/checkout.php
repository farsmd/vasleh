<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
  <?php if (!empty($error)): ?><div class="bg-rose-50 dark:bg-rose-950 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-sm rounded-2xl px-4 py-3 mb-6"><?= e($error) ?></div><?php endif; ?>
  <?php if (($orderNote ?? '') !== ''): ?><div class="bg-emerald-50 dark:bg-emerald-950 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm rounded-2xl px-4 py-3 mb-6"><?= e($orderNote) ?></div><?php endif; ?>

  <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/70 dark:border-slate-800 shadow-soft mb-8">
    <div class="grid grid-cols-3 gap-2 sm:gap-4 relative">
      <button type="button" onclick="goCheckoutStep(1)" id="co-step-1" class="flex flex-col sm:flex-row items-center justify-center gap-2.5 p-3 rounded-2xl bg-brand-50 text-brand-600 border border-brand-200 transition">
        <span class="w-8 h-8 rounded-xl bg-brand-600 text-white font-black text-xs flex items-center justify-center">۱</span>
        <span class="text-xs font-black">سبد خرید</span>
      </button>
      <button type="button" onclick="goCheckoutStep(2)" id="co-step-2" class="flex flex-col sm:flex-row items-center justify-center gap-2.5 p-3 rounded-2xl text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border border-transparent transition">
        <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 font-black text-xs flex items-center justify-center">۲</span>
        <span class="text-xs font-bold">نشانی و جزئیات</span>
      </button>
      <button type="button" onclick="goCheckoutStep(3)" id="co-step-3" class="flex flex-col sm:flex-row items-center justify-center gap-2.5 p-3 rounded-2xl text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border border-transparent transition">
        <span class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 font-black text-xs flex items-center justify-center">۳</span>
        <span class="text-xs font-bold">شیوه پرداخت</span>
      </button>
    </div>
  </div>

  <form method="post" action="/shop/checkout" id="checkout-form">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
      <div class="lg:col-span-8 space-y-6">
        <div id="co-pane-1" class="checkout-pane space-y-6">
          <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/70 dark:border-slate-800 shadow-soft space-y-4">
            <h3 class="text-base font-black text-slate-900 dark:text-white">کالاهای موجود در سبد شما</h3>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
              <?php foreach ($items as $row): ?>
              <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                  <img src="<?= e(\Modules\Shop\Catalog::displayImage($row['product'])) ?>" class="w-20 h-20 object-contain rounded-2xl bg-slate-50 dark:bg-slate-800 p-2 border border-slate-100 dark:border-slate-700">
                  <div>
                    <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white mt-0.5"><?= e($row['product']['title']) ?></h4>
                    <?php if ($row['options_text'] !== ''): ?><span class="text-xs text-slate-400"><?= e($row['options_text']) ?></span><?php endif; ?>
                    <span class="text-xs font-extrabold text-brand-600 block mt-1"><?= number_format($row['unit_price']) ?> <?= e($currency) ?> × <?= (int) $row['qty'] ?></span>
                  </div>
                </div>
                <span class="font-black text-sm"><?= number_format($row['line_total']) ?> <?= e($currency) ?></span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php if (($giftPrice ?? 0) > 0): ?>
          <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/70 dark:border-slate-800 shadow-soft">
            <div class="flex items-start justify-between gap-4">
              <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/80 text-rose-500 flex items-center justify-center text-xl flex-shrink-0">
                  <i class="fa-solid fa-ribbon"></i>
                </div>
                <div>
                  <h4 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                    بسته‌بندی کادویی لوکس + کارت‌پستال اختصاصی
                    <span class="bg-rose-500/10 text-rose-500 text-[10px] px-2 py-0.5 rounded-full font-extrabold">+<?= number_format($giftPrice) ?> <?= e($currency) ?></span>
                  </h4>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">سفارش شما داخل بسته‌بندی ویژه همراه با پیام دلخواهتان ارسال می‌شود.</p>
                </div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer mt-1">
                <input id="gift-wrap-toggle" name="gift_wrap" value="1" type="checkbox" <?= !empty($old['gift_wrap']) ? 'checked' : '' ?> onchange="toggleGiftWrap()" class="sr-only peer">
                <div class="w-12 h-6 bg-slate-200 dark:bg-slate-700 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-rose-500"></div>
              </label>
            </div>
            <div id="gift-message-box" class="<?= !empty($old['gift_wrap']) ? '' : 'hidden' ?> mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
              <input type="text" name="gift_message" value="<?= e($old['gift_message'] ?? '') ?>" placeholder="متن روی کارت پستال را بنویسید..."
                class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 py-3 text-xs outline-none focus:border-rose-500">
            </div>
          </div>
          <?php endif; ?>
        </div>

        <div id="co-pane-2" class="checkout-pane hidden space-y-6">
          <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/70 dark:border-slate-800 shadow-soft space-y-4">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
              <i class="fa-solid fa-user text-brand-600"></i> مشخصات و نشانی تحویل
            </h3>
            <div>
              <label class="text-xs font-bold text-slate-800 dark:text-slate-200 block mb-1.5" for="f-name">نام و نام خانوادگی *</label>
              <input id="f-name" type="text" name="name" required value="<?= e($old['name'] ?? '') ?>" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>
            <div>
              <label class="text-xs font-bold text-slate-800 dark:text-slate-200 block mb-1.5" for="f-phone">شماره موبایل *</label>
              <input id="f-phone" type="tel" name="phone" required dir="ltr" value="<?= e($old['phone'] ?? '') ?>" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 text-left">
            </div>
            <div>
              <label class="text-xs font-bold text-slate-800 dark:text-slate-200 block mb-1.5" for="f-email">ایمیل (اختیاری)</label>
              <input id="f-email" type="email" name="email" dir="ltr" value="<?= e($old['email'] ?? '') ?>" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 text-left">
            </div>
            <div>
              <label class="text-xs font-bold text-slate-800 dark:text-slate-200 block mb-1.5" for="f-address">نشانی ارسال</label>
              <textarea id="f-address" name="address" rows="3" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500"><?= e($old['address'] ?? '') ?></textarea>
            </div>
            <div>
              <label class="text-xs font-bold text-slate-800 dark:text-slate-200 block mb-1.5" for="f-note">یادداشت (اختیاری)</label>
              <textarea id="f-note" name="note" rows="2" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500"><?= e($old['note'] ?? '') ?></textarea>
            </div>
            <?php if (!empty($deliverySlots)): ?>
            <div>
              <label class="text-xs font-bold text-slate-800 dark:text-slate-200 block mb-1.5">بازه‌ی تحویل دلخواه</label>
              <select name="delivery_slot" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500">
                <option value="">بدون ترجیح</option>
                <?php foreach ($deliverySlots as $slot): ?><option value="<?= e($slot) ?>"<?= ($old['delivery_slot'] ?? '') === $slot ? ' selected' : '' ?>><?= e($slot) ?></option><?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div id="co-pane-3" class="checkout-pane hidden space-y-6">
          <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/70 dark:border-slate-800 shadow-soft space-y-4">
            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
              <i class="fa-solid fa-credit-card text-brand-600"></i> روش پرداخت را انتخاب کنید
            </h3>
            <?php if (empty($paymentMethods)): ?>
              <p class="text-sm text-slate-500 dark:text-slate-400">روش پرداخت از پنل فروشگاه تنظیم نشده است؛ سفارش بدون انتخاب روش ثبت می‌شود و مدیر با شما هماهنگ می‌کند.</p>
            <?php else: foreach ($paymentMethods as $i => [$mkey, $mlabel]): ?>
            <label class="pay-method-card cursor-pointer p-5 rounded-2xl border-2 <?= $i === 0 ? 'border-brand-600 bg-brand-50/40' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300' ?> flex items-center justify-between transition block">
              <span class="flex items-center gap-4">
                <span class="w-12 h-12 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-xl"><i class="fa-solid fa-wallet"></i></span>
                <span>
                  <span class="text-sm font-black text-slate-900 dark:text-white block"><?= e($mlabel) ?></span>
                  <span class="text-xs text-slate-500 dark:text-slate-400"><?= $mkey === 'zibal' ? 'پرداخت امن از طریق درگاه زیبال.' : 'پس از ثبت سفارش، مدیر فروشگاه هماهنگی پرداخت را انجام می‌دهد.' ?></span>
                </span>
              </span>
              <input type="radio" name="payment_method" value="<?= e($mkey) ?>" <?= ($old['payment_method'] ?? $paymentMethods[0][0]) === $mkey ? 'checked' : '' ?> class="accent-indigo-600 w-5 h-5">
            </label>
            <?php endforeach; endif; ?>
          </div>
        </div>
      </div>

      <aside class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/70 dark:border-slate-800 shadow-soft space-y-5 sticky top-36">
        <h3 class="text-base font-black text-slate-900 dark:text-white pb-3 border-b border-slate-100 dark:border-slate-800">خلاصه صورتحساب</h3>
        <div class="space-y-3 text-xs pt-2 border-t border-slate-100 dark:border-slate-800">
          <div class="flex justify-between text-slate-600 dark:text-slate-400">
            <span>مجموع قیمت کالاها:</span>
            <span class="font-bold text-slate-900 dark:text-white"><?= number_format($subtotal) ?> <?= e($currency) ?></span>
          </div>
          <?php if (($discount ?? 0) > 0): ?>
          <div class="flex justify-between text-emerald-600 font-bold">
            <span>تخفیف کوپن<?= $coupon !== null ? ' (' . e($coupon['code']) . ')' : '' ?>:</span>
            <span>-<?= number_format($discount) ?> <?= e($currency) ?></span>
          </div>
          <?php endif; ?>
          <div id="summary-gift-row" class="<?= !empty($old['gift_wrap']) ? 'flex' : 'hidden' ?> justify-between text-rose-500 font-semibold">
            <span>بسته‌بندی کادویی:</span>
            <span><?= number_format($giftPrice ?? 0) ?> <?= e($currency) ?></span>
          </div>
          <div class="flex justify-between text-slate-600 dark:text-slate-400">
            <span>هزینه ارسال:</span>
            <span class="font-bold text-slate-900 dark:text-white"><?= $shipping > 0 ? number_format($shipping) . ' ' . e($currency) : 'رایگان' ?></span>
          </div>
          <?php if ($tax > 0): ?>
          <div class="flex justify-between text-slate-600 dark:text-slate-400">
            <span>مالیات (<?= (int) $taxPercent ?>٪):</span>
            <span class="font-bold text-slate-900 dark:text-white"><?= number_format($tax) ?> <?= e($currency) ?></span>
          </div>
          <?php endif; ?>
        </div>
        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
          <span class="text-xs font-black text-slate-800 dark:text-slate-200">مبلغ قابل پرداخت:</span>
          <div class="text-left">
            <span id="summary-total" class="text-xl font-black text-brand-600"><?= number_format($total) ?></span>
            <span class="text-[11px] font-bold text-slate-400"><?= e($currency) ?></span>
          </div>
        </div>
        <button type="button" id="checkout-next-btn" onclick="proceedCheckoutStep()" class="w-full py-4 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-black text-sm shadow-glow transition flex items-center justify-center gap-2">
          <span id="checkout-next-btn-text">تایید و رفتن به نشانی</span>
          <i class="fa-solid fa-arrow-left text-xs"></i>
        </button>
      </aside>
    </div>
  </form>
</div>

<script>
var coStep = 1;
var coBaseTotal = <?= (int) $total ?>;
var coGiftPrice = <?= (int) ($giftPrice ?? 0) ?>;
function goCheckoutStep(n){
  coStep = n;
  for (var i = 1; i <= 3; i++) {
    var pane = document.getElementById('co-pane-' + i);
    if (pane) pane.classList.toggle('hidden', i !== n);
    var ind = document.getElementById('co-step-' + i);
    if (ind) {
      ind.className = (i <= n)
        ? 'flex flex-col sm:flex-row items-center justify-center gap-2.5 p-3 rounded-2xl bg-brand-50 text-brand-600 border border-brand-200 transition'
        : 'flex flex-col sm:flex-row items-center justify-center gap-2.5 p-3 rounded-2xl text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 border border-transparent transition';
    }
  }
  var btnText = document.getElementById('checkout-next-btn-text');
  var btn = document.getElementById('checkout-next-btn');
  if (n === 1) { btnText.textContent = 'تایید و رفتن به نشانی'; btn.type = 'button'; }
  if (n === 2) { btnText.textContent = 'تایید نشانی و رفتن به پرداخت'; btn.type = 'button'; }
  if (n === 3) { btnText.textContent = 'ثبت نهایی سفارش'; btn.type = 'submit'; }
}
function proceedCheckoutStep(){
  if (coStep === 1) {
    var name = document.getElementById('f-name');
    if (name && name.value.trim() === '') { goCheckoutStep(2); name.focus(); return; }
    goCheckoutStep(2);
  } else if (coStep === 2) {
    goCheckoutStep(3);
  }
  window.scrollTo({ top: 0, behavior: 'smooth' });
}
function toggleGiftWrap(){
  var on = document.getElementById('gift-wrap-toggle').checked;
  document.getElementById('gift-message-box').classList.toggle('hidden', !on);
  var row = document.getElementById('summary-gift-row');
  row.classList.toggle('hidden', !on);
  row.classList.toggle('flex', on);
  document.getElementById('summary-total').textContent = (coBaseTotal + (on ? coGiftPrice : 0)).toLocaleString('en-US');
}
document.getElementById('checkout-form').addEventListener('submit', function(e){
  if (coStep < 3) { e.preventDefault(); goCheckoutStep(coStep + 1); window.scrollTo({ top: 0, behavior: 'smooth' }); }
});
</script>
