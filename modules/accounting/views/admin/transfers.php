<div class="card">
  <h1>انتقال بین حساب‌ها</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/accounting/transfers">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>از حساب
        <select name="from_account" required>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= (int) $a['id'] ?>"><?= e($a['title']) ?> (<?= number_format((int) $a['balance']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>به حساب
        <select name="to_account" required>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= (int) $a['id'] ?>"><?= e($a['title']) ?> (<?= number_format((int) $a['balance']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>مبلغ
        <input type="number" name="amount" min="0" required>
      </label>
      <label>تاریخ
        <input type="text" name="date" dir="ltr" required value="<?= date('Y-m-d') ?>">
      </label>
      <label>یادداشت
        <input type="text" name="note">
      </label>
    </div>
    <p><button class="btn green">ثبت انتقال</button></p>
  </form>
</div>

<div class="card">
  <?php if (empty($transfers)): ?>
    <p>هنوز انتقالی ثبت نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>تاریخ</th><th>از</th><th>به</th><th>مبلغ (<?= e($currency) ?>)</th><th>یادداشت</th><th>عملیات</th></tr>
    <?php foreach ($transfers as $tr): ?>
    <tr>
      <td><?= e($tr['date']) ?></td>
      <td><?= e($tr['from_title'] ?? '؟') ?></td>
      <td><?= e($tr['to_title'] ?? '؟') ?></td>
      <td><?= number_format((int) $tr['amount']) ?></td>
      <td class="wrap"><?= e($tr['note']) ?></td>
      <td>
        <form method="post" action="/admin/accounting/transfers/delete/<?= (int) $tr['id'] ?>" style="display:inline" onsubmit="return confirm('این انتقال حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
