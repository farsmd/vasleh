<div class="card">
  <h1><?= !empty($tx['id']) ? 'ویرایش سند' : 'سند تازه' ?></h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>نوع سند
        <select name="kind" id="tx-kind">
          <option value="expense" <?= $tx['kind'] === 'expense' ? 'selected' : '' ?>>هزینه (پرداخت)</option>
          <option value="income" <?= $tx['kind'] === 'income' ? 'selected' : '' ?>>درآمد (دریافت)</option>
        </select>
      </label>
      <label>حساب
        <select name="account_id" required>
          <?php foreach ($accounts as $a): ?>
            <option value="<?= (int) $a['id'] ?>" <?= (int) $tx['account_id'] === (int) $a['id'] ? 'selected' : '' ?>><?= e($a['title']) ?> (مانده: <?= number_format((int) $a['balance']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>دسته
        <select name="category_id" id="tx-cat">
          <option value="0">— بدون دسته —</option>
          <optgroup label="درآمد" data-kind="income">
            <?php foreach ($incomeCats as $c): ?>
              <option value="<?= (int) $c['id'] ?>" data-kind="income" <?= (int) $tx['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="هزینه" data-kind="expense">
            <?php foreach ($expenseCats as $c): ?>
              <option value="<?= (int) $c['id'] ?>" data-kind="expense" <?= (int) $tx['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      </label>
    </div>
    <div class="row">
      <label>مبلغ
        <input type="number" name="amount" min="0" required value="<?= (int) $tx['amount'] ?>">
      </label>
      <label>تاریخ (میلادی — مثل 2026-10-23)
        <input type="text" name="date" dir="ltr" required value="<?= e($tx['date']) ?>">
      </label>
      <label>مرجع/شماره سند
        <input type="text" name="ref" dir="ltr" value="<?= e($tx['ref']) ?>">
      </label>
      <label>طرف حساب (مشتری/فروشنده)
        <input type="text" name="party" value="<?= e($tx['party']) ?>">
      </label>
    </div>
    <label>توضیح
      <input type="text" name="description" value="<?= e($tx['description']) ?>">
    </label>
    <p>
      <button class="btn green">ذخیره سند</button>
      <a class="btn gray" href="/admin/accounting/transactions">بازگشت</a>
    </p>
  </form>
</div>
