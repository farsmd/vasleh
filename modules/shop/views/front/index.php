<?php use Modules\Shop\Catalog; $catTitles = []; foreach (($categories ?? []) as $c) { $catTitles[(int) $c['id']] = $c['title']; } ?>
<?php if (!empty($activeCat)): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6 font-medium">
    <a href="/shop" class="hover:text-blue-600">فروشگاه</a>
    <i class="fa-solid fa-angle-left text-[10px] text-slate-400"></i>
    <span class="text-slate-900 font-bold"><?= e($activeCat['title']) ?></span>
  </nav>
  <h1 class="text-2xl font-black text-slate-900"><?= e($activeCat['title']) ?></h1>
  <?php if (($activeCat['description'] ?? '') !== ''): ?><p class="text-sm text-slate-500 mt-2"><?= e($activeCat['description']) ?></p><?php endif; ?>
</div>
<?php else: ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <div class="relative rounded-3xl overflow-hidden bg-gradient-to-l from-slate-950 via-slate-900 to-indigo-950 text-white min-h-[320px] flex items-center shadow-2xl">
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-500/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="p-8 sm:p-12 z-10 w-full space-y-5">
      <h1 class="text-3xl sm:text-5xl font-black leading-tight tracking-tight"><?= e($shopTitle) ?></h1>
      <p class="text-slate-300 text-sm sm:text-base max-w-xl leading-relaxed">
        <?= count($products) ?> محصول منتشر شده — دسته‌ی موردنظرت را انتخاب کن یا مستقیم از فهرست زیر خرید کن.
      </p>
      <div class="flex flex-wrap items-center gap-4 pt-2">
        <a href="#products" class="px-7 py-3.5 rounded-2xl bg-white text-slate-950 font-bold hover:bg-slate-100 transition-all text-sm">مشاهده محصولات</a>
        <a href="/shop/track" class="px-7 py-3.5 rounded-2xl bg-white/10 hover:bg-white/15 border border-white/20 text-white font-semibold backdrop-blur-md transition-all text-sm">پیگیری سفارش</a>
      </div>
    </div>
  </div>
</div>
<?php if (!empty($categories)): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-black text-slate-900">دسته‌بندی‌ها</h2>
  </div>
  <div class="grid grid-cols-3 sm:grid-cols-6 gap-4">
    <?php foreach ($categories as $c): $n = 0; foreach ($products as $pp) { if ((int) $pp['category_id'] === (int) $c['id']) { $n++; } } ?>
    <a href="/shop?cat=<?= e(rawurlencode((string) $c['slug'])) ?>" class="group bg-white p-5 rounded-3xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-500 text-center transition-all duration-300">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-50 group-hover:bg-blue-50 text-slate-700 group-hover:text-blue-600 flex items-center justify-center text-2xl transition duration-300"><i class="fa-solid fa-layer-group"></i></div>
      <h3 class="text-xs font-bold text-slate-800 mt-3 group-hover:text-blue-600"><?= e($c['title']) ?></h3>
      <span class="text-[10px] text-slate-400"><?= $n ?> کالا</span>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div id="products" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14">
  <?php if (empty($products)): ?>
    <p class="text-sm text-slate-500">هنوز محصولی منتشر نشده است.</p>
  <?php else: ?>
  <?php if (empty($activeCat)): ?><h2 class="text-xl font-black text-slate-900 mb-6">محصولات</h2><?php endif; ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php foreach ($products as $p): $price = Catalog::effectivePrice($p); ?>
    <div class="bg-white rounded-3xl p-4 border border-slate-100 hover:border-slate-300 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
      <div>
        <div class="relative overflow-hidden rounded-2xl bg-slate-50 p-4 aspect-square flex items-center justify-center">
          <?php if ((int) $p['sale_price'] > 0): ?><span class="absolute top-3 right-3 bg-rose-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md z-10">فروش ویژه</span><?php endif; ?>
          <?php if ((int) $p['track_stock'] === 1 && (int) $p['stock'] < 1): ?><span class="absolute top-3 left-3 bg-slate-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-md z-10">ناموجود</span><?php endif; ?>
          <a href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>">
            <?php if (($p['image'] ?? '') !== ''): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" class="object-contain h-44 group-hover:scale-105 transition duration-300">
            <?php else: ?><span class="w-20 h-20 rounded-2xl bg-slate-100 text-slate-300 flex items-center justify-center text-3xl"><i class="fa-regular fa-image"></i></span><?php endif; ?>
          </a>
        </div>
        <div class="mt-4">
          <div class="flex items-center justify-between text-[11px] text-slate-400">
            <span><?= e($catTitles[(int) $p['category_id']] ?? '') ?></span>
          </div>
          <h3 class="text-sm font-bold text-slate-800 mt-1.5 leading-snug">
            <a href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>" class="hover:text-blue-600"><?= e($p['title']) ?></a>
          </h3>
          <?php if (($p['short_desc'] ?? '') !== ''): ?><p class="text-xs text-slate-400 mt-1 line-clamp-2"><?= e($p['short_desc']) ?></p><?php endif; ?>
        </div>
      </div>
      <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between">
        <div class="flex flex-col">
          <?php if ((int) $p['sale_price'] > 0): ?><span class="text-xs text-slate-400 line-through"><?= number_format((int) $p['price']) ?></span><?php endif; ?>
          <span class="text-base font-black text-slate-900"><?= number_format($price) ?> <span class="text-[11px] font-normal text-slate-500"><?= e($currency) ?></span></span>
        </div>
        <a href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-blue-600 text-white text-xs font-semibold flex items-center gap-1.5 transition">
          <i class="fa-solid fa-cart-plus"></i> خرید
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
