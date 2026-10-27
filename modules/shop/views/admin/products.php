<?php use Modules\Shop\Catalog; $catTitles = []; foreach ($categories as $c) { $catTitles[(int) $c['id']] = $c['title']; } ?>
<div class="card">
  <h1>محصولات فروشگاه</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    <a class="btn green" href="/admin/shop/products/create">+ محصول تازه</a>
    <a class="btn gray" href="/shop" target="_blank">مشاهده فروشگاه</a>
    <?php if ($lowCount > 0): ?><span class="btn gray">کالای کم‌موجود: <?= (int) $lowCount ?> (آستانه: <?= (int) $lowThreshold ?>)</span><?php endif; ?>
  </p>
  <?php if (empty($products)): ?>
    <p>هنوز محصولی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>عنوان</th><th>دسته</th><th>کد کالا</th><th>قیمت</th><th>قیمت فروش</th><th>موجودی</th><th>وضعیت</th><th>عملیات</th></tr>
    <?php foreach ($products as $p): ?>
    <tr>
      <td class="wrap"><?= e($p['title']) ?><?php if (!empty($p['featured'])): ?> <span style="color:#d97706;font-weight:bold">★ ویژه</span><?php endif; ?></td>
      <td><?= e($catTitles[(int) $p['category_id']] ?? '—') ?></td>
      <td><code dir="ltr"><?= e($p['sku']) ?></code></td>
      <td><?= number_format((int) $p['price']) ?></td>
      <td><?= (int) $p['sale_price'] > 0 ? number_format((int) $p['sale_price']) : '—' ?></td>
      <td><?= (int) $p['track_stock'] === 1 ? (int) $p['stock'] : 'نامحدود' ?><?php if ((int) $p['track_stock'] === 1 && (int) $p['stock'] <= (int) $lowThreshold): ?> <span style="color:#dc2626;font-weight:bold">کمبود</span><?php endif; ?></td>
      <td><?= $p['status'] === 'published' ? 'منتشرشده' : 'پیش‌نویس' ?></td>
      <td>
        <a class="btn yellow" href="/admin/shop/products/edit/<?= (int) $p['id'] ?>">ویرایش</a>
        <?php if ($p['status'] === 'published'): ?>
          <a class="btn gray" target="_blank" href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>">مشاهده</a>
        <?php endif; ?>
        <form method="post" action="/admin/shop/products/delete/<?= (int) $p['id'] ?>" style="display:inline" onsubmit="return confirm('این محصول حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p class="muted">قیمت‌ها به <?= e($currency) ?> است. برای ویرایش سریع همه قیمت‌ها از «لیست قیمت» استفاده کنید.</p>
  <?php endif; ?>
</div>
