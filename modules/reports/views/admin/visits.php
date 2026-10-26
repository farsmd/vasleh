<?php use Modules\Reports\Logger;
$maskIp = static function (string $ip): string {
    $parts = explode('.', $ip);
    if (count($parts) === 4) {
        return $parts[0] . '.' . $parts[1] . '.x.x';
    }
    return $ip;
};
$qs = 'range=' . (int) $range . ($q !== '' ? '&q=' . rawurlencode($q) : '') . ($os !== '' ? '&os=' . rawurlencode($os) : '');
?>
<?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>

<div class="space-y-6">
  <div class="glass-panel rounded-2xl p-6">
    <h1 class="text-xl font-black text-slate-900 dark:text-white">گزارش بازدیدها</h1>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">بازدید روت‌ها و ویوها با IP و مشخصات بازدیدکننده. بازدید صفحات گزارشات حساب نمی‌شود. زمان‌ها UTC است.</p>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">فعال در ۵ دقیقه گذشته</div>
      <div class="text-2xl font-black text-emerald-500 mt-1 flex items-center gap-2"><span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span></span><?= (int) $active5 ?></div>
      <div class="text-[11px] text-slate-400 mt-1">IP یکتا — نه «آنلاین لحظه‌ای»</div>
    </div>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">بازدید امروز</div>
      <div class="text-2xl font-black text-slate-900 dark:text-white mt-1"><?= number_format((int) $stats['today']) ?></div>
    </div>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">IP یکتای امروز</div>
      <div class="text-2xl font-black text-slate-900 dark:text-white mt-1"><?= number_format((int) $stats['today_ips']) ?></div>
    </div>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">کل بازدیدها</div>
      <div class="text-2xl font-black text-slate-900 dark:text-white mt-1"><?= number_format((int) $stats['total']) ?></div>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">روند ترافیک ورودی (<?= (int) $range ?> روز گذشته)</h3>
      <div class="relative h-[300px]"><canvas id="trafficChart"></canvas></div>
    </div>
    <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
      <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">توزیع مرورگرها</h3>
      <div class="relative h-[250px] flex justify-center"><canvas id="browserChart"></canvas></div>
    </div>
  </div>

  <?php if (!empty($stats['routes'])): ?>
  <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">پرتکرارترین روت‌ها</h3>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <tr class="text-right text-slate-500 border-b border-slate-100 dark:border-slate-800"><th class="py-2 pl-3 font-medium">مسیر</th><th class="py-2 font-medium">بازدید</th></tr>
        <?php foreach ($stats['routes'] as $row): ?>
        <tr class="border-b border-slate-50 dark:border-slate-800/50"><td class="py-2 pl-3"><code><?= e($row['target']) ?></code></td><td class="py-2"><?= (int) $row['cnt'] ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
      <h3 class="text-sm font-bold text-slate-900 dark:text-white">بازدیدهای اخیر</h3>
      <form method="get" action="/admin/reports/visits" class="flex flex-wrap items-center gap-2">
        <select name="range" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none">
          <option value="7"<?= $range === 7 ? ' selected' : '' ?>>۷ روز گذشته</option>
          <option value="30"<?= $range === 30 ? ' selected' : '' ?>>۳۰ روز گذشته</option>
          <option value="90"<?= $range === 90 ? ' selected' : '' ?>>۹۰ روز گذشته</option>
        </select>
        <input type="text" name="q" value="<?= e($q) ?>" placeholder="جستجوی IP..." class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none w-40">
        <select name="os" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none">
          <option value="">همه سیستم‌عامل‌ها</option>
          <option value="windows"<?= $os === 'windows' ? ' selected' : '' ?>>Windows</option>
          <option value="macos"<?= $os === 'macos' ? ' selected' : '' ?>>macOS</option>
          <option value="android"<?= $os === 'android' ? ' selected' : '' ?>>Android</option>
          <option value="ios"<?= $os === 'ios' ? ' selected' : '' ?>>iOS</option>
          <option value="linux"<?= $os === 'linux' ? ' selected' : '' ?>>Linux</option>
        </select>
        <button class="px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-bold">اعمال فیلتر</button>
      </form>
    </div>
    <?php if (empty($rows)): ?>
      <p class="text-sm text-slate-500">در این بازه بازدیدی ثبت نشده است.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <tr class="text-right text-slate-500 border-b border-slate-100 dark:border-slate-800">
          <th class="py-2 pl-3 font-medium whitespace-nowrap">زمان</th>
          <th class="py-2 pl-3 font-medium">متد</th>
          <th class="py-2 pl-3 font-medium">مسیر</th>
          <th class="py-2 pl-3 font-medium">بازدیدکننده</th>
          <th class="py-2 pl-3 font-medium">IP</th>
          <th class="py-2 pl-3 font-medium">مرورگر / سیستم</th>
          <th class="py-2 font-medium">ارجاع</th>
        </tr>
        <?php foreach ($rows as $row): $meta = json_decode((string) ($row['meta'] ?? ''), true); ?>
        <tr class="border-b border-slate-50 dark:border-slate-800/50 hover:bg-slate-50 dark:hover:bg-slate-800/30">
          <td class="py-2 pl-3 whitespace-nowrap"><?= e($row['created_at']) ?></td>
          <td class="py-2 pl-3"><code><?= e($row['action']) ?></code></td>
          <td class="py-2 pl-3"><code><?= e($row['target']) ?></code></td>
          <td class="py-2 pl-3"><?= e($row['actor']) ?></td>
          <td class="py-2 pl-3"><code title="<?= e($row['ip']) ?>"><?= e($maskIp((string) $row['ip'])) ?></code></td>
          <td class="py-2 pl-3" title="<?= e($row['ua']) ?>"><?= e(Logger::uaSummary((string) $row['ua'])) ?></td>
          <td class="py-2 max-w-[240px] break-all text-slate-500"><?= e(is_array($meta) ? (string) ($meta['referer'] ?? '') : '') ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>
    <?php if ($pages > 1): ?>
    <p class="mt-4 text-sm">
      <?php if ($page > 1): ?><a class="btn gray" href="/admin/reports/visits?<?= $qs ?>&page=<?= $page - 1 ?>">قبلی</a><?php endif; ?>
      صفحه <?= (int) $page ?> از <?= (int) $pages ?>
      <?php if ($page < $pages): ?><a class="btn gray" href="/admin/reports/visits?<?= $qs ?>&page=<?= $page + 1 ?>">بعدی</a><?php endif; ?>
    </p>
    <?php endif; ?>
  </div>

  <div class="p-4 rounded-xl bg-white dark:bg-slate-900/70 border border-slate-200 dark:border-slate-800">
    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">پاک‌سازی بازدیدهای قدیمی</h3>
    <form method="post" action="/admin/reports/prune" onsubmit="return confirm('بازدیدهای قدیمی‌تر پاک شوند؟')">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <label class="text-sm">حذف بازدیدهای قدیمی‌تر از
        <select name="days" class="bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs outline-none">
          <option value="30">۳۰ روز</option>
          <option value="90" selected>۹۰ روز</option>
          <option value="180">۱۸۰ روز</option>
          <option value="365">یک سال</option>
        </select>
      </label>
      <button class="btn gray">پاک‌سازی</button>
    </form>
  </div>
</div>

<script src="/assets/chartjs/chart.umd.min.js"></script>
<script>
(function(){
  var isDark = document.documentElement.classList.contains('dark');
  var textColor = isDark ? '#94a3b8' : '#475569';
  var gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
  var pointBorder = isDark ? '#0f172a' : '#ffffff';
  var traffic = new Chart(document.getElementById('trafficChart'), {
    type: 'line',
    data: {
      labels: <?= json_encode(array_column($daily, 'date')) ?>,
      datasets: [{ label: 'بازدید', data: <?= json_encode(array_column($daily, 'count')) ?>, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', borderWidth: 2, tension: 0.4, fill: true, pointBackgroundColor: '#3b82f6', pointBorderColor: pointBorder, pointRadius: 3 }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { grid: { color: gridColor }, ticks: { color: textColor } }, x: { grid: { display: false }, ticks: { color: textColor, maxTicksLimit: 10 } } } }
  });
  var browsers = new Chart(document.getElementById('browserChart'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_column($browsers, 'browser')) ?>,
      datasets: [{ data: <?= json_encode(array_column($browsers, 'cnt')) ?>, backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#64748b', '#8b5cf6', '#ef4444', '#94a3b8'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '75%', plugins: { legend: { position: 'bottom', labels: { color: textColor, padding: 16 } } } }
  });
  var toggle = document.getElementById('themeToggleBtn');
  if (toggle) toggle.addEventListener('click', function(){
    setTimeout(function(){
      var dark = document.documentElement.classList.contains('dark');
      var tc = dark ? '#94a3b8' : '#475569';
      var gc = dark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
      traffic.options.scales.y.grid.color = gc;
      traffic.options.scales.y.ticks.color = tc;
      traffic.options.scales.x.ticks.color = tc;
      traffic.data.datasets[0].pointBorderColor = dark ? '#0f172a' : '#ffffff';
      traffic.update();
      browsers.options.plugins.legend.labels.color = tc;
      browsers.update();
    }, 50);
  });
})();
</script>
