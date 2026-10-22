<?php $catTitles = []; foreach ($categories as $c) { $catTitles[(int) $c['id']] = $c['title']; } ?>
<div class="card">
  <h1>لیست قیمت محصولات</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p class="muted">قیمت، قیمت فروش و موجودی را همینجا عوض کن و یکجا ذخیره کن (<?= e($currency) ?>). قیمت واریانت‌ها از فرم ویرایش هر محصول تنظیم می‌شود.</p>
  <form method="post" action="/admin/shop/pricelist">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <table>
      <tr><th>محصول</th><th>دسته</th><th>قیمت پایه</th><th>قیمت فروش</th><th>موجودی</th><th>واریانت‌ها</th></tr>
      <?php foreach ($products as $p): ?>
      <tr>
        <td class="wrap"><?= e($p['title']) ?><?= $p['status'] !== 'published' ? ' <small class="muted">(پیش‌نویس)</small>' : '' ?></td>
        <td><?= e($catTitles[(int) $p['category_id']] ?? '—') ?></td>
        <td><input type="number" name="price[<?= (int) $p['id'] ?>]" value="<?= (int) $p['price'] ?>" min="0" style="width:8rem"></td>
        <td><input type="number" name="sale_price[<?= (int) $p['id'] ?>]" value="<?= (int) $p['sale_price'] ?>" min="0" style="width:8rem"></td>
        <td><input type="number" name="stock[<?= (int) $p['id'] ?>]" value="<?= (int) $p['stock'] ?>" style="width:6rem" <?= (int) $p['track_stock'] !== 1 ? 'disabled' : '' ?>></td>
        <td class="wrap">
          <?php foreach (($variants[(int) $p['id']] ?? []) as $v): $opts = []; foreach (($v['options'] ?? []) as $k => $val) { $opts[] = $val; } ?>
            <div><?= e(implode(' / ', $opts)) ?>: <?= number_format(\Modules\Shop\Catalog::variantPrice($p, $v)) ?> <small class="muted">(موجودی <?= (int) $v['stock'] ?>)</small></div>
          <?php endforeach; ?>
          <?php if (empty($variants[(int) $p['id']] ?? [])): ?>—<?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
    <p><button class="btn green">ذخیره همه قیمت‌ها</button></p>
  </form>
</div>
