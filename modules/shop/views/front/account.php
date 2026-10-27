<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-6">حساب من</h1>
  <?php if (!empty($flash)): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm rounded-2xl px-4 py-3 mb-6 max-w-xl"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-2xl px-4 py-3 mb-6 max-w-xl"><?= e($error) ?></div><?php endif; ?>
  <div class="grid md:grid-cols-2 gap-6 max-w-4xl">
    <div id="otp-step1"<?php if (!empty($otpPhone)): ?> style="display:none"<?php endif; ?>>
    <form id="otp-request-form" method="post" action="/shop/account/otp/request" class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
      <h2 class="font-black text-slate-900 dark:text-white text-lg">ورود / ثبت‌نام با کد پیامکی</h2>
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="o-name">نام</label>
        <input id="o-name" type="text" name="name" value="<?= e($old['name'] ?? '') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white">
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5">برای شماره‌های تازه، نام الزامی است.</p>
      </div>
      <div>
        <label class="text-xs font-bold text-slate-800 block mb-1.5" for="o-phone">شماره موبایل</label>
        <div class="flex items-center gap-2" dir="ltr">
          <span class="px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-500">+98</span>
          <input id="o-phone" type="tel" name="phone" required placeholder="9123456789" value="<?= e(isset($old['phone']) && strlen($old['phone']) === 11 && substr($old['phone'], 0, 2) === '09' ? substr($old['phone'], 2) : ($old['phone'] ?? '')) ?>" class="flex-1 bg-slate-50 border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500 focus:bg-white text-left">
        </div>
      </div>
      <button class="px-6 py-3 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm" type="submit">ارسال کد تأیید</button>
    </form>
    </div>
    <?php if (!empty($otpPhone)): ?>
    <div id="otp-step2">
    <form id="otp-verify-form" method="post" action="/shop/account/otp/verify" class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
      <h2 class="font-black text-slate-900 dark:text-white text-lg">کد تأیید</h2>
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="phone" value="<?= e($otpPhone) ?>">
      <input type="hidden" name="name" value="<?= e($otpName ?? '') ?>">
      <input type="hidden" name="code" id="otp-code" value="">
      <p class="text-xs text-slate-500 dark:text-slate-400">کد ۵ رقمی پیامک‌شده به <span dir="ltr"><?= e($otpPhone) ?></span> را وارد کنید.</p>
      <div>
        <style>.otp-box{width:3rem;height:3.5rem;font-size:1.25rem}</style>
        <div class="flex gap-2 justify-center" dir="ltr">
          <input class="otp-box bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-brand-500 text-center font-extrabold" data-idx="0" type="text" maxlength="1" inputmode="numeric" autocomplete="one-time-code">
          <input class="otp-box bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-brand-500 text-center font-extrabold" data-idx="1" type="text" maxlength="1" inputmode="numeric">
          <input class="otp-box bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-brand-500 text-center font-extrabold" data-idx="2" type="text" maxlength="1" inputmode="numeric">
          <input class="otp-box bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-brand-500 text-center font-extrabold" data-idx="3" type="text" maxlength="1" inputmode="numeric">
          <input class="otp-box bg-slate-50 border border-slate-200 rounded-2xl outline-none focus:border-brand-500 text-center font-extrabold" data-idx="4" type="text" maxlength="1" inputmode="numeric">
        </div>
      </div>
      <p id="otp-timer-wrap" data-fresh="<?= !empty($otpFresh) ? '1' : '0' ?>" class="text-xs text-slate-500 dark:text-slate-400">ارسال مجدد کد تا <span id="otp-timer">۰۲:۰۰</span></p>
      <div class="flex gap-2">
        <button class="px-6 py-3 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm" type="submit">تأیید و ورود</button>
        <button class="px-4 py-3 rounded-2xl border border-slate-200 text-slate-500 font-bold text-sm" type="button" id="otp-edit">ویرایش شماره</button>
      </div>
    </form>
    <form id="otp-resend" method="post" action="/shop/account/otp/request" style="display:none" class="mt-4">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="phone" value="<?= e($otpPhone) ?>">
      <input type="hidden" name="name" value="<?= e($otpName ?? '') ?>">
      <button class="px-6 py-3 rounded-2xl bg-slate-900 text-white font-bold text-sm" type="submit">ارسال مجدد پیامک کد</button>
    </form>
    </div>
    <?php endif; ?>
  </div>
  <p class="text-xs text-slate-500 dark:text-slate-400 mt-6">سفارش‌های قبلی که بدون ورود ثبت شده‌اند، با کد سفارش و موبایل از صفحه <a class="text-brand-600 hover:underline" href="/shop/track">پیگیری سفارش</a> قابل مشاهده‌اند.</p>
</div>
<script>
(function () {
  var norm = function (s) { return String(s).replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); }).replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); }).replace(/[^0-9]/g, ''); };
  var req = document.getElementById('otp-request-form');
  if (req) { req.addEventListener('submit', function () { var p = document.getElementById('o-phone'); if (!p) return; var v = norm(p.value); if (v.indexOf('98') === 0 && v.length === 12) v = '0' + v.slice(2); else if (v.charAt(0) === '9' && v.length === 10) v = '0' + v; p.value = v; }); }
})();
</script>
<?php if (!empty($otpPhone)): ?>
<script>
(function () {
  var fa = function (s) { return String(s).replace(/[0-9]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'[+d]; }); };
  var norm = function (s) { return String(s).replace(/[۰-۹]/g, function (d) { return '۰۱۲۳۴۵۶۷۸۹'.indexOf(d); }).replace(/[٠-٩]/g, function (d) { return '٠١٢٣٤٥٦٧٨٩'.indexOf(d); }).replace(/[^0-9]/g, ''); };
  var boxes = [];
  for (var i = 0; i < 5; i++) { var b = document.querySelector('[data-idx="' + i + '"]'); if (b) boxes.push(b); }
  var hidden = document.getElementById('otp-code');
  var collect = function () { if (hidden) hidden.value = boxes.map(function (x) { return x.value; }).join(''); };
  boxes.forEach(function (box, i) {
    box.addEventListener('input', function () { var v = norm(box.value); box.value = v ? v.slice(-1) : ''; collect(); if (box.value && i < boxes.length - 1) { boxes[i + 1].focus(); boxes[i + 1].select(); } });
    box.addEventListener('keydown', function (e) { if (e.key === 'Backspace' && !box.value && i > 0) { e.preventDefault(); boxes[i - 1].focus(); boxes[i - 1].select(); } });
    box.addEventListener('paste', function (e) { e.preventDefault(); var t = norm((e.clipboardData || window.clipboardData).getData('text')); for (var j = i; j < boxes.length && j - i < t.length; j++) { boxes[j].value = t.charAt(j - i); } collect(); var next = Math.min(i + t.length, boxes.length - 1); boxes[next].focus(); boxes[next].select(); });
    box.addEventListener('focus', function () { box.select(); });
  });
  var vf = document.getElementById('otp-verify-form');
  if (vf) { vf.addEventListener('submit', collect); }
  var edit = document.getElementById('otp-edit');
  if (edit) { edit.addEventListener('click', function () { var s1 = document.getElementById('otp-step1'); var s2 = document.getElementById('otp-step2'); if (s1) s1.style.display = ''; if (s2) s2.style.display = 'none'; }); }
  var timerEl = document.getElementById('otp-timer');
  var wrap = document.getElementById('otp-timer-wrap');
  var resend = document.getElementById('otp-resend');
  var phoneInput = vf ? vf.querySelector('input[name="phone"]') : null;
  var phone = phoneInput ? phoneInput.value : '';
  var fresh = wrap && wrap.getAttribute('data-fresh') === '1';
  if (timerEl && phone) {
    var key = 'vasleh-otp-' + phone;
    var deadline = 0;
    try { deadline = parseInt(localStorage.getItem(key) || '0', 10) || 0; } catch (e) {}
    if (fresh || !deadline || deadline <= Date.now()) { deadline = Date.now() + 120000; try { localStorage.setItem(key, String(deadline)); } catch (e) {} }
    var render = function () { var r = Math.max(0, deadline - Date.now()); var m = Math.floor(r / 60000); var s = Math.floor((r % 60000) / 1000); timerEl.textContent = fa((m < 10 ? '0' : '') + m) + ':' + fa((s < 10 ? '0' : '') + s); if (r <= 0) { if (wrap) wrap.style.display = 'none'; if (resend) resend.style.display = ''; return true; } return false; };
    if (!render()) { var iv = setInterval(function () { if (render()) clearInterval(iv); }, 1000); }
  }
})();
</script>
<?php endif; ?>
