<div class="card">
  <h1>گزارش‌های حسابداری</h1>
  <p><span class="btn gray">مانده کل همه حساب‌ها: <?= number_format((int) $total) ?> <?= e($currency) ?></span></p>

  <h2>خلاصه ماهانه</h2>
  <?php if (empty($monthly)): ?>
    <p>هنوز سندی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>ماه</th><th>درآمد</th><th>هزینه</th><th>خالص</th></tr>
    <?php foreach ($monthly as $m): ?>
    <tr>
      <td dir="ltr"><?= e($m['month']) ?></td>
      <td><?= number_format((int) $m['income']) ?></td>
      <td><?= number_format((int) $m['expense']) ?></td>
      <td><strong><?= number_format((int) $m['net']) ?></strong></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <h2>جمع به تفکیک دسته</h2>
  <form method="get" action="/admin/accounting/reports">
    <div class="row">
      <label>از تاریخ
        <input type="text" name="from" dir="ltr" placeholder="2026-01-01" value="<?= e($from) ?>">
      </label>
      <label>تا تاریخ
        <input type="text" name="to" dir="ltr" placeholder="2026-12-31" value="<?= e($to) ?>">
      </label>
    </div>
    <p><button class="btn gray">اعمال بازه</button></p>
  </form>
  <?php if (empty($categoryTotals)): ?>
    <p>در این بازه سندی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>نوع</th><th>دسته</th><th>تعداد سند</th><th>جمع (<?= e($currency) ?>)</th></tr>
    <?php foreach ($categoryTotals as $row): ?>
    <tr>
      <td><?= $row['kind'] === 'income' ? 'درآمد' : 'هزینه' ?></td>
      <td><?= e($row['category_title'] ?? 'بدون دسته') ?></td>
      <td><?= (int) $row['docs'] ?></td>
      <td><?= number_format((int) $row['total']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
