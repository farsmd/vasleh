<?php use Modules\Shop\Catalog; $catTitles = []; foreach ($categories as $c) { $catTitles[(int) $c['id']] = $c['title']; } ?>
<h1>لیست قیمت محصولات</h1>
<?php if (empty($byCategory)): ?>
  <p>هنوز محصولی منتشر نشده است.</p>
<?php else: ?>
<?php foreach ($byCategory as $catId => $products): ?>
  <h2><?= e($catTitles[(int) $catId] ?? 'بدون دسته') ?></h2>
  <table>
    <tr><th>محصول</th><th>کد کالا</th><th>قیمت</th></tr>
    <?php foreach ($products as $p): ?>
    <tr>
      <td><a href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>"><?= e($p['title']) ?></a></td>
      <td dir="ltr"><?= e($p['sku']) ?></td>
      <td>
        <?= number_format(Catalog::effectivePrice($p)) ?> <?= e($currency) ?>
        <?php if ((int) $p['sale_price'] > 0): ?><span class="old-price"><?= number_format((int) $p['price']) ?></span><?php endif; ?>
      </td>
    </tr>
    <?php foreach (($variants[(int) $p['id']] ?? []) as $v): ?>
    <tr>
      <td style="padding-inline-start:2rem" class="muted"><?= e(Catalog::optionsText($v['options'], $attrs)) ?></td>
      <td dir="ltr"><?= e($v['sku']) ?></td>
      <td><?= number_format(Catalog::variantPrice($p, $v)) ?> <?= e($currency) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php endforeach; ?>
  </table>
<?php endforeach; ?>
<?php endif; ?>
