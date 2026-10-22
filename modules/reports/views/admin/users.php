<div class="card">
  <h1>فعالیت کاربران</h1>
  <p>هر کاربر چه کارهایی کرده است — خلاصه از روی لاگ رویدادها و بازدیدها.</p>
  <?php if (empty($summary)): ?>
    <p>هنوز فعالیتی از کاربری ثبت نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>کاربر</th><th>رویدادها</th><th>بازدیدها</th><th>آخرین فعالیت</th><th></th></tr>
    <?php foreach ($summary as $row): ?>
    <tr>
      <td><strong><?= e($row['actor']) ?></strong></td>
      <td><?= (int) $row['cnt'] ?></td>
      <td><?= (int) $row['visits'] ?></td>
      <td><?= e($row['last_at']) ?></td>
      <td><a class="btn yellow" href="/admin/reports/users?user=<?= urlencode((string) $row['actor']) ?>">جزئیات</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php if ($actor !== ''): ?>
<div class="card">
  <h3>کارهای «<?= e($actor) ?>»</h3>
  <?php if (empty($events)): ?>
    <p>رویدادی برای این کاربر نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>زمان</th><th>رویداد</th><th>هدف</th><th>IP</th></tr>
    <?php foreach ($events as $row): ?>
    <tr>
      <td style="white-space:nowrap"><?= e($row['created_at']) ?></td>
      <td><?= e($row['action']) ?></td>
      <td><?= e($row['target']) ?></td>
      <td><code><?= e($row['ip']) ?></code></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <h3>آخرین بازدیدهای «<?= e($actor) ?>»</h3>
  <?php if (empty($actorVisits)): ?>
    <p>بازدیدی ثبت نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>زمان</th><th>متد</th><th>مسیر</th><th>IP</th></tr>
    <?php foreach ($actorVisits as $row): ?>
    <tr>
      <td style="white-space:nowrap"><?= e($row['created_at']) ?></td>
      <td><code><?= e($row['action']) ?></code></td>
      <td><code><?= e($row['target']) ?></code></td>
      <td><code><?= e($row['ip']) ?></code></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
  <p><a class="btn gray" href="/admin/reports/users">برگشت به خلاصه</a></p>
</div>
<?php endif; ?>
