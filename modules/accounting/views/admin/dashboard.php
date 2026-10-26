<?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

<div class="space-y-6">
  <div class="glass-panel rounded-2xl p-6 flex flex-wrap items-center justify-between gap-4">
    <div>
      <h1 class="text-xl font-black text-slate-900 dark:text-white">داشبورد حسابداری</h1>
      <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">ماه جاری: <?= e($monthLabel) ?> — مبالغ به <?= e($currency) ?></p>
    </div>
    <div class="flex flex-wrap gap-2">
      <button type="button" onclick="document.getElementById('quickAddModal').classList.remove('hidden')" class="btn green">+ ثبت سریع سند</button>
      <a class="btn gray" href="/admin/accounting/transactions">همه اسناد</a>
      <a class="btn gray" href="/admin/accounting/reports">گزارش‌ها</a>
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">موجودی کل حساب‌ها</div>
      <div class="text-2xl font-black text-slate-900 dark:text-white mt-1"><?= number_format((int) $total) ?></div>
      <div class="text-[11px] text-slate-400 mt-1"><?= e($currency) ?></div>
    </div>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">درآمد ماه جاری</div>
      <div class="text-2xl font-black text-emerald-500 mt-1"><?= number_format((int) $month['income']) ?></div>
      <div class="text-[11px] text-slate-400 mt-1"><?= e($currency) ?></div>
    </div>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">هزینه ماه جاری</div>
      <div class="text-2xl font-black text-rose-500 mt-1"><?= number_format((int) $month['expense']) ?></div>
      <div class="text-[11px] text-slate-400 mt-1"><?= e($currency) ?></div>
    </div>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">سود خالص ماه جاری</div>
      <div class="text-2xl font-black <?= $month['net'] >= 0 ? 'text-emerald-500' : 'text-rose-500' ?> mt-1"><?= number_format((int) $month['net']) ?></div>
      <div class="text-[11px] text-slate-400 mt-1"><?= e($currency) ?></div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">جریان نقد ماهانه (درآمد در برابر هزینه)</h3>
      <?php if (empty($monthly)): ?>
      <p class="text-sm text-slate-500 dark:text-slate-400">هنوز سندی ثبت نشده است؛ نمودار پس از ثبت اولین سند پر می‌شود.</p>
      <?php else: ?>
      <div class="relative h-[300px]"><canvas id="cashflowChart"></canvas></div>
      <?php endif; ?>
    </div>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">هزینه‌ها به تفکیک دسته</h3>
      <?php if (empty($expenseByCat)): ?>
      <p class="text-sm text-slate-500 dark:text-slate-400">هنوز هزینه‌ای ثبت نشده است.</p>
      <?php else: ?>
      <div class="relative h-[250px] flex justify-center"><canvas id="expenseChart"></canvas></div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (!empty($hasShop)): ?>
  <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-1">دریافت فاکتورهای فروشگاه</h3>
    <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">سفارش‌های پرداخت‌شده و لغونشده فروشگاه، به‌عنوان درآمد با مرجع SHOP-کد سفارش وارد می‌شوند؛ مواردی که قبلاً وارد شده‌اند دوباره ثبت نمی‌شوند.</p>
    <form method="post" action="/admin/accounting/import-shop" class="flex flex-wrap items-center gap-2">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <select name="account_id" class="border rounded-lg px-3 py-2 text-sm bg-white dark:bg-slate-900">
        <option value="0">حساب مقصد را انتخاب کنید…</option>
        <?php foreach ($activeAccounts as $a): ?><option value="<?= (int) $a['id'] ?>"><?= e($a['title']) ?></option><?php endforeach; ?>
      </select>
      <button class="btn gray">دریافت فاکتورهای فروشگاه</button>
    </form>
  </div>
  <?php endif; ?>

  <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">مانده حساب‌ها</h3>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <tr class="text-right text-slate-500 border-b border-slate-100 dark:border-slate-800"><th class="py-2 pl-3 font-medium">حساب</th><th class="py-2 font-medium">نوع</th><th class="py-2 font-medium">موجودی اول دوره</th><th class="py-2 font-medium">مانده فعلی</th></tr>
        <?php foreach ($accounts as $a): ?>
        <tr class="border-b border-slate-50 dark:border-slate-800/50">
          <td class="py-2 pl-3"><?= e($a['title']) ?><?= (int) $a['active'] !== 1 ? ' <small class="text-slate-400">(غیرفعال)</small>' : '' ?></td>
          <td class="py-2"><?= e(\Modules\Accounting\Ledger::ACCOUNT_TYPES[$a['type']] ?? $a['type']) ?></td>
          <td class="py-2"><?= number_format((int) $a['opening_balance']) ?></td>
          <td class="py-2"><strong><?= number_format((int) $a['balance']) ?></strong> <?= e($currency) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><th class="py-2 pl-3 text-right">جمع کل</th><td></td><td></td><td class="py-2"><strong><?= number_format((int) $total) ?></strong> <?= e($currency) ?></td></tr>
      </table>
    </div>
  </div>

  <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">آخرین اسناد</h3>
    <?php if (empty($recent)): ?>
    <p class="text-sm text-slate-500 dark:text-slate-400">هنوز سندی ثبت نشده است.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <tr class="text-right text-slate-500 border-b border-slate-100 dark:border-slate-800"><th class="py-2 pl-3 font-medium">تاریخ</th><th class="py-2 font-medium">نوع</th><th class="py-2 font-medium">حساب</th><th class="py-2 font-medium">دسته</th><th class="py-2 font-medium">طرف حساب</th><th class="py-2 font-medium">مبلغ</th></tr>
        <?php foreach ($recent as $t): ?>
        <tr class="border-b border-slate-50 dark:border-slate-800/50">
          <td class="py-2 pl-3"><?= e($t['date']) ?></td>
          <td class="py-2"><span class="<?= $t['kind'] === 'income' ? 'text-emerald-500' : 'text-rose-500' ?>"><?= $t['kind'] === 'income' ? 'درآمد' : 'هزینه' ?></span></td>
          <td class="py-2"><?= e($t['account_title'] ?? '؟') ?></td>
          <td class="py-2"><?= e($t['category_title'] ?? '—') ?></td>
          <td class="py-2"><?= e($t['party']) ?></td>
          <td class="py-2"><?= number_format((int) $t['amount']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<div id="quickAddModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this)this.classList.add('hidden')">
  <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 w-full max-w-lg space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-base font-black text-slate-900 dark:text-white">ثبت سریع سند</h3>
      <button type="button" onclick="document.getElementById('quickAddModal').classList.add('hidden')" class="text-slate-400 text-xl leading-none">×</button>
    </div>
    <form method="post" action="/admin/accounting/transactions/create" class="space-y-3">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <div class="grid grid-cols-2 gap-3">
        <label class="text-sm">نوع
          <select name="kind" id="qaKind" class="w-full border rounded-lg px-3 py-2 mt-1 bg-white dark:bg-slate-900">
            <option value="expense">هزینه</option>
            <option value="income">درآمد</option>
          </select>
        </label>
        <label class="text-sm">مبلغ (<?= e($currency) ?>)
          <input type="number" name="amount" min="1" required class="w-full border rounded-lg px-3 py-2 mt-1 bg-white dark:bg-slate-900">
        </label>
      </div>
      <label class="text-sm block">حساب
        <select name="account_id" required class="w-full border rounded-lg px-3 py-2 mt-1 bg-white dark:bg-slate-900">
          <option value="0">انتخاب کنید…</option>
          <?php foreach ($activeAccounts as $a): ?><option value="<?= (int) $a['id'] ?>"><?= e($a['title']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="text-sm block">دسته
        <select name="category_id" id="qaCat" class="w-full border rounded-lg px-3 py-2 mt-1 bg-white dark:bg-slate-900">
          <option value="0">بدون دسته</option>
        </select>
      </label>
      <div class="grid grid-cols-2 gap-3">
        <label class="text-sm">تاریخ
          <input type="text" name="date" value="<?= date('Y-m-d') ?>" dir="ltr" class="w-full border rounded-lg px-3 py-2 mt-1 bg-white dark:bg-slate-900">
        </label>
        <label class="text-sm">طرف حساب
          <input type="text" name="party" class="w-full border rounded-lg px-3 py-2 mt-1 bg-white dark:bg-slate-900">
        </label>
      </div>
      <label class="text-sm block">شرح
        <input type="text" name="description" class="w-full border rounded-lg px-3 py-2 mt-1 bg-white dark:bg-slate-900">
      </label>
      <button class="btn green w-full">ثبت سند</button>
    </form>
  </div>
</div>

<?php if (!empty($monthly) || !empty($expenseByCat)): ?>
<script src="/assets/chartjs/chart.umd.min.js"></script>
<script>
(function(){
  var isDark = document.documentElement.classList.contains('dark');
  var textColor = isDark ? '#94a3b8' : '#475569';
  var gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
  <?php if (!empty($monthly)): ?>
  var cashflow = new Chart(document.getElementById('cashflowChart'), {
    type: 'bar',
    data: {
      labels: <?= json_encode(array_column($monthly, 'month')) ?>,
      datasets: [
        { label: 'درآمد', data: <?= json_encode(array_column($monthly, 'income')) ?>, backgroundColor: '#10b981' },
        { label: 'هزینه', data: <?= json_encode(array_column($monthly, 'expense')) ?>, backgroundColor: '#f43f5e' }
      ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: textColor } } }, scales: { y: { grid: { color: gridColor }, ticks: { color: textColor } }, x: { grid: { display: false }, ticks: { color: textColor } } } }
  });
  <?php endif; ?>
  <?php if (!empty($expenseByCat)): ?>
  var expenses = new Chart(document.getElementById('expenseChart'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_column($expenseByCat, 'category_title')) ?>,
      datasets: [{ data: <?= json_encode(array_column($expenseByCat, 'total')) ?>, backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#64748b', '#8b5cf6', '#ef4444', '#94a3b8'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { color: textColor, padding: 16 } } } }
  });
  <?php endif; ?>
  var toggle = document.getElementById('themeToggleBtn');
  if (toggle) toggle.addEventListener('click', function(){
    setTimeout(function(){
      var dark = document.documentElement.classList.contains('dark');
      var tc = dark ? '#94a3b8' : '#475569';
      var gc = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
      if (typeof cashflow !== 'undefined') { cashflow.options.scales.y.grid.color = gc; cashflow.options.scales.y.ticks.color = tc; cashflow.options.scales.x.ticks.color = tc; cashflow.options.plugins.legend.labels.color = tc; cashflow.update(); }
      if (typeof expenses !== 'undefined') { expenses.options.plugins.legend.labels.color = tc; expenses.update(); }
    }, 50);
  });
})();
</script>
<?php endif; ?>
<script>
(function(){
  var kindSel = document.getElementById('qaKind');
  var catSel = document.getElementById('qaCat');
  if (!kindSel || !catSel) return;
  var lists = {
    expense: <?= json_encode(array_map(static fn ($c) => ['id' => (int) $c['id'], 'title' => $c['title']], $expenseCats)) ?>,
    income: <?= json_encode(array_map(static fn ($c) => ['id' => (int) $c['id'], 'title' => $c['title']], $incomeCats)) ?>
  };
  function refill(){
    var list = lists[kindSel.value] || [];
    catSel.innerHTML = '<option value="0">بدون دسته</option>';
    list.forEach(function(c){
      var o = document.createElement('option');
      o.value = c.id; o.textContent = c.title;
      catSel.appendChild(o);
    });
  }
  kindSel.addEventListener('change', refill);
  refill();
})();
</script>
