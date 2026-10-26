<?php use Modules\Shop\Catalog; $price = Catalog::effectivePrice($product); $hasVariants = !empty($variants); ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-6 font-medium">
    <a href="/shop" class="hover:text-brand-600">فروشگاه</a>
    <?php if (!empty($category)): ?>
    <i class="fa-solid fa-angle-left text-[10px] text-slate-400"></i>
    <a href="/shop?cat=<?= e(rawurlencode((string) $category['slug'])) ?>" class="hover:text-brand-600"><?= e($category['title']) ?></a>
    <?php endif; ?>
    <i class="fa-solid fa-angle-left text-[10px] text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-bold"><?= e($product['title']) ?></span>
  </nav>

  <?php if (!empty($added)): ?><div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-2xl px-4 py-3 mb-6">به سبد خرید اضافه شد. <a href="/shop/cart" class="font-bold underline">مشاهده سبد</a></div><?php endif; ?>

  <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-10 border border-slate-100 shadow-sm">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
      <div class="lg:col-span-5">
        <div class="relative bg-slate-50 dark:bg-slate-800/60 rounded-3xl p-8 flex items-center justify-center border border-slate-100 dark:border-slate-800 overflow-hidden">
          <?php if ((int) $product['sale_price'] > 0): ?><span class="absolute top-4 right-4 bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-full">فروش ویژه</span><?php endif; ?>
          <?php if (($product['image'] ?? '') !== ''): ?>
            <img src="<?= e($product['image']) ?>" alt="<?= e($product['title']) ?>" class="w-full max-h-[380px] object-contain">
          <?php else: ?>
            <span class="w-32 h-32 rounded-3xl bg-slate-100 text-slate-300 flex items-center justify-center text-5xl"><i class="fa-regular fa-image"></i></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="lg:col-span-7 space-y-6">
        <div>
          <div class="flex items-center justify-between text-xs text-slate-400 mb-2">
            <?php if (($product['sku'] ?? '') !== ''): ?><span>کد کالا: <span dir="ltr" class="font-mono text-slate-600 font-bold"><?= e($product['sku']) ?></span></span><?php endif; ?>
            <?php if ((int) $product['track_stock'] === 1): ?><span class="font-bold <?= (int) $product['stock'] > 0 ? 'text-emerald-600' : 'text-slate-400' ?>"><?= (int) $product['stock'] > 0 ? 'موجود (' . (int) $product['stock'] . ' عدد)' : 'ناموجود' ?></span><?php endif; ?>
          </div>
          <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white leading-tight"><?= e($product['title']) ?></h1>
          <?php if (($product['short_desc'] ?? '') !== ''): ?><p class="text-sm text-slate-500 mt-2 leading-relaxed"><?= e($product['short_desc']) ?></p><?php endif; ?>
        </div>

        <form method="post" action="/shop/cart/add" class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-3xl border border-slate-200/80 dark:border-slate-800 space-y-4">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
          <?php if ($hasVariants): ?>
          <div>
            <label class="text-xs font-bold text-slate-800 block mb-2" for="variant-select">انتخاب نوع (واریانت)</label>
            <select name="variant_id" id="variant-select" class="w-full bg-white border border-slate-200 rounded-2xl px-3 py-2.5 text-sm outline-none focus:border-brand-500">
              <?php foreach ($variants as $v): ?>
              <option value="<?= (int) $v['id'] ?>" data-price="<?= Catalog::variantPrice($product, $v) ?>">
                <?= e(Catalog::optionsText($v['options'], $attrs)) ?> — <?= number_format(Catalog::variantPrice($product, $v)) ?> <?= e($currency) ?><?= (int) $product['track_stock'] === 1 && (int) $v['stock'] < 1 ? ' (ناموجود)' : '' ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
              <?php if ((int) $product['sale_price'] > 0): ?><span class="text-xs text-slate-400 line-through block"><?= number_format((int) $product['price']) ?> <?= e($currency) ?></span><?php endif; ?>
              <div id="price-box" class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white"><?= number_format($price) ?> <span class="text-sm font-medium text-slate-500"><?= e($currency) ?></span></div>
            </div>
            <div class="flex items-center border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-2xl p-1 shadow-sm">
              <button type="button" onclick="var q=document.getElementById('qty');q.value=Math.max(1,parseInt(q.value||'1')+1)" class="w-9 h-9 rounded-xl hover:bg-slate-100 flex items-center justify-center text-slate-700 font-bold transition"><i class="fa-solid fa-plus text-xs"></i></button>
              <input id="qty" type="number" name="qty" value="1" min="1" class="w-12 text-center font-bold text-sm text-slate-800 outline-none border-0">
              <button type="button" onclick="var q=document.getElementById('qty');q.value=Math.max(1,parseInt(q.value||'1')-1)" class="w-9 h-9 rounded-xl hover:bg-slate-100 flex items-center justify-center text-slate-700 font-bold transition"><i class="fa-solid fa-minus text-xs"></i></button>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <button type="submit" class="w-full py-4 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-sm shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 transition duration-200">
              <i class="fa-solid fa-bag-shopping text-base"></i><span>افزودن به سبد خرید</span>
            </button>
            <a href="/shop" class="w-full py-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm flex items-center justify-center gap-2 transition duration-200">بازگشت به فروشگاه</a>
          </div>
        </form>
        <form method="post" action="/shop/wishlist/toggle" class="pt-1">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">
          <input type="hidden" name="return" value="/shop/product/<?= e(rawurlencode((string) $product['slug'])) ?>">
          <button class="w-full py-3 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-500 font-bold text-sm flex items-center justify-center gap-2 transition">
            <i class="<?= in_array((int) $product['id'], $wishlistIds ?? [], true) ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
            <span><?= in_array((int) $product['id'], $wishlistIds ?? [], true) ? 'در علاقه‌مندی‌هاست — حذف' : 'افزودن به علاقه‌مندی‌ها' ?></span>
          </button>
        </form>
        <?php if ($hasVariants): ?>
        <script>
          (function(){var s=document.getElementById('variant-select');if(!s)return;s.addEventListener('change',function(){var o=s.options[s.selectedIndex];var p=o.getAttribute('data-price');if(p)document.getElementById('price-box').innerHTML=Number(p).toLocaleString('en-US')+' <span class="text-sm font-medium text-slate-500"><?= e($currency) ?></span>';});})();
        </script>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($fields) || ($product['description'] ?? '') !== ''): ?>
    <div class="mt-14 pt-8 border-t border-slate-100 dark:border-slate-800">
      <?php if (!empty($fields)): ?>
      <h3 class="text-base font-black text-slate-900 mb-4">مشخصات</h3>
      <div class="max-w-2xl divide-y divide-slate-100 dark:divide-slate-800 text-xs mb-8">
        <?php foreach ($fields as $f): ?>
        <div class="grid grid-cols-3 py-3 font-medium"><span class="text-slate-400"><?= e($f['title']) ?></span><span class="col-span-2 text-slate-800 dark:text-slate-100 font-bold"><?= $f['type'] === 'checkbox' ? ($f['value'] === '1' ? 'بله' : 'خیر') : e($f['value']) ?></span></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <?php if (($product['description'] ?? '') !== ''): ?>
      <div class="text-sm text-slate-600 leading-relaxed"><?= $product['description'] ?></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php if (!empty($related)): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14">
  <h2 class="text-xl font-black text-slate-900 dark:text-white mb-6">کالاهای مرتبط</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php foreach ($related as $rp): ?><?= \Core\View::render('shop', 'front', 'card', ['product' => $rp, 'wishlistIds' => $wishlistIds ?? [], 'csrf' => $csrf, 'currency' => $currency, 'catTitles' => $catTitles, 'variantsMap' => $variantsMap ?? []]) ?><?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
