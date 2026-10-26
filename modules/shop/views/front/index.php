<?php use Modules\Shop\Catalog; use Core\View;
$f = $filters ?? ['q' => '', 'max_price' => 0, 'sort' => '', 'sale' => false];
$side = array_slice(!empty($featured) ? $featured : $products, 0, 2);
$shipCost = (int) ($shippingCost ?? 0);
?>
<?php if (!empty($activeCat)): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-6 font-medium">
    <a href="/shop" class="hover:text-brand-600">فروشگاه</a>
    <i class="fa-solid fa-angle-left text-[10px] text-slate-400"></i>
    <span class="text-slate-900 dark:text-white font-bold"><?= e($activeCat['title']) ?></span>
  </nav>
  <h1 class="text-2xl font-black text-slate-900 dark:text-white"><?= e($activeCat['title']) ?></h1>
  <?php if (($activeCat['description'] ?? '') !== ''): ?><p class="text-sm text-slate-500 dark:text-slate-400 mt-2"><?= e($activeCat['description']) ?></p><?php endif; ?>
</div>
<?php elseif (empty($filtersActive)): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
    <div class="lg:col-span-8 relative rounded-[2rem] overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white p-8 sm:p-12 flex flex-col justify-between min-h-[420px] shadow-xl">
      <div class="absolute -top-24 -left-24 w-80 h-80 bg-brand-500/30 rounded-full blur-3xl pointer-events-none"></div>
      <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center my-auto relative z-10">
        <div class="md:col-span-7 space-y-5">
          <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs text-amber-300 font-bold">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
            <?= count($products) ?> محصول منتشر شده
          </div>
          <h1 class="text-3xl sm:text-5xl font-black leading-tight"><?= e($shopTitle) ?></h1>
          <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-md">
            دسته‌ی موردنظرت را انتخاب کن یا مستقیم از فهرست کامل زیر خرید کن — همه‌ی محصولات با عکس و قیمت واقعی.
          </p>
          <div class="flex flex-wrap items-center gap-3 pt-2">
            <a href="#products" class="px-6 py-3.5 rounded-2xl bg-white text-slate-950 font-black text-xs sm:text-sm hover:bg-brand-50 transition shadow-lg flex items-center gap-2">
              <span>مشاهده محصولات</span>
              <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <a href="/shop/track" class="px-5 py-3.5 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/15 text-white font-bold text-xs sm:text-sm backdrop-blur-md transition">
              پیگیری سفارش
            </a>
          </div>
        </div>
        <?php if (!empty($side)): $hero = $side[0]; ?>
        <div class="md:col-span-5 flex justify-center relative">
          <a href="/shop/product/<?= e(rawurlencode((string) $hero['slug'])) ?>">
            <?php if (($hero['image'] ?? '') !== ''): ?><img src="<?= e($hero['image']) ?>" alt="<?= e($hero['title']) ?>" class="w-60 sm:w-72 h-auto object-contain drop-shadow-[0_25px_35px_rgba(0,0,0,0.6)] animate-float rounded-3xl">
            <?php else: ?><span class="w-60 h-60 rounded-3xl bg-white/10 text-white/50 flex items-center justify-center text-5xl"><i class="fa-regular fa-image"></i></span><?php endif; ?>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="lg:col-span-4 flex flex-col gap-5">
      <?php foreach (array_slice($side, 0, 2) as $i => $sp): ?>
      <a href="/shop/product/<?= e(rawurlencode((string) $sp['slug'])) ?>" class="flex-1 group rounded-[2rem] <?= $i === 0 ? 'bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg' : 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-soft hover:border-brand-500' ?> p-7 relative overflow-hidden flex items-center justify-between transition">
        <div class="space-y-2 z-10">
          <span class="<?= $i === 0 ? 'bg-black/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' ?> px-2.5 py-1 rounded-lg text-[10px] font-bold"><?= !empty($featured) ? 'پیشنهاد ویژه' : 'تازه‌ترین کالا' ?></span>
          <h3 class="text-lg font-black leading-snug <?= $i === 0 ? '' : 'text-slate-900 dark:text-white' ?> line-clamp-2"><?= e($sp['title']) ?></h3>
          <p class="text-xs <?= $i === 0 ? 'text-amber-100' : 'text-slate-500 dark:text-slate-400' ?> font-medium"><?= number_format(Catalog::effectivePrice($sp)) ?> <?= e($currency) ?></p>
        </div>
        <?php if (($sp['image'] ?? '') !== ''): ?><img src="<?= e($sp['image']) ?>" alt="" class="w-28 h-28 object-contain rounded-2xl group-hover:scale-110 transition duration-300">
        <?php else: ?><span class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800 text-slate-300 flex items-center justify-center text-2xl"><i class="fa-regular fa-image"></i></span><?php endif; ?>
      </a>
      <?php endforeach; ?>
      <?php if (count($side) < 2): ?>
      <a href="/shop/track" class="flex-1 group rounded-[2rem] bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-7 relative overflow-hidden flex items-center justify-between shadow-soft hover:border-brand-500 transition">
        <div class="space-y-2 z-10">
          <span class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 px-2.5 py-1 rounded-lg text-[10px] font-extrabold">پیگیری سریع</span>
          <h3 class="text-lg font-black text-slate-900 dark:text-white">سفارشت را با کد پیگیری دنبال کن</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">کد VSL و شماره موبایل کافی است</p>
        </div>
        <div class="w-16 h-16 rounded-3xl bg-brand-50 text-brand-600 flex items-center justify-center text-2xl group-hover:rotate-12 transition">
          <i class="fa-solid fa-truck-fast"></i>
        </div>
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-10">
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-soft flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-xl flex-shrink-0"><i class="fa-solid fa-bolt-lightning"></i></div>
      <div>
        <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">ارسال سفارش</h4>
        <p class="text-[11px] text-slate-400 mt-0.5">هزینه و شرایط از تنظیمات فروشگاه</p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-soft flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl flex-shrink-0"><i class="fa-solid fa-box-open"></i></div>
      <div>
        <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white"><?= count($products) ?> کالا</h4>
        <p class="text-[11px] text-slate-400 mt-0.5">منتشر شده و آماده‌ی خرید</p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-soft flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl flex-shrink-0"><i class="fa-solid fa-layer-group"></i></div>
      <div>
        <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white"><?= count($categories ?? []) ?> دسته‌بندی</h4>
        <p class="text-[11px] text-slate-400 mt-0.5">مرور بر اساس دسته‌ی کالا</p>
      </div>
    </div>
    <div class="bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-soft flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl flex-shrink-0"><i class="fa-solid fa-heart"></i></div>
      <div>
        <h4 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">علاقه‌مندی‌ها</h4>
        <p class="text-[11px] text-slate-400 mt-0.5">کالاهای محبوبت را با قلب ذخیره کن</p>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($categories)): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14">
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-black text-slate-900 dark:text-white">دسته‌بندی‌ها</h2>
  </div>
  <div class="grid grid-cols-3 sm:grid-cols-6 gap-4">
    <?php foreach ($categories as $c): $n = 0; foreach ($products as $pp) { if ((int) $pp['category_id'] === (int) $c['id']) { $n++; } } ?>
    <a href="/shop?cat=<?= e(rawurlencode((string) $c['slug'])) ?>" class="group bg-white dark:bg-slate-900 p-5 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm hover:shadow-md hover:border-brand-500 text-center transition-all duration-300">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-50 dark:bg-slate-800 group-hover:bg-brand-50 text-slate-700 dark:text-slate-300 group-hover:text-brand-600 flex items-center justify-center text-2xl transition duration-300"><i class="fa-solid fa-layer-group"></i></div>
      <h3 class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-3 group-hover:text-brand-600"><?= e($c['title']) ?></h3>
      <span class="text-[10px] text-slate-400"><?= $n ?> کالا</span>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($featured)): ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14">
  <div class="bg-gradient-to-l from-rose-600 via-rose-500 to-orange-500 rounded-[2.2rem] p-6 sm:p-8 text-white shadow-xl">
    <div class="flex items-center gap-3.5 mb-7">
      <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl">
        <i class="fa-solid fa-fire-flame-curved"></i>
      </div>
      <div>
        <h2 class="text-xl sm:text-2xl font-black">پیشنهادهای ویژه</h2>
        <p class="text-rose-100 text-xs mt-0.5">منتخب مدیر فروشگاه — با آیکون چشم، سریع بررسی کنید.</p>
      </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <?php foreach ($featured as $fp): ?><?= View::render('shop', 'front', 'card', ['product' => $fp, 'wishlistIds' => $wishlistIds ?? [], 'csrf' => $csrf, 'currency' => $currency, 'catTitles' => $catTitles, 'variantsMap' => $variantsMap ?? []]) ?><?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>

<div id="products" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14">
  <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200/70 dark:border-slate-800 shadow-soft mb-6">
    <form method="get" action="/shop" class="flex flex-wrap items-center gap-3">
      <?php if (!empty($activeCat)): ?><input type="hidden" name="cat" value="<?= e($activeCat['slug']) ?>"><?php endif; ?>
      <input type="text" name="q" value="<?= e($f['q']) ?>" placeholder="جستجو در محصولات..." class="flex-1 min-w-[12rem] bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs outline-none focus:border-brand-500">
      <input type="number" name="max_price" value="<?= $f['max_price'] > 0 ? (int) $f['max_price'] : '' ?>" min="0" placeholder="سقف قیمت (تومان)" class="w-40 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs outline-none focus:border-brand-500">
      <select name="sort" class="bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-xs outline-none focus:border-brand-500">
        <option value=""<?= $f['sort'] === '' ? ' selected' : '' ?>>مرتب‌سازی پیش‌فرض</option>
        <option value="cheapest"<?= $f['sort'] === 'cheapest' ? ' selected' : '' ?>>ارزان‌ترین</option>
        <option value="expensive"<?= $f['sort'] === 'expensive' ? ' selected' : '' ?>>گران‌ترین</option>
        <option value="newest"<?= $f['sort'] === 'newest' ? ' selected' : '' ?>>جدیدترین</option>
      </select>
      <label class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300">
        <input type="checkbox" name="sale" value="1"<?= !empty($f['sale']) ? ' checked' : '' ?> class="accent-indigo-600"> فقط فروش ویژه
      </label>
      <button class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold">اعمال فیلتر</button>
      <?php if (!empty($filtersActive)): ?><a href="/shop" class="text-xs font-bold text-rose-500 hover:underline">پاکسازی</a><?php endif; ?>
    </form>
  </div>

  <?php if (empty($products)): ?>
    <p class="text-sm text-slate-500 dark:text-slate-400">با این فیلترها محصولی پیدا نشد. <a href="/shop" class="text-brand-600 font-bold">نمایش همه</a></p>
  <?php else: ?>
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-black text-slate-900 dark:text-white"><?= !empty($activeCat) ? e($activeCat['title']) : 'همه‌ی محصولات' ?></h2>
    <span class="text-xs bg-brand-50 text-brand-600 px-3 py-1 rounded-full font-bold"><?= count($products) ?> محصول</span>
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php foreach ($products as $p): ?><?= View::render('shop', 'front', 'card', ['product' => $p, 'wishlistIds' => $wishlistIds ?? [], 'csrf' => $csrf, 'currency' => $currency, 'catTitles' => $catTitles, 'variantsMap' => $variantsMap ?? []]) ?><?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
