<?php use Modules\Shop\Catalog; $price = Catalog::effectivePrice($product); $hasVariants = !empty($variants); ?>
<?php if (!empty($added)): ?><div class="flash">به سبد خرید اضافه شد. <a href="/shop/cart">مشاهده سبد</a></div><?php endif; ?>
<?php if (!empty($category)): ?><p class="muted"><a href="/shop?cat=<?= e(rawurlencode((string) $category['slug'])) ?>"><?= e($category['title']) ?></a> / <?= e($product['title']) ?></p><?php endif; ?>
<h1><?= e($product['title']) ?></h1>
<?php if (($product['image'] ?? '') !== ''): ?>
  <img src="<?= e($product['image']) ?>" alt="<?= e($product['title']) ?>" style="max-width:26rem;width:100%;border-radius:.8rem">
<?php endif; ?>
<?php if (($product['short_desc'] ?? '') !== ''): ?><p><?= e($product['short_desc']) ?></p><?php endif; ?>
<div class="price" style="font-size:1.25rem">
  <?php if ($hasVariants): ?>از <?php endif; ?>
  <?= number_format($price) ?> <?= e($currency) ?>
  <?php if ((int) $product['sale_price'] > 0): ?><span class="old-price"><?= number_format((int) $product['price']) ?></span><?php endif; ?>
</div>
<?php if ((int) $product['track_stock'] === 1): ?>
  <p class="muted"><?= (int) $product['stock'] > 0 ? 'موجودی: ' . (int) $product['stock'] : 'ناموجود' ?></p>
<?php endif; ?>
<?php if (($product['sku'] ?? '') !== ''): ?><p class="muted">کد کالا: <span dir="ltr"><?= e($product['sku']) ?></span></p><?php endif; ?>

<form method="post" action="/shop/cart/add" style="max-width:30rem;margin:1rem 0">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
  <?php if ($hasVariants): ?>
  <label>انتخاب نوع (واریانت)
    <select name="variant_id" id="variant-select">
      <?php foreach ($variants as $v): ?>
        <option value="<?= (int) $v['id'] ?>" data-price="<?= Catalog::variantPrice($product, $v) ?>" data-stock="<?= (int) $v['stock'] ?>">
          <?= e(Catalog::optionsText($v['options'], $attrs)) ?> — <?= number_format(Catalog::variantPrice($product, $v)) ?> <?= e($currency) ?><?= (int) $product['track_stock'] === 1 && (int) $v['stock'] < 1 ? ' (ناموجود)' : '' ?>
        </option>
      <?php endforeach; ?>
    </select>
  </label>
  <?php endif; ?>
  <label>تعداد
    <input type="number" name="qty" value="1" min="1" style="max-width:8rem">
  </label>
  <button class="btn" type="submit">افزودن به سبد خرید</button>
  <a class="btn light" href="/shop">بازگشت به فروشگاه</a>
</form>

<?php if (!empty($fields)): ?>
<div class="specs">
  <h3 style="margin-top:0">مشخصات</h3>
  <?php foreach ($fields as $f): ?>
    <div><span class="muted"><?= e($f['title']) ?></span><span><?= $f['type'] === 'checkbox' ? ($f['value'] === '1' ? 'بله' : 'خیر') : e($f['value']) ?></span></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (($product['description'] ?? '') !== ''): ?>
<div style="background:var(--admin-panel-bg);border:1px solid var(--admin-border);border-radius:var(--admin-radius);padding:1rem">
  <?= $product['description'] ?>
</div>
<?php endif; ?>
