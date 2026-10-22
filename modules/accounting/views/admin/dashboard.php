<div class="card">
  <h1>حسابداری</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    <a class="btn green" href="/admin/accounting/transactions/create">+ سند تازه</a>
    <a class="btn gray" href="/admin/accounting/transactions">همه اسناد</a>
    <a class="btn gray" href="/admin/accounting/reports">گزارش‌ها</a>
  </p>
  <h2>مانده حساب‌ها</h2>
  <table>
    <tr><th>حساب</th><th>نوع</th><th>موجودی اول دوره</th><th>مانده فعلی</th></tr>
    <?php foreach ($accounts as $a): ?>
    <tr>
      <td><?= e($a['title']) ?><?= (int) $a['active'] !== 1 ? ' <small class="muted">(غیرفعال)</small>' : '' ?></td>
      <td><?= e(\Modules\Accounting\Ledger::ACCOUNT_TYPES[$a['type']] ?? $a['type']) ?></td>
      <td><?= number_format((int) $a['opening_balance']) ?></td>
      <td><strong><?= number_format((int) $a['balance']) ?></strong> <?= e($currency) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr><th colspan="3">جمع کل</th><td><strong><?= number_format((int) $total) ?></strong> <?= e($currency) ?></td></tr>
  </table>

  <h2>این ماه (<?= e($monthLabel) ?>)</h2>
  <p>
    <span class="btn gray">درآمد: <?= number_format((int) $month['income']) ?> <?= e($currency) ?></span>
    <span class="btn gray">هزینه: <?= number_format((int) $month['expense']) ?> <?= e($currency) ?></span>
    <span class="btn <?= $month['net'] >= 0 ? 'green' : 'red' ?>">خالص: <?= number_format((int) $month['net']) ?> <?= e($currency) ?></span>
  </p>

  <h2>آخرین اسناد</h2>
  <?php if (empty($recent)): ?>
    <p>هنوز سندی ثبت نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>تاریخ</th><th>نوع</th><th>حساب</th><th>دسته</th><th>طرف حساب</th><th>مبلغ</th></tr>
    <?php foreach ($recent as $t): ?>
    <tr>
      <td><?= e($t['date']) ?></td>
      <td><?= $t['kind'] === 'income' ? 'درآمد' : 'هزینه' ?></td>
      <td><?= e($t['account_title'] ?? '؟') ?></td>
      <td><?= e($t['category_title'] ?? '—') ?></td>
      <td class="wrap"><?= e($t['party']) ?></td>
      <td><?= number_format((int) $t['amount']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
