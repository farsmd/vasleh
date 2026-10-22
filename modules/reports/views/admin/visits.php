<?php use Modules\Reports\Logger; ?>
<?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>

<div class="card">
  <h1>گزارش بازدیدها</h1>
  <p>بازدید روت‌ها و ویوها با IP و مشخصات بازدیدکننده. بازدید صفحات گزارشات حساب نمی‌شود. زمان‌ها UTC است.</p>
  <table>
    <tr><th>بازدید امروز</th><th>IP یکتای امروز</th><th>کل بازدیدها</th></tr>
    <tr>
      <td style="font-size:1.4em"><strong><?= (int) $stats['today'] ?></strong></td>
      <td style="font-size:1.4em"><strong><?= (int) $stats['today_ips'] ?></strong></td>
      <td style="font-size:1.4em"><strong><?= (int) $stats['total'] ?></strong></td>
    </tr>
  </table>
</div>

<div class="card">
  <h3>پرتکرارترین روت‌ها</h3>
  <?php if (empty($stats['routes'])): ?><p>هنوز بازدیدی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>مسیر</th><th>بازدید</th></tr>
    <?php foreach ($stats['routes'] as $row): ?>
    <tr><td><code><?= e($row['target']) ?></code></td><td><?= (int) $row['cnt'] ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  <h3>پرتکرارترین IPها</h3>
  <?php if (empty($stats['ips'])): ?><p>هنوز بازدیدی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>IP</th><th>بازدید</th></tr>
    <?php foreach ($stats['ips'] as $row): ?>
    <tr><td><code><?= e($row['ip']) ?></code></td><td><?= (int) $row['cnt'] ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<div class="card">
  <h3>آخرین بازدیدها</h3>
  <?php if (empty($rows)): ?>
    <p>هنوز بازدیدی ثبت نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>زمان</th><th>متد</th><th>مسیر</th><th>بازدیدکننده</th><th>IP</th><th>مرورگر / سیستم</th><th>ارجاع</th></tr>
    <?php foreach ($rows as $row): ?>
    <?php $meta = json_decode((string) ($row['meta'] ?? ''), true); ?>
    <tr>
      <td style="white-space:nowrap"><?= e($row['created_at']) ?></td>
      <td><code><?= e($row['action']) ?></code></td>
      <td><code><?= e($row['target']) ?></code></td>
      <td><?= e($row['actor']) ?></td>
      <td><code><?= e($row['ip']) ?></code></td>
      <td title="<?= e($row['ua']) ?>"><?= e(Logger::uaSummary((string) $row['ua'])) ?></td>
      <td class="wrap" style="max-width:240px;word-break:break-all"><?= e(is_array($meta) ? (string) ($meta['referer'] ?? '') : '') ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  <?php if ($pages > 1): ?>
  <p>
    <?php if ($page > 1): ?><a class="btn gray" href="/admin/reports/visits?page=<?= $page - 1 ?>">قبلی</a><?php endif; ?>
    صفحه <?= (int) $page ?> از <?= (int) $pages ?>
    <?php if ($page < $pages): ?><a class="btn gray" href="/admin/reports/visits?page=<?= $page + 1 ?>">بعدی</a><?php endif; ?>
  </p>
  <?php endif; ?>
</div>

<div class="card">
  <h3>پاک‌سازی بازدیدهای قدیمی</h3>
  <form method="post" action="/admin/reports/prune" onsubmit="return confirm('بازدیدهای قدیمی‌تر پاک شوند؟')">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>حذف بازدیدهای قدیمی‌تر از
      <select name="days">
        <option value="30">۳۰ روز</option>
        <option value="90" selected>۹۰ روز</option>
        <option value="180">۱۸۰ روز</option>
        <option value="365">یک سال</option>
      </select>
    </label>
    <button class="btn gray">پاک‌سازی</button>
  </form>
</div>
