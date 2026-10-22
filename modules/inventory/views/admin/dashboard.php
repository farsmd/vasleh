<?php use Modules\Inventory\Stock; $kindLabels = ['material' => 'ماده اولیه', 'product' => 'محصول', 'other' => 'سایر']; ?>
<div class="card">
  <h1>موجودی انبار</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    <span class="btn gray">انبارها: <?= (int) $stats['warehouses'] ?></span>
    <span class="btn gray">کالاهای فعال: <?= (int) $stats['items'] ?></span>
    <span class="btn gray">کل اسناد گردش: <?= (int) $stats['moves'] ?></span>
    <a class="btn green" href="/admin/inventory/move">+ ثبت ورود/خروج</a>
  </p>
  <?php if (!empty($low)): ?>
  <div class="error">
    <strong>هشدار کمبود موجودی:</strong>
    <?php foreach ($low as $l): ?>
      <?= e($l['title']) ?> (<?= Stock::fmt((float) $l['qty']) ?> <?= e($l['unit']) ?> — حد: <?= Stock::fmt((float) $l['min_stock']) ?>)؛
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <form method="get" action="/admin/inventory" style="max-width:22rem">
    <label>نمایش موجودی انبار
      <select name="warehouse" onchange="this.form.submit()">
        <option value="0" <?= $warehouseId === 0 ? 'selected' : '' ?>>همه انبارها (جمع)</option>
        <?php foreach ($warehouses as $w): ?>
          <option value="<?= (int) $w['id'] ?>" <?= $warehouseId === (int) $w['id'] ? 'selected' : '' ?>><?= e($w['title']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </form>
  <?php if (empty($rows)): ?>
    <p>هنوز کالایی تعریف نشده است. از «کالاهای انبار» شروع کنید.</p>
  <?php else: ?>
  <table>
    <tr><th>کالا</th><th>کد</th><th>نوع</th><th>موجودی</th><th>واحد</th><th>حد هشدار</th><th>وضعیت</th></tr>
    <?php foreach ($rows as $r): $qty = (float) $r['qty']; $isLow = (float) $r['min_stock'] > 0 && $qty <= (float) $r['min_stock']; ?>
    <tr>
      <td class="wrap"><?= e($r['title']) ?><?= (int) $r['active'] !== 1 ? ' <small class="muted">(غیرفعال)</small>' : '' ?></td>
      <td><code dir="ltr"><?= e($r['sku']) ?></code></td>
      <td><?= e($kindLabels[$r['kind']] ?? $r['kind']) ?></td>
      <td><strong><?= Stock::fmt($qty) ?></strong></td>
      <td><?= e($r['unit']) ?></td>
      <td><?= Stock::fmt((float) $r['min_stock']) ?></td>
      <td><?= $isLow ? '⚠ رو به اتمام' : '—' ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
