<div class="card">
  <h1>اسناد حسابداری</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p><a class="btn green" href="/admin/accounting/transactions/create">+ سند تازه</a></p>

  <form method="get" action="/admin/accounting/transactions">
    <div class="row">
      <label>نوع
        <select name="kind">
          <option value="">همه</option>
          <option value="income" <?= $filters['kind'] === 'income' ? 'selected' : '' ?>>درآمد</option>
          <option value="expense" <?= $filters['kind'] === 'expense' ? 'selected' : '' ?>>هزینه</option>
        </select>
      </label>
      <label>حساب
        <select name="account">
          <option value="0">همه</option>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= (int) $a['id'] ?>" <?= $filters['account_id'] === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>دسته
        <select name="category">
          <option value="0">همه</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= $filters['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?> (<?= $c['kind'] === 'income' ? 'درآمد' : 'هزینه' ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>ماه (مثل 2026-10)
        <input type="text" name="month" dir="ltr" value="<?= e($filters['month']) ?>">
      </label>
      <label>جست‌وجو
        <input type="text" name="q" value="<?= e($filters['q']) ?>">
      </label>
    </div>
    <p><button class="btn gray">فیلتر</button></p>
  </form>

  <?php if (!empty($hasShop)): ?>
  <form method="post" action="/admin/accounting/import-shop" style="border:1px dashed #bbb;border-radius:.5rem;padding:.8rem;margin-bottom:1rem">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <strong>وارد کردن فروش‌های فروشگاه:</strong>
    سفارش‌های پرداخت‌شده‌ای که هنوز سند ندارند، به‌عنوان درآمدِ دسته «فروش» ثبت می‌شوند.
    <label>به حساب
      <select name="account_id">
        <?php foreach ($accounts as $a): if ((int) $a['active'] !== 1) continue; ?>
          <option value="<?= (int) $a['id'] ?>"><?= e($a['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="btn green">وارد کردن</button>
  </form>
  <?php endif; ?>

  <?php if (!empty($hasInventory)): ?>
  <form method="post" action="/admin/accounting/import-inventory" style="border:1px dashed #bbb;border-radius:.5rem;padding:.8rem;margin-bottom:1rem">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <strong>وارد کردن خریدهای انبار:</strong>
    ورودهای قیمت‌دار انبار که هنوز سند ندارند، به‌عنوان هزینهٔ دسته «خرید مواد» ثبت می‌شوند (هر حرکت با مرجع INVMOV-شماره‌اش، بدون تکرار).
    <label>به حساب
      <select name="account_id">
        <?php foreach ($accounts as $a): if ((int) $a['active'] !== 1) continue; ?>
          <option value="<?= (int) $a['id'] ?>"><?= e($a['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="btn green">وارد کردن</button>
  </form>
  <?php endif; ?>

  <p><a class="btn gray" href="/admin/accounting/transactions/export?<?= http_build_query(array_filter(['kind' => $filters['kind'], 'account' => $filters['account_id'], 'category' => $filters['category_id'], 'month' => $filters['month'], 'q' => $filters['q']])) ?>">خروجی CSV همین فیلتر</a></p>

  <?php if (empty($rows)): ?>
    <p>سندی با این فیلتر نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>تاریخ</th><th>نوع</th><th>حساب</th><th>دسته</th><th>طرف حساب</th><th>مرجع</th><th>مبلغ (<?= e($currency) ?>)</th><th>عملیات</th></tr>
    <?php $sumIn = 0; $sumOut = 0; foreach ($rows as $t): $t['kind'] === 'income' ? $sumIn += (int) $t['amount'] : $sumOut += (int) $t['amount']; ?>
    <tr>
      <td><?= e($t['date']) ?></td>
      <td><?= $t['kind'] === 'income' ? 'درآمد' : 'هزینه' ?></td>
      <td><?= e($t['account_title'] ?? '؟') ?></td>
      <td><?= e($t['category_title'] ?? '—') ?></td>
      <td class="wrap"><?= e($t['party']) ?></td>
      <td><code dir="ltr"><?= e($t['ref']) ?></code></td>
      <td><?= number_format((int) $t['amount']) ?></td>
      <td>
        <a class="btn yellow" href="/admin/accounting/transactions/edit/<?= (int) $t['id'] ?>">ویرایش</a>
        <form method="post" action="/admin/accounting/transactions/delete/<?= (int) $t['id'] ?>" style="display:inline" onsubmit="return confirm('این سند حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <tr><th colspan="6">جمع فیلترشده — درآمد: <?= number_format($sumIn) ?> | هزینه: <?= number_format($sumOut) ?> | خالص: <?= number_format($sumIn - $sumOut) ?></th><td colspan="2"></td></tr>
  </table>
  <?php endif; ?>
</div>
