<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — <?= e($shopTitle) ?></title>
<link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
<link rel="stylesheet" href="/assets/taskyar/fa/css/all.min.css">
<link rel="stylesheet" href="/assets/shop/shop.css">
<?php $__shopTheme = \Modules\Admin\AdminTheme::load(dirname(__DIR__, 4)); ?>
<style>
  <?= \Modules\Admin\AdminTheme::varsCss($__shopTheme) ?>
  /* رنگ اصلی فروشگاه از همان رنگ اصلی قالب ادمین می‌آید (پیش‌فرض: همان آبی نکسوس)؛
     قالب فروشگاه روشن می‌ماند و فقط لهجه‌ی رنگی با تنظیم ادمین هماهنگ می‌شود. */
  .bg-blue-600{background-color:var(--admin-accent)}
  .hover\:bg-blue-500:hover{background-color:var(--admin-accent);filter:brightness(1.08)}
  .text-blue-600{color:var(--admin-accent)}
  .hover\:text-blue-600:hover{color:var(--admin-accent)}
  .bg-blue-50{background-color:color-mix(in srgb,var(--admin-accent) 8%,#fff)}
  .border-blue-100{border-color:color-mix(in srgb,var(--admin-accent) 25%,#fff)}
  .focus\:border-blue-500:focus{border-color:var(--admin-accent)}
</style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col" style="font-family:'Vazirmatn',system-ui,sans-serif">
<header class="bg-white/95 backdrop-blur-md border-b border-slate-100 sticky top-0 z-40">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-20 gap-4">
      <a href="/shop" class="flex items-center gap-2.5 shrink-0">
        <span class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-blue-700 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/25">
          <i class="fa-solid fa-gem text-lg"></i>
        </span>
        <span class="text-xl font-black tracking-tight text-slate-900 leading-tight"><?= e($shopTitle) ?></span>
      </a>
      <nav class="flex items-center gap-2 sm:gap-3">
        <a href="/shop" class="hidden sm:inline-block px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-blue-600 hover:bg-slate-50">فروشگاه</a>
        <a href="/shop/pricelist" class="hidden sm:inline-block px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-blue-600 hover:bg-slate-50">لیست قیمت</a>
        <a href="/shop/track" class="hidden sm:inline-block px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-blue-600 hover:bg-slate-50">پیگیری سفارش</a>
        <a href="/shop/cart" class="relative flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-blue-50 text-blue-600 hover:bg-blue-100/70 font-medium border border-blue-100">
          <i class="fa-solid fa-bag-shopping text-lg"></i>
          <span class="text-xs font-bold hidden sm:inline">سبد خرید</span>
          <?php if ($cartCount > 0): ?><span class="w-5 h-5 bg-blue-600 text-white rounded-full text-xs flex items-center justify-center font-bold"><?= (int) $cartCount ?></span><?php endif; ?>
        </a>
      </nav>
    </div>
    <?php if (!empty($categories)): ?>
    <div class="flex items-center gap-6 border-t border-slate-100 py-2.5 text-xs font-medium text-slate-600 overflow-x-auto">
      <a href="/shop" class="flex items-center gap-2 text-slate-900 font-bold hover:text-blue-600 shrink-0">
        <i class="fa-solid fa-bars text-blue-600"></i><span>همه‌ی کالاها</span>
      </a>
      <span class="w-px h-4 bg-slate-200 shrink-0"></span>
      <?php foreach ($categories as $c): ?>
      <a href="/shop?cat=<?= e(rawurlencode((string) $c['slug'])) ?>" class="hover:text-blue-600 shrink-0"><?= e($c['title']) ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</header>

<main class="flex-1 pb-16">
<?= $content ?>
</main>

<footer class="bg-slate-950 text-slate-400 text-xs border-t border-slate-800 mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
      <div class="flex items-center gap-2.5">
        <span class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white"><i class="fa-solid fa-gem"></i></span>
        <span class="text-lg font-black text-white"><?= e($shopTitle) ?></span>
      </div>
      <nav class="flex flex-wrap items-center gap-5">
        <a href="/shop" class="hover:text-white">فروشگاه</a>
        <a href="/shop/pricelist" class="hover:text-white">لیست قیمت</a>
        <a href="/shop/track" class="hover:text-white">پیگیری سفارش</a>
        <a href="/shop/cart" class="hover:text-white">سبد خرید</a>
      </nav>
    </div>
    <div class="border-t border-slate-900 mt-8 pt-5 text-slate-500 text-[11px]">
      <p>تمامی حقوق این فروشگاه محفوظ است.</p>
    </div>
  </div>
</footer>
</body>
</html>
