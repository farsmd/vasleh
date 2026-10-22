<div class="card">
  <h1>لاگ سیستم</h1>
  <p>تغییرات و رویدادهای سیستم و کارهای کاربران — تازه‌ترین‌ها اول. زمان‌ها UTC است. (<?= (int) $total ?> رویداد)</p>
  <p>
    <a class="btn <?= $kind === null ? '' : 'gray' ?>" href="/admin/reports">همه</a>
    <a class="btn <?= $kind === 'system' ? '' : 'gray' ?>" href="/admin/reports?kind=system">سیستم</a>
    <a class="btn <?= $kind === 'user' ? '' : 'gray' ?>" href="/admin/reports?kind=user">کاربران</a>
  </p>
  <?php if (empty($rows)): ?>
    <p>هنوز رویدادی ثبت نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>زمان</th><th>نوع</th><th>عامل</th><th>رویداد</th><th>هدف</th><th>IP</th></tr>
    <?php foreach ($rows as $row): ?>
    <tr>
      <td style="white-space:nowrap"><?= e($row['created_at']) ?></td>
      <td><?= $row['kind'] === 'system' ? 'سیستم' : 'کاربر' ?></td>
      <td><?= e($row['actor'] !== '' ? $row['actor'] : '—') ?></td>
      <td><?= e($row['action']) ?></td>
      <td><?= e($row['target']) ?></td>
      <td><code><?= e($row['ip']) ?></code></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  <?php if ($pages > 1): ?>
  <p>
    <?php if ($page > 1): ?><a class="btn gray" href="/admin/reports?<?= $kind !== null ? 'kind=' . e($kind) . '&' : '' ?>page=<?= $page - 1 ?>">قبلی</a><?php endif; ?>
    صفحه <?= (int) $page ?> از <?= (int) $pages ?>
    <?php if ($page < $pages): ?><a class="btn gray" href="/admin/reports?<?= $kind !== null ? 'kind=' . e($kind) . '&' : '' ?>page=<?= $page + 1 ?>">بعدی</a><?php endif; ?>
  </p>
  <?php endif; ?>
</div>
