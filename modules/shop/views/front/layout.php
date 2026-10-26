<?php
// تم یکپارچه: متغیرهای قالب ادمین (storage/admin-theme.json) روی فروشگاه هم می‌نشیند؛
// حالت تاریک/روشن با همان کلید localStorage ادمین خوانده می‌شود.
$__theme = \Modules\Admin\AdminTheme::load(dirname(__DIR__, 4));
$__dark = ($__theme['default_theme'] ?? 'dark') === 'dark';
?>
<!doctype html>
<html lang="fa" dir="rtl"<?= $__dark ? ' class="dark"' : '' ?>>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — <?= e($shopTitle) ?></title>
<script>try{var _t=localStorage.getItem('vasleh_theme');if(_t==='light'){document.documentElement.classList.remove('dark')}else if(_t==='dark'){document.documentElement.classList.add('dark')}}catch(e){}</script>
<link rel="stylesheet" href="/cms.css">
<link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
<style>
  <?= \Modules\Admin\AdminTheme::varsCss($__theme) ?>
  body{font-family:'Vazirmatn',system-ui,sans-serif;margin:0;background:var(--admin-main-bg);color:var(--admin-text)}
  .shop-wrap{max-width:72rem;margin:0 auto;padding:1rem}
  .shop-header{display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;border-bottom:1px solid var(--admin-border);padding-bottom:.8rem;margin-bottom:1rem}
  .shop-header .brand{font-size:1.3rem;font-weight:700}
  .shop-nav{display:flex;flex-wrap:wrap;gap:.4rem}
  .shop-nav a{border:1px solid var(--admin-border);background:var(--admin-panel-bg);border-radius:2rem;padding:.35rem .9rem;text-decoration:none;color:var(--admin-text)}
  .shop-nav a:hover{filter:brightness(1.08)}
  .cats{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem}
  .cats a{border:1px solid var(--admin-border);background:var(--admin-panel-bg);border-radius:2rem;padding:.25rem .8rem;text-decoration:none;color:var(--admin-text)}
  .cats a.active{background:var(--admin-accent);border-color:var(--admin-accent);color:#fff}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(15rem,1fr));gap:1rem}
  .p-card{background:var(--admin-panel-bg);border:1px solid var(--admin-border);border-radius:var(--admin-radius);padding:1rem;text-decoration:none;color:var(--admin-text);display:block}
  .p-card img{width:100%;border-radius:.5rem;aspect-ratio:1;object-fit:cover;background:var(--admin-main-bg)}
  .p-card h3{margin:.6rem 0 .3rem}
  .price{font-weight:700}
  .old-price{text-decoration:line-through;color:var(--admin-muted);font-weight:400;margin-inline-start:.4rem}
  .btn{display:inline-block;border:1px solid var(--admin-accent);background:var(--admin-accent);color:#fff;border-radius:.5rem;padding:.5rem 1.1rem;text-decoration:none;cursor:pointer}
  .btn.light{background:var(--admin-panel-bg);color:var(--admin-text);border-color:var(--admin-border)}
  .btn.red{background:#b91c1c;border-color:#b91c1c}
  table{width:100%;border-collapse:collapse;background:var(--admin-panel-bg)}
  th,td{border:1px solid var(--admin-border);padding:.5rem .6rem;text-align:right}
  input,select,textarea{font:inherit;padding:.5rem;border:1px solid var(--admin-border);border-radius:.5rem;width:100%;box-sizing:border-box;background:var(--admin-panel-bg);color:var(--admin-text)}
  label{display:block;margin-bottom:.8rem}
  .flash{background:#e8f7ee;border:1px solid #34a853;border-radius:.5rem;padding:.6rem 1rem;margin-bottom:1rem;color:#14532d}
  .error{background:#fdecec;border:1px solid #d33;border-radius:.5rem;padding:.6rem 1rem;margin-bottom:1rem;color:#7f1d1d}
  .muted{color:var(--admin-muted)}
  footer{border-top:1px solid var(--admin-border);margin-top:2rem;padding-top:1rem;color:var(--admin-muted)}
  .specs{background:var(--admin-panel-bg);border:1px solid var(--admin-border);border-radius:var(--admin-radius);padding:1rem;margin:1rem 0}
  .specs div{display:flex;justify-content:space-between;border-bottom:1px dashed var(--admin-border);padding:.35rem 0}
  .specs div:last-child{border-bottom:0}
</style>
</head>
<body>
<div class="shop-wrap">
  <div class="shop-header">
    <div class="brand"><?= e($shopTitle) ?></div>
    <nav class="shop-nav">
      <a href="/shop">فروشگاه</a>
      <a href="/shop/pricelist">لیست قیمت</a>
      <a href="/shop/track">پیگیری سفارش</a>
      <a href="/shop/cart">سبد خرید<?= $cartCount > 0 ? ' (' . (int) $cartCount . ')' : '' ?></a>
    </nav>
  </div>
  <?= $content ?>
  <footer><?= e($shopTitle) ?> — قدرت‌گرفته از وصله</footer>
</div>
</body>
</html>
