<?php use Modules\Inventory\Stock; $kindLabels = ['material' => 'ماده اولیه', 'product' => 'محصول', 'other' => 'سایر']; ?>
<div class="card">
  <h1>کالاهای انبار</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/inventory/items/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
      <label>عنوان کالا
        <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
      </label>
      <label>کد کالا (SKU — یکتا)
        <input type="text" name="sku" dir="ltr" value="<?= e($edit['sku'] ?? '') ?>">
      </label>
      <label>واحد
        <input type="text" name="unit" value="<?= e($edit['unit'] ?? 'عدد') ?>">
      </label>
      <label>نوع
        <select name="kind">
          <?php foreach ($kindLabels as $k => $label): ?>
            <option value="<?= $k ?>" <?= ($edit['kind'] ?? 'material') === $k ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>حد هشدار کمبود (۰ = بدون هشدار)
        <input type="number" name="min_stock" step="any" value="<?= e($edit['min_stock'] ?? '0') ?>">
      </label>
    </div>
    <div class="row">
      <?php if (!empty($shopProducts)): ?>
      <label>اتصال به محصول فروشگاه (اختیاری — برای گزارش و تولید)
        <select name="shop_product_id">
          <option value="0">— بدون اتصال —</option>
          <?php foreach ($shopProducts as $p): ?>
            <option value="<?= (int) $p['id'] ?>" <?= (int) ($edit['shop_product_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <?php endif; ?>
      <label>یادداشت
        <input type="text" name="note" value="<?= e($edit['note'] ?? '') ?>">
      </label>
    </div>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="active" value="1" <?= !isset($edit) || (int) ($edit['active'] ?? 1) === 1 ? 'checked' : '' ?>> فعال
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت کالا' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/inventory/items">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<div class="card">
  <?php if (empty($items)): ?>
    <p>هنوز کالایی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>عنوان</th><th>کد</th><th>نوع</th><th>واحد</th><th>موجودی کل</th><th>حد هشدار</th><th>عملیات</th></tr>
    <?php foreach ($items as $i => $it): ?>
    <tr>
      <td class="wrap"><?= e($it['title']) ?><?= (int) $it['active'] !== 1 ? ' <small class="muted">(غیرفعال)</small>' : '' ?></td>
      <td><code dir="ltr"><?= e($it['sku']) ?></code></td>
      <td><?= e($kindLabels[$it['kind']] ?? $it['kind']) ?></td>
      <td><?= e($it['unit']) ?></td>
      <td><strong><?= Stock::fmt((float) ($totals[$i] ?? 0)) ?></strong></td>
      <td><?= Stock::fmt((float) $it['min_stock']) ?></td>
      <td>
        <a class="btn gray" href="/admin/inventory/kardex?item=<?= (int) $it['id'] ?>">کاردکس</a>
        <a class="btn yellow" href="/admin/inventory/items?edit=<?= (int) $it['id'] ?>">ویرایش</a>
        <form method="post" action="/admin/inventory/items/delete/<?= (int) $it['id'] ?>" style="display:inline" onsubmit="return confirm('این کالا با همه سوابق گردشش حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
