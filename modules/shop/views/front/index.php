<?php use Modules\Shop\Catalog; ?>
<?php if (!empty($categories)): ?>
<div class="cats">
  <a href="/shop" class="<?= empty($activeCat) ? 'active' : '' ?>">همه</a>
  <?php foreach ($categories as $c): ?>
    <a href="/shop?cat=<?= e(rawurlencode((string) $c['slug'])) ?>" class="<?= !empty($activeCat) && (int) $activeCat['id'] === (int) $c['id'] ? 'active' : '' ?>"><?= e($c['title']) ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($activeCat) && ($activeCat['description'] ?? '') !== ''): ?>
  <p class="muted"><?= e($activeCat['description']) ?></p>
<?php endif; ?>
<?php if (empty($products)): ?>
  <p>هنوز محصولی منتشر نشده است.</p>
<?php else: ?>
<div class="grid">
  <?php foreach ($products as $p): $price = Catalog::effectivePrice($p); ?>
  <a class="p-card" href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>">
    <?php if (($p['image'] ?? '') !== ''): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>"><?php endif; ?>
    <h3><?= e($p['title']) ?></h3>
    <?php if (($p['short_desc'] ?? '') !== ''): ?><p class="muted"><?= e($p['short_desc']) ?></p><?php endif; ?>
    <div class="price">
      <?= number_format($price) ?> <?= e($currency) ?>
      <?php if ((int) $p['sale_price'] > 0): ?><span class="old-price"><?= number_format((int) $p['price']) ?></span><?php endif; ?>
    </div>
    <?php if ((int) $p['track_stock'] === 1 && (int) $p['stock'] < 1): ?><div class="muted">ناموجود</div><?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
