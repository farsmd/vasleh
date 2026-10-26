<?php use Core\View; ?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
  <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-2">علاقه‌مندی‌ها</h1>
  <p class="text-sm text-slate-500 dark:text-slate-400 mb-8">کالاهایی که با قلب ذخیره کرده‌اید — فقط در همین مرورگر نگه داشته می‌شوند.</p>
  <?php if (empty($products)): ?>
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-100 dark:border-slate-800 shadow-sm text-center">
      <p class="text-sm text-slate-500 dark:text-slate-400">هنوز چیزی ذخیره نکرده‌اید.</p>
      <p class="mt-4"><a href="/shop" class="px-5 py-3 rounded-2xl bg-brand-600 text-white text-sm font-bold">رفتن به فروشگاه</a></p>
    </div>
  <?php else: ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <?php foreach ($products as $p): ?><?= View::render('shop', 'front', 'card', ['product' => $p, 'wishlistIds' => $wishlistIds ?? [], 'csrf' => $csrf, 'currency' => $currency, 'catTitles' => $catTitles, 'variantsMap' => $variantsMap ?? [], 'wishReturn' => $wishReturn ?? '/shop/wishlist']) ?><?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
