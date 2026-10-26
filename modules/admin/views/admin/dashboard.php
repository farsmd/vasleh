<?php
// داشبورد مدیریت وصله — ویجت‌های شیشه‌ای با داده واقعی (بدون عدد نمایشی).
$faNum = static function (int|string $n): string {
    return strtr(number_format((int) $n), ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', ',' => '٬']);
};
$faDate = static function (string $d): string {
    return strtr(substr($d, 0, 10), ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', '-' => '/']);
};
$modules = $modules ?? [];
$errors = $errors ?? [];
$enabledCount = (int) ($enabledCount ?? 0);
$totalModules = count($modules);
$errorCount = 0;
foreach ($modules as $slug => $m) {
    if (!empty($errors[$slug])) {
        $errorCount++;
    }
}
$sales30 = $sales30 ?? ['count' => 0, 'sum' => 0];
$prod = $prod ?? ['planned' => 0, 'running' => 0, 'done' => 0];
$inv = $inv ?? ['items' => 0, 'low' => 0];
$month = $month ?? ['income' => 0, 'expense' => 0, 'net' => 0];
$chart = $chart ?? [];
$chartMax = 1;
foreach ($chart as $day) {
    $chartMax = max($chartMax, (int) $day['income'], (int) $day['expense']);
}
$chartHasData = $chartMax > 1;
$orders = $orders ?? [];
$tasks = $tasks ?? [];
$orderStatuses = $orderStatuses ?? [];
?>
<div class="space-y-8">

  <div class="glass-panel rounded-2xl p-6 sm:p-7 relative overflow-hidden">
    <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
      <div>
        <div class="flex items-center gap-2 mb-2 flex-wrap">
          <?php if ($errorCount > 0): ?>
            <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-400 text-xs font-bold border border-rose-500/30"><?= $faNum($errorCount) ?> ماژول خطا دارد</span>
          <?php else: ?>
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold border border-emerald-500/30">سیستم سالم • تمامی سرویس‌ها فعال</span>
          <?php endif; ?>
          <span class="text-slate-500 dark:text-slate-400 text-xs"><?= $faNum($enabledCount) ?> ماژول فعال از <?= $faNum($totalModules) ?></span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
          <?php if (!empty($username)): ?>سلام، <?= e($username) ?>؛<?php else: ?>سلام؛<?php endif; ?> نبض کسب‌وکار شما اینجاست
        </h1>
        <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1.5 max-w-2xl leading-relaxed">
          فروش، تولید، انبار و حسابداری در یک نگاه — همه اعداد زیر از داده واقعی همین سیستم می‌آیند.
        </p>
      </div>
      <div class="flex flex-wrap items-center gap-3 shrink-0">
        <a href="/admin/backup" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">تهیه نسخه پشتیبان</a>
        <a href="/" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:border-slate-400 dark:hover:border-slate-500 text-slate-700 dark:text-slate-200 text-xs font-bold transition-all">مشاهده وب‌سایت</a>
      </div>
    </div>
  </div>

  <?php if ($errorCount > 0): ?>
  <div class="glass-panel rounded-2xl p-5 border border-rose-500/30">
    <h3 class="text-base font-bold text-rose-500 dark:text-rose-400 mb-3">خطا در بارگذاری ماژول‌ها</h3>
    <div class="space-y-2 text-xs">
      <?php foreach ($modules as $slug => $m): ?>
        <?php if (!empty($errors[$slug])): ?>
        <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20">
          <span class="font-bold text-slate-900 dark:text-white"><?= e($m['title']) ?> (<?= e($slug) ?>)</span>
          <span class="text-slate-600 dark:text-slate-300 block mt-1"><?= e($errors[$slug]) ?></span>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <div class="glass-panel rounded-2xl p-5 glass-card-hover relative overflow-hidden">
      <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
        <span class="text-xs font-bold">فروش ۳۰ روز اخیر</span>
        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
      </div>
      <div class="flex items-baseline gap-2 mb-2">
        <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"><?= $faNum((int) $sales30['sum']) ?></span>
        <span class="text-xs text-slate-500 dark:text-slate-400">تومان</span>
      </div>
      <div class="flex items-center justify-between text-xs">
        <span class="text-emerald-500 dark:text-emerald-400 font-bold"><?= $faNum((int) $sales30['count']) ?> سفارش در ۳۰ روز</span>
        <a href="/admin/shop/orders" class="text-slate-500 dark:text-slate-400 hover:text-blue-500">سفارش‌ها</a>
      </div>
    </div>

    <div class="glass-panel rounded-2xl p-5 glass-card-hover relative overflow-hidden">
      <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
        <span class="text-xs font-bold">خط تولید فعال</span>
        <div class="w-9 h-9 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
        </div>
      </div>
      <div class="flex items-baseline gap-2 mb-2">
        <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tight"><?= $faNum((int) $prod['running']) ?></span>
        <span class="text-xs text-slate-500 dark:text-slate-400">دستور در حال تولید</span>
      </div>
      <div class="flex items-center justify-between text-xs">
        <span class="text-cyan-500 dark:text-cyan-400 font-bold"><?= $faNum((int) $prod['planned']) ?> برنامه‌ریزی‌شده</span>
        <span class="text-purple-500 dark:text-purple-400 font-medium"><?= $faNum((int) $prod['done']) ?> تمام‌شده</span>
      </div>
    </div>

    <div class="glass-panel rounded-2xl p-5 glass-card-hover relative overflow-hidden">
      <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
        <span class="text-xs font-bold">هشدار کمبود انبار</span>
        <div class="w-9 h-9 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center border border-rose-500/20">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
      </div>
      <div class="flex items-baseline gap-2 mb-2">
        <span class="text-2xl font-black <?= ((int) $inv['low'] > 0) ? 'text-rose-500 dark:text-rose-400' : 'text-slate-900 dark:text-white' ?> tracking-tight"><?= $faNum((int) $inv['low']) ?></span>
        <span class="text-xs text-slate-500 dark:text-slate-400">قلم زیر نقطه سفارش</span>
      </div>
      <div class="flex items-center justify-between text-xs">
        <span class="<?= ((int) $inv['low'] > 0) ? 'text-rose-500 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-slate-400' ?>"><?= ((int) $inv['low'] > 0) ? 'نیازمند بررسی' : 'موجودی در وضعیت خوب' ?></span>
        <a href="/admin/inventory" class="text-slate-500 dark:text-slate-400 hover:text-blue-500">انبارداری</a>
      </div>
    </div>

    <div class="glass-panel rounded-2xl p-5 glass-card-hover relative overflow-hidden">
      <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 mb-3">
        <span class="text-xs font-bold">سود خالص ماه جاری</span>
        <div class="w-9 h-9 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
      </div>
      <div class="flex items-baseline gap-2 mb-2">
        <span class="text-2xl font-black <?= ((int) $month['net'] < 0) ? 'text-rose-500 dark:text-rose-400' : 'text-emerald-500 dark:text-emerald-400' ?> tracking-tight"><?= $faNum((int) $month['net']) ?></span>
        <span class="text-xs text-slate-500 dark:text-slate-400">تومان</span>
      </div>
      <div class="flex items-center justify-between text-xs">
        <span class="text-emerald-500 dark:text-emerald-400 font-bold">درآمد <?= $faNum((int) $month['income']) ?></span>
        <span class="text-slate-500 dark:text-slate-400">هزینه <?= $faNum((int) $month['expense']) ?></span>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="lg:col-span-8 glass-panel rounded-2xl p-6 border border-slate-800">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
          <h3 class="text-base font-bold text-slate-900 dark:text-white">درآمد و هزینه ۷ روز اخیر</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">از اسناد حسابداری ثبت‌شده (میلادی)</p>
        </div>
      </div>
      <?php if (!$chartHasData): ?>
        <p class="text-xs text-slate-500 dark:text-slate-400">در ۷ روز اخیر تراکنشی ثبت نشده است.</p>
      <?php else: ?>
        <div class="h-64 flex items-end justify-between gap-2 sm:gap-4 pt-8 px-2 border-b border-slate-200 dark:border-slate-800/80">
          <?php foreach ($chart as $day): ?>
          <div class="flex-1 flex flex-col items-center gap-2">
            <div class="w-full flex items-end justify-center gap-1 h-48">
              <div style="height: <?= max(2, (int) round(((int) $day['income'] / $chartMax) * 100)) ?>%;" class="w-3 sm:w-5 bg-blue-500 rounded-t-md" title="درآمد <?= $faNum((int) $day['income']) ?>"></div>
              <div style="height: <?= max(2, (int) round(((int) $day['expense'] / $chartMax) * 100)) ?>%;" class="w-3 sm:w-5 bg-rose-500/70 rounded-t-md" title="هزینه <?= $faNum((int) $day['expense']) ?>"></div>
            </div>
            <span class="text-[11px] text-slate-500 dark:text-slate-400"><?= e($day['day']) ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="flex items-center justify-center gap-6 pt-4 text-xs">
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded bg-blue-500"></span>
            <span class="text-slate-600 dark:text-slate-300">درآمد</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded bg-rose-500/70"></span>
            <span class="text-slate-600 dark:text-slate-300">هزینه</span>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="lg:col-span-4 glass-panel rounded-2xl p-6 border border-slate-800 flex flex-col justify-between">
      <div>
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800 mb-4">
          <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">کارهای امروز من</h3>
          </div>
          <a href="/me" class="text-[10px] bg-rose-500/10 text-rose-500 dark:text-rose-300 px-2 py-0.5 rounded-full font-mono">مدیریت شخصی</a>
        </div>
        <?php if (empty($tasks)): ?>
          <p class="text-xs text-slate-500 dark:text-slate-400">برای امروز کاری ثبت نشده است.</p>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($tasks as $t): ?>
            <div class="flex items-start gap-3 p-2.5 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800/80">
              <span class="w-4 h-4 mt-0.5 rounded border border-slate-300 dark:border-slate-700 flex items-center justify-center text-[10px] <?= !empty($t['done']) ? 'bg-emerald-500 text-white border-emerald-500' : 'text-transparent' ?>">✓</span>
              <div class="text-xs">
                <span class="<?= !empty($t['done']) ? 'line-through text-slate-500' : 'text-slate-800 dark:text-slate-200 font-semibold' ?>"><?= e($t['title']) ?></span>
                <span class="text-[10px] text-slate-500 dark:text-slate-400 block"><?= ($t['hour'] ?? '') === 'all-day' ? 'تمام‌روز' : e((string) ($t['hour'] ?? '')) ?></span>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      <div class="pt-4 mt-4 border-t border-slate-200 dark:border-slate-800">
        <a href="/me" class="text-xs text-blue-500 hover:text-blue-400 font-bold">رفتن به مدیریت شخصی</a>
      </div>
    </div>
  </div>

  <div class="glass-panel rounded-2xl p-6 border border-slate-800">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
      <div>
        <h3 class="text-base font-bold text-slate-900 dark:text-white">ماژول‌های سیستم وصله</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">وضعیت فعال/غیرفعال؛ مدیریت از صفحه ماژول‌ها</p>
      </div>
      <a href="/admin/modules" class="text-xs text-blue-500 hover:text-blue-400 font-bold">مدیریت ماژول‌ها</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($modules as $slug => $m): ?>
      <a href="/admin/modules" class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800 flex items-center justify-between hover:border-blue-500/50 transition-colors">
        <div>
          <div class="text-xs font-bold text-slate-900 dark:text-white"><?= e($m['title']) ?></div>
          <div class="text-[10px] text-slate-500 dark:text-slate-400">نسخه <?= e($m['version']) ?> • <code><?= e($slug) ?></code></div>
        </div>
        <?php if (!empty($errors[$slug])): ?>
          <span class="text-[10px] bg-rose-500/10 text-rose-500 dark:text-rose-400 px-2 py-0.5 rounded font-bold">خطا</span>
        <?php elseif ($loader->isEnabled($slug)): ?>
          <span class="text-[10px] bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 px-2 py-0.5 rounded font-bold">فعال</span>
        <?php else: ?>
          <span class="text-[10px] bg-slate-500/10 text-slate-500 dark:text-slate-400 px-2 py-0.5 rounded font-bold">غیرفعال</span>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="glass-panel rounded-2xl border border-slate-800 overflow-hidden">
    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h3 class="text-base font-bold text-slate-900 dark:text-white">آخرین سفارش‌های فروشگاه</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">تازه‌ترین سفارش‌های ثبت‌شده با وضعیت واقعی</p>
      </div>
      <a href="/admin/shop/orders" class="text-xs text-blue-500 hover:text-blue-400 font-bold">همه سفارش‌ها</a>
    </div>
    <?php if (empty($orders)): ?>
      <p class="p-5 text-xs text-slate-500 dark:text-slate-400">هنوز سفارشی ثبت نشده است.</p>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-right text-xs text-slate-600 dark:text-slate-300">
          <thead class="bg-slate-50 dark:bg-slate-900/80 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="p-4">کد رهگیری</th>
              <th class="p-4">مشتری</th>
              <th class="p-4">مبلغ</th>
              <th class="p-4">وضعیت سفارش</th>
              <th class="p-4">وضعیت پرداخت</th>
              <th class="p-4">تاریخ</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
            <?php foreach ($orders as $o): ?>
            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
              <td class="p-4 font-mono font-bold text-cyan-600 dark:text-cyan-400"><?= e($o['code']) ?></td>
              <td class="p-4 font-semibold text-slate-900 dark:text-white"><?= e($o['customer_name']) ?></td>
              <td class="p-4 font-bold text-slate-900 dark:text-white"><?= $faNum((int) $o['total']) ?> ت</td>
              <td class="p-4"><?= e($orderStatuses[$o['status']] ?? $o['status']) ?></td>
              <td class="p-4"><?= ($o['payment_status'] === 'paid') ? 'پرداخت شده' : 'پرداخت نشده' ?></td>
              <td class="p-4"><?= $faDate((string) $o['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
