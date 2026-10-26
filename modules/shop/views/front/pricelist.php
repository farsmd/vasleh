<?php use Modules\Shop\Catalog; $catTitles = []; foreach ($categories as $c) { $catTitles[(int) $c['id']] = $c['title']; } ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 mb-6">لیست قیمت محصولات</h1>
  <?php if (empty($byCategory)): ?>
    <p class="text-sm text-slate-500">هنوز محصولی منتشر نشده است.</p>
  <?php else: ?>
  <?php foreach ($byCategory as $catId => $products): ?>
  <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm mb-6">
    <h2 class="font-black text-slate-900 text-lg mb-4"><?= e($catTitles[(int) $catId] ?? 'بدون دسته') ?></h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <tr class="text-right text-slate-500 border-b border-slate-100"><th class="py-2 pl-3 font-medium">محصول</th><th class="py-2 pl-3 font-medium">کد کالا</th><th class="py-2 font-medium">قیمت</th></tr>
        <?php foreach ($products as $p): ?>
        <tr class="border-b border-slate-50">
          <td class="py-2 pl-3"><a href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>" class="font-bold text-slate-800 hover:text-blue-600"><?= e($p['title']) ?></a></td>
          <td dir="ltr" class="py-2 pl-3 text-slate-500"><?= e($p['sku']) ?></td>
          <td class="py-2">
            <?= number_format(Catalog::effectivePrice($p)) ?> <?= e($currency) ?>
            <?php if ((int) $p['sale_price'] > 0): ?><span class="text-xs text-slate-400 line-through mr-2"><?= number_format((int) $p['price']) ?></span><?php endif; ?>
          </td>
        </tr>
        <?php foreach (($variants[(int) $p['id']] ?? []) as $v): ?>
        <tr class="border-b border-slate-50">
          <td class="py-2 pl-3 text-slate-500 pr-8"><?= e(Catalog::optionsText($v['options'], $attrs)) ?></td>
          <td dir="ltr" class="py-2 pl-3 text-slate-500"><?= e($v['sku']) ?></td>
          <td class="py-2"><?= number_format(Catalog::variantPrice($p, $v)) ?> <?= e($currency) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
