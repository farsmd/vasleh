<?php use Modules\Inventory\Stock; ?>
<div class="card">
  <h1>ثبت گردش انبار</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

  <h2>ورود / خروج / اصلاح</h2>
  <form method="post" action="/admin/inventory/move">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>نوع
        <select name="type">
          <option value="in">ورود به انبار</option>
          <option value="out">خروج از انبار</option>
          <option value="adjust">اصلاح (مقدار = موجودی واقعی شمارش‌شده)</option>
        </select>
      </label>
      <label>کالا
        <select name="item_id" required>
          <?php foreach ($items as $it): ?>
            <option value="<?= (int) $it['id'] ?>"><?= e($it['title']) ?> (<?= e($it['unit']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>انبار
        <select name="warehouse_id" required>
          <?php foreach ($warehouses as $w): ?>
            <option value="<?= (int) $w['id'] ?>" <?= ($defaultWarehouse['id'] ?? 0) == $w['id'] ? 'selected' : '' ?>><?= e($w['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>مقدار
        <input type="number" name="qty" step="any" required>
      </label>
    </div>
    <div class="row">
      <label>مرجع/سند (مثلاً شماره سفارش یا فاکتور خرید)
        <input type="text" name="ref" dir="ltr">
      </label>
      <label>یادداشت
        <input type="text" name="note">
      </label>
    </div>
    <p><button class="btn green">ثبت گردش</button></p>
  </form>

  <h2>انتقال بین انبارها</h2>
  <form method="post" action="/admin/inventory/transfer">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>کالا
        <select name="item_id" required>
          <?php foreach ($items as $it): ?>
            <option value="<?= (int) $it['id'] ?>"><?= e($it['title']) ?> (<?= e($it['unit']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>از انبار
        <select name="from_id" required>
          <?php foreach ($warehouses as $w): ?><option value="<?= (int) $w['id'] ?>"><?= e($w['title']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>به انبار
        <select name="to_id" required>
          <?php foreach ($warehouses as $w): ?><option value="<?= (int) $w['id'] ?>"><?= e($w['title']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label>مقدار
        <input type="number" name="qty" step="any" required>
      </label>
    </div>
    <div class="row">
      <label>مرجع
        <input type="text" name="ref" dir="ltr">
      </label>
      <label>یادداشت
        <input type="text" name="note">
      </label>
    </div>
    <p><button class="btn green">ثبت انتقال</button></p>
  </form>
</div>

<div class="card">
  <h2>گردش‌های اخیر</h2>
  <?php if (empty($recent)): ?>
    <p>هنوز گردشی ثبت نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>تاریخ</th><th>کالا</th><th>انبار</th><th>نوع</th><th>مقدار</th><th>موجودی بعد</th><th>مرجع</th><th>کاربر</th></tr>
    <?php foreach ($recent as $m): ?>
    <tr>
      <td><?= e($m['created_at']) ?></td>
      <td class="wrap"><?= e($m['item_title']) ?></td>
      <td><?= e($m['warehouse_title']) ?></td>
      <td><?= e($types[$m['type']] ?? $m['type']) ?></td>
      <td dir="ltr"><?= Stock::fmt((float) $m['qty']) ?></td>
      <td><?= Stock::fmt((float) $m['balance_after']) ?> <?= e($m['item_unit']) ?></td>
      <td><code dir="ltr"><?= e($m['ref']) ?></code></td>
      <td><?= e($m['actor']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
