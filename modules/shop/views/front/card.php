<?php use Modules\Shop\Catalog;
$p = $product;
$price = Catalog::effectivePrice($p);
$wished = in_array((int) $p['id'], $wishlistIds ?? [], true);
$hasVar = !empty($variantsMap[(int) $p['id']] ?? null);
$catTitles = $catTitles ?? [];
?>
<div class="bg-white dark:bg-slate-900 rounded-3xl p-4 text-slate-800 dark:text-slate-100 border border-slate-100 dark:border-slate-800 shadow-soft hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
  <div>
    <div class="relative overflow-hidden rounded-2xl bg-slate-50 dark:bg-slate-800/70 p-4 aspect-square flex items-center justify-center">
      <?php if ((int) $p['sale_price'] > 0): ?><span class="absolute top-3 right-3 bg-rose-500 text-white text-[10px] font-black px-2.5 py-1 rounded-xl z-10">فروش ویژه</span><?php endif; ?>
      <?php if ((int) $p['track_stock'] === 1 && (int) $p['stock'] < 1): ?><span class="absolute top-3 left-3 bg-slate-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-xl z-10">ناموجود</span><?php endif; ?>
      <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
        <form method="post" action="/shop/wishlist/toggle">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
          <input type="hidden" name="return" value="<?= e($wishReturn ?? '/shop') ?>">
          <button class="w-8 h-8 rounded-xl bg-white/90 dark:bg-slate-900/90 <?= $wished ? 'text-rose-500' : 'text-slate-500 hover:text-rose-500' ?> flex items-center justify-center shadow-sm transition" title="علاقه‌مندی">
            <i class="<?= $wished ? 'fa-solid' : 'fa-regular' ?> fa-heart text-xs"></i>
          </button>
        </form>
        <button onclick="shopOpenQuickView(this)" data-id="<?= (int) $p['id'] ?>" data-title="<?= e($p['title']) ?>" data-price="<?= number_format($price) ?>" data-image="<?= e($p['image'] ?? '') ?>" data-url="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>" data-variants="<?= $hasVar ? '1' : '0' ?>" class="w-8 h-8 rounded-xl bg-white/90 dark:bg-slate-900/90 text-slate-500 hover:text-brand-600 flex items-center justify-center shadow-sm transition" title="مشاهده سریع">
          <i class="fa-regular fa-eye text-xs"></i>
        </button>
      </div>
      <a href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>">
        <?php if (($p['image'] ?? '') !== ''): ?><img src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" class="object-contain h-40 group-hover:scale-110 transition duration-300 rounded-xl">
        <?php else: ?><span class="w-20 h-20 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-300 flex items-center justify-center text-3xl"><i class="fa-regular fa-image"></i></span><?php endif; ?>
      </a>
    </div>
    <div class="mt-4">
      <div class="flex items-center justify-between text-[11px] text-slate-400">
        <span><?= e($catTitles[(int) $p['category_id']] ?? '') ?></span>
      </div>
      <h3 class="text-xs sm:text-sm font-extrabold text-slate-800 dark:text-slate-100 hover:text-brand-600 cursor-pointer line-clamp-2 mt-1.5 leading-snug">
        <a href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>"><?= e($p['title']) ?></a>
      </h3>
      <?php if (($p['short_desc'] ?? '') !== ''): ?><p class="text-xs text-slate-400 mt-1 line-clamp-2"><?= e($p['short_desc']) ?></p><?php endif; ?>
    </div>
  </div>
  <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
    <div class="flex flex-col">
      <?php if ((int) $p['sale_price'] > 0): ?><span class="text-[11px] text-slate-400 line-through"><?= number_format((int) $p['price']) ?></span><?php else: ?><span class="h-4"></span><?php endif; ?>
      <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white"><?= number_format($price) ?> <span class="text-[10px] font-normal text-slate-500"><?= e($currency) ?></span></span>
    </div>
    <a href="/shop/product/<?= e(rawurlencode((string) $p['slug'])) ?>" class="px-3.5 py-2.5 rounded-xl bg-slate-900 dark:bg-brand-600 hover:bg-brand-600 text-white text-xs font-bold flex items-center gap-1.5 transition shadow-sm">
      <i class="fa-solid fa-plus"></i> خرید
    </a>
  </div>
</div>
