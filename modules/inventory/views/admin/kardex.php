<?php use Modules\Inventory\Stock; ?>
<div class="card">
  <h1>کاردکس انبار<?= !empty($item) ? ' — ' . e($item['title']) : '' ?></h1>
  <form method="get" action="/admin/inventory/kardex">
    <div class="row">
      <label>کالا
        <select name="item">
          <option value="0">— همه کالاها —</option>
          <?php foreach ($items as $it): ?>
            <option value="<?= (int) $it['id'] ?>" <?= $itemId === (int) $it['id'] ? 'selected' : '' ?>><?= e($it['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>انبار
        <select name="warehouse">
          <option value="0">— همه انبارها —</option>
          <?php foreach ($warehouses as $w): ?>
            <option value="<?= (int) $w['id'] ?>" <?= $warehouseId === (int) $w['id'] ? 'selected' : '' ?>><?= e($w['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <p><button class="btn green">نمایش</button></p>
  </form>

  <?php if (empty($rows)): ?>
    <p>گردشی برای این فیلتر نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>تاریخ</th><th>کالا</th><th>انبار</th><th>نوع</th><th>مقدار</th><th>قیمت واحد</th><th>مبلغ</th><th>موجودی بعد</th><th>مرجع</th><th>یادداشت</th><th>کاربر</th></tr>
    <?php foreach ($rows as $m): ?>
    <tr>
      <td><?= e($m['created_at']) ?></td>
      <td class="wrap"><?= e($m['item_title']) ?></td>
      <td><?= e($m['warehouse_title']) ?></td>
      <td><?= e($types[$m['type']] ?? $m['type']) ?></td>
      <td dir="ltr"><?= Stock::fmt((float) $m['qty']) ?></td>
      <td><?= (float) ($m['unit_price'] ?? 0) > 0 ? number_format((float) $m['unit_price']) : '—' ?></td>
      <td><?= (float) ($m['unit_price'] ?? 0) > 0 ? number_format((int) round((float) $m['qty'] * (float) $m['unit_price'])) : '—' ?></td>
      <td><?= Stock::fmt((float) $m['balance_after']) ?> <?= e($m['item_unit']) ?></td>
      <td><code dir="ltr"><?= e($m['ref']) ?></code></td>
      <td class="wrap"><?= e($m['note']) ?></td>
      <td><?= e($m['actor']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
