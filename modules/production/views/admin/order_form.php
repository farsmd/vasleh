<div class="card">
  <h1>دستور تولید تازه</h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <?php if (empty($recipes)): ?>
    <p>هنوز فرمول فعالی نیست. اول از «فرمول‌های ساخت» یک فرمول بسازید.</p>
  <?php else: ?>
  <form method="post" action="/admin/production/orders/create">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>فرمول ساخت
        <select name="recipe_id" required>
          <?php foreach ($recipes as $r): ?>
            <option value="<?= (int) $r['id'] ?>" <?= $preselected === (int) $r['id'] ? 'selected' : '' ?>><?= e($r['title']) ?> ← <?= e($r['output_title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>تعداد بچ
        <input type="number" name="batches" step="any" min="0" value="1" required>
      </label>
      <label>انبار (مواد از این انبار کسر و خروجی به همین انبار می‌رود)
        <select name="warehouse_id" required>
          <?php foreach ($warehouses as $w): ?>
            <option value="<?= (int) $w['id'] ?>" <?= ($defaultWarehouse['id'] ?? 0) == $w['id'] ? 'selected' : '' ?>><?= e($w['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <label>یادداشت
      <input type="text" name="note">
    </label>
    <p>
      <button class="btn green">ساخت دستور</button>
      <a class="btn gray" href="/admin/production/orders">بازگشت</a>
    </p>
  </form>
  <?php endif; ?>
</div>
