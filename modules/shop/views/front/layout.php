<?php
$ver = '8.2.3';
$vf = dirname(__DIR__, 4) . '/version.json';
if (is_file($vf)) { $vj = json_decode((string) file_get_contents($vf), true); if (is_array($vj)) { $ver = (int) $vj['core'] . '.' . (int) $vj['system'] . '.' . (int) $vj['other']; } }
$drawerTotal = 0; foreach (($cartItems ?? []) as $ci) { $drawerTotal += (int) $ci['line_total']; }
$accent = preg_match('/^#[0-9a-fA-F]{6}$/', (string) ($shopAccent ?? '')) ? (string) $shopAccent : '#4f46e5';
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — <?= e($shopTitle) ?></title>
<script>(function(){try{if(localStorage.getItem('shop_theme')==='dark'){document.documentElement.classList.add('dark');}}catch(e){}})();</script>
<link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
<link rel="stylesheet" href="/assets/taskyar/fa/css/all.min.css">
<link rel="stylesheet" href="/assets/shop/shop.css?v=<?= e($ver) ?>">
<style>
  :root{--shop-accent:<?= e($accent) ?>}
  .bg-brand-600{background-color:var(--shop-accent)}
  .hover\:bg-brand-500:hover{background-color:var(--shop-accent);filter:brightness(1.08)}
  .text-brand-600{color:var(--shop-accent)}
  .hover\:text-brand-600:hover{color:var(--shop-accent)}
  .bg-brand-50{background-color:color-mix(in srgb,var(--shop-accent) 8%,transparent)}
  .border-brand-600{border-color:var(--shop-accent)}
  .focus\:border-brand-500:focus{border-color:var(--shop-accent)}
  .shadow-glow{box-shadow:0 10px 30px -5px color-mix(in srgb,var(--shop-accent) 40%,transparent)}
  @keyframes shopFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
  .animate-float{animation:shopFloat 4s ease-in-out infinite}
  .no-scrollbar::-webkit-scrollbar{display:none}
  .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
</style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased min-h-screen flex flex-col transition-colors duration-300" style="font-family:'Vazirmatn',system-ui,sans-serif">
<header class="bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-b border-slate-200/70 dark:border-slate-800 sticky top-0 z-40 transition-colors">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-20 gap-4">
      <a href="/shop" class="flex items-center gap-3 shrink-0 group">
        <span class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-brand-600 to-violet-500 flex items-center justify-center text-white shadow-glow group-hover:scale-105 transition">
          <i class="fa-solid fa-sparkles text-lg"></i>
        </span>
        <span>
          <span class="text-xl font-black tracking-tight text-slate-900 dark:text-white block leading-none"><?= e($shopTitle) ?></span>
          <span class="text-[10px] tracking-widest text-brand-600 font-extrabold uppercase mt-1 block">SHOP</span>
        </span>
      </a>
      <div class="flex-1 max-w-xl relative hidden md:block" id="shop-search-box">
        <div class="relative">
          <input id="shop-search-input" type="text" autocomplete="off" placeholder="جستجوی محصولات..."
            class="w-full bg-slate-100 dark:bg-slate-800/90 focus:bg-white dark:focus:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm rounded-2xl pr-11 pl-4 py-3.5 border border-transparent focus:border-brand-500 outline-none transition-all">
          <i class="fa-solid fa-magnifying-glass absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        </div>
        <div id="shop-search-results" class="hidden absolute top-full right-0 left-0 mt-2 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-800 p-3 z-50"></div>
      </div>
      <div class="flex items-center gap-2 sm:gap-3">
        <button onclick="shopToggleTheme()" class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-amber-400 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition" title="تغییر حالت شب و روز">
          <i id="shop-theme-icon" class="fa-solid fa-moon text-sm"></i>
        </button>
        <a href="/shop/wishlist" class="relative w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-rose-500 flex items-center justify-center transition" title="علاقه‌مندی‌ها">
          <i class="fa-regular fa-heart text-base"></i>
          <?php if ($wishlistCount > 0): ?><span class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white rounded-full text-[10px] flex items-center justify-center font-bold"><?= (int) $wishlistCount ?></span><?php endif; ?>
        </a>
        <button onclick="shopToggleDrawer(true)" class="relative flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white shadow-glow transition">
          <i class="fa-solid fa-bag-shopping text-sm"></i>
          <span class="text-xs font-bold hidden sm:inline">سبد خرید</span>
          <span class="px-1.5 py-0.5 bg-white text-brand-600 rounded-lg text-[11px] font-black"><?= (int) $cartCount ?></span>
        </button>
      </div>
    </div>
    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/80 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-400 overflow-x-auto no-scrollbar">
      <div class="flex items-center gap-6 min-w-max">
        <a href="/shop" class="flex items-center gap-2 text-slate-900 dark:text-white font-extrabold hover:text-brand-600 transition py-1">
          <i class="fa-solid fa-grip text-brand-600 text-sm"></i><span>دسته‌بندی کالاها</span>
        </a>
        <span class="w-px h-4 bg-slate-200 dark:bg-slate-800"></span>
        <?php foreach (($categories ?? []) as $c): ?>
        <a href="/shop?cat=<?= e(rawurlencode((string) $c['slug'])) ?>" class="hover:text-brand-600 transition shrink-0"><?= e($c['title']) ?></a>
        <?php endforeach; ?>
        <a href="/shop/pricelist" class="hover:text-brand-600 transition shrink-0">لیست قیمت</a>
        <a href="/shop/track" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline flex items-center gap-1.5 shrink-0">
          <i class="fa-solid fa-truck-fast"></i> پیگیری سفارش
        </a>
      </div>
      <form method="get" action="/shop" class="hidden lg:block min-w-max mr-6">
        <input type="text" name="q" placeholder="جستجو..." class="bg-slate-100 dark:bg-slate-800 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-brand-500 border border-transparent w-44">
      </form>
    </div>
  </div>
</header>

<main class="flex-1 pb-24">
<?= $content ?>
</main>

<footer class="bg-slate-900 text-slate-400 text-xs border-t border-slate-800 mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-xl bg-brand-600 text-white flex items-center justify-center">
        <i class="fa-solid fa-sparkles"></i>
      </div>
      <span class="text-white font-extrabold text-sm"><?= e($shopTitle) ?></span>
    </div>
    <div class="flex items-center gap-5">
      <a href="/shop" class="hover:text-white">فروشگاه</a>
      <a href="/shop/track" class="hover:text-white">پیگیری سفارش</a>
      <a href="/shop/wishlist" class="hover:text-white">علاقه‌مندی‌ها</a>
    </div>
  </div>
</footer>

<div id="shop-drawer-backdrop" onclick="shopToggleDrawer(false)" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300"></div>
<aside id="shop-drawer" class="fixed top-0 left-0 bottom-0 w-full max-w-md bg-white dark:bg-slate-900 z-50 shadow-2xl border-r border-slate-200 dark:border-slate-800 flex flex-col transform -translate-x-full transition-transform duration-300">
  <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
    <div class="flex items-center gap-2.5">
      <div class="w-9 h-9 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
        <i class="fa-solid fa-bag-shopping"></i>
      </div>
      <div>
        <h3 class="text-sm font-black text-slate-900 dark:text-white">سبد خرید سریع</h3>
        <span class="text-[11px] text-slate-400"><?= (int) $cartCount ?> کالا در سبد شماست</span>
      </div>
    </div>
    <button onclick="shopToggleDrawer(false)" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-rose-500 flex items-center justify-center transition">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
  <div class="flex-1 overflow-y-auto p-5 space-y-4">
    <?php if (empty($cartItems)): ?>
      <div class="text-center py-12 text-slate-400 text-xs">سبد خرید شما فعلاً خالی است.</div>
    <?php else: foreach ($cartItems as $row): ?>
    <div class="flex items-center gap-3.5 bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
      <?php if (($row['product']['image'] ?? '') !== ''): ?><img src="<?= e($row['product']['image']) ?>" class="w-16 h-16 object-contain rounded-xl bg-white dark:bg-slate-900 p-1">
      <?php else: ?><span class="w-16 h-16 rounded-xl bg-white dark:bg-slate-900 text-slate-300 flex items-center justify-center text-xl"><i class="fa-regular fa-image"></i></span><?php endif; ?>
      <div class="flex-1 min-w-0">
        <a href="/shop/product/<?= e(rawurlencode((string) $row['product']['slug'])) ?>" class="text-xs font-extrabold text-slate-900 dark:text-white truncate block"><?= e($row['product']['title']) ?></a>
        <?php if ($row['options_text'] !== ''): ?><span class="text-[11px] text-slate-400"><?= e($row['options_text']) ?></span><?php endif; ?>
        <span class="text-xs font-black text-brand-600 block mt-1"><?= number_format($row['unit_price']) ?> تومان × <?= (int) $row['qty'] ?></span>
      </div>
      <form method="post" action="/shop/cart/remove">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="key" value="<?= e($row['key']) ?>">
        <button class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center" title="حذف"><i class="fa-solid fa-trash text-xs"></i></button>
      </form>
    </div>
    <?php endforeach; endif; ?>
  </div>
  <div class="p-5 bg-slate-50 dark:bg-slate-800/60 border-t border-slate-200/80 dark:border-slate-800 space-y-4">
    <div class="flex items-center justify-between">
      <span class="text-xs font-bold text-slate-500 dark:text-slate-400">جمع کل سبد خرید:</span>
      <div>
        <span class="text-lg font-black text-slate-900 dark:text-white"><?= number_format($drawerTotal) ?></span>
        <span class="text-xs text-slate-400">تومان</span>
      </div>
    </div>
    <a href="/shop/checkout" class="w-full py-4 rounded-2xl bg-brand-600 hover:bg-brand-500 text-white font-black text-xs sm:text-sm shadow-glow flex items-center justify-center gap-2 transition">
      <span>ورود به فرآیند خرید و تسویه حساب</span>
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <a href="/shop/cart" class="w-full py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs text-center block">مشاهده کامل سبد</a>
  </div>
</aside>

<div id="quick-view-modal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm">
  <div class="bg-white dark:bg-slate-900 rounded-[2rem] max-w-2xl w-full p-6 sm:p-8 border border-slate-200 dark:border-slate-800 shadow-2xl relative">
    <button onclick="shopCloseQuickView()" class="absolute top-5 left-5 w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-rose-500 flex items-center justify-center">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-6 items-center">
      <div class="sm:col-span-5 bg-slate-50 dark:bg-slate-800 rounded-2xl p-6 flex items-center justify-center min-h-[180px]">
        <img id="qv-img" src="" alt="" class="h-44 object-contain rounded-xl">
        <span id="qv-noimg" class="hidden w-24 h-24 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-300 flex items-center justify-center text-4xl"><i class="fa-regular fa-image"></i></span>
      </div>
      <div class="sm:col-span-7 space-y-4">
        <h3 id="qv-title" class="text-lg font-black text-slate-900 dark:text-white leading-snug"></h3>
        <div class="pt-2 flex items-baseline gap-2">
          <span id="qv-price" class="text-2xl font-black text-brand-600"></span>
          <span class="text-xs text-slate-400">تومان</span>
        </div>
        <div class="flex gap-2.5 pt-2">
          <form id="qv-add-form" method="post" action="/shop/cart/add" class="flex-1">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="product_id" id="qv-product-id" value="0">
            <input type="hidden" name="qty" value="1">
            <button class="w-full py-3 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-xs font-black shadow-glow transition">
              <i class="fa-solid fa-cart-plus ml-1"></i> افزودن به سبد
            </button>
          </form>
          <a id="qv-page-link" href="/shop" class="px-4 py-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-200 transition">صفحه کامل محصول</a>
        </div>
        <p id="qv-variants-note" class="hidden text-[11px] text-slate-400">این محصول چند نوع دارد؛ برای انتخاب نوع، به صفحه کامل آن بروید.</p>
      </div>
    </div>
  </div>
</div>

<script>
function shopToggleTheme(){
  var html=document.documentElement;
  var dark=html.classList.toggle('dark');
  try{localStorage.setItem('shop_theme',dark?'dark':'light');}catch(e){}
  var icon=document.getElementById('shop-theme-icon');
  if(icon)icon.className=dark?'fa-solid fa-sun text-sm':'fa-solid fa-moon text-sm';
}
(function(){if(document.documentElement.classList.contains('dark')){var i=document.getElementById('shop-theme-icon');if(i)i.className='fa-solid fa-sun text-sm';}})();
function shopToggleDrawer(open){
  var d=document.getElementById('shop-drawer'),b=document.getElementById('shop-drawer-backdrop');
  if(open){b.classList.remove('hidden');setTimeout(function(){b.classList.remove('opacity-0');},10);d.classList.remove('-translate-x-full');}
  else{b.classList.add('opacity-0');d.classList.add('-translate-x-full');setTimeout(function(){b.classList.add('hidden');},300);}
}
function shopOpenQuickView(btn){
  var img=document.getElementById('qv-img'),noimg=document.getElementById('qv-noimg');
  document.getElementById('qv-title').textContent=btn.getAttribute('data-title')||'';
  document.getElementById('qv-price').textContent=btn.getAttribute('data-price')||'';
  document.getElementById('qv-product-id').value=btn.getAttribute('data-id')||'0';
  document.getElementById('qv-page-link').href=btn.getAttribute('data-url')||'/shop';
  var src=btn.getAttribute('data-image')||'';
  if(src){img.src=src;img.classList.remove('hidden');noimg.classList.add('hidden');}
  else{img.classList.add('hidden');noimg.classList.remove('hidden');}
  var hasVar=btn.getAttribute('data-variants')==='1';
  document.getElementById('qv-add-form').style.display=hasVar?'none':'block';
  document.getElementById('qv-variants-note').classList.toggle('hidden',!hasVar);
  var m=document.getElementById('quick-view-modal');
  m.classList.remove('hidden');m.classList.add('flex');
}
function shopCloseQuickView(){
  var m=document.getElementById('quick-view-modal');
  m.classList.add('hidden');m.classList.remove('flex');
}
(function(){
  var input=document.getElementById('shop-search-input');
  var box=document.getElementById('shop-search-results');
  if(!input||!box)return;
  var timer=null;
  function esc(s){return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
  input.addEventListener('input',function(){
    clearTimeout(timer);
    var q=input.value.trim();
    if(q.length<2){box.classList.add('hidden');box.innerHTML='';return;}
    timer=setTimeout(function(){
      fetch('/shop/search?q='+encodeURIComponent(q))
        .then(function(r){return r.json();})
        .then(function(list){
          if(!list.length){box.innerHTML='<p class="text-xs text-slate-400 p-2">چیزی پیدا نشد.</p>';}
          else{
            box.innerHTML=list.map(function(p){
              var img=p.image?'<img src="'+esc(p.image)+'" class="w-11 h-11 object-contain rounded-xl bg-slate-100 dark:bg-slate-700 p-1">':'<span class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-300 flex items-center justify-center"><i class="fa-regular fa-image text-xs"></i></span>';
              return '<a href="'+esc(p.url)+'" class="flex items-center justify-between p-2.5 rounded-2xl hover:bg-slate-50 dark:hover:bg-slate-800 transition"><span class="flex items-center gap-3">'+img+'<span><span class="text-xs font-bold text-slate-800 dark:text-slate-100 block">'+esc(p.title)+'</span><span class="text-[11px] text-brand-600 font-extrabold">'+Number(p.price).toLocaleString('en-US')+' تومان</span></span></span><i class="fa-solid fa-angle-left text-xs text-slate-400"></i></a>';
            }).join('');
          }
          box.classList.remove('hidden');
        })
        .catch(function(){box.classList.add('hidden');});
    },250);
  });
  input.addEventListener('keydown',function(e){
    if(e.key==='Enter'){e.preventDefault();window.location='/shop?q='+encodeURIComponent(input.value.trim());}
  });
  document.addEventListener('click',function(e){
    var wrap=document.getElementById('shop-search-box');
    if(wrap&&!wrap.contains(e.target)){box.classList.add('hidden');}
  });
})();
</script>
</body>
</html>
