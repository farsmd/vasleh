<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — <?= e($shopTitle) ?></title>
<link rel="stylesheet" href="/cms.css">
<style>
  body{font-family:Tahoma,system-ui,sans-serif;margin:0;background:#fafafa;color:#222}
  .shop-wrap{max-width:72rem;margin:0 auto;padding:1rem}
  .shop-header{display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;border-bottom:1px solid #e5e5e5;padding-bottom:.8rem;margin-bottom:1rem}
  .shop-header .brand{font-size:1.3rem;font-weight:700}
  .shop-nav{display:flex;flex-wrap:wrap;gap:.4rem}
  .shop-nav a{border:1px solid #ddd;background:#fff;border-radius:2rem;padding:.35rem .9rem;text-decoration:none;color:#222}
  .shop-nav a:hover{background:#f0f0f0}
  .cats{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:1rem}
  .cats a{border:1px solid #ddd;background:#fff;border-radius:2rem;padding:.25rem .8rem;text-decoration:none;color:#333}
  .cats a.active{background:#222;color:#fff}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(15rem,1fr));gap:1rem}
  .p-card{background:#fff;border:1px solid #e5e5e5;border-radius:.8rem;padding:1rem;text-decoration:none;color:#222;display:block}
  .p-card img{width:100%;border-radius:.5rem;aspect-ratio:1;object-fit:cover;background:#f0f0f0}
  .p-card h3{margin:.6rem 0 .3rem}
  .price{font-weight:700}
  .old-price{text-decoration:line-through;color:#999;font-weight:400;margin-inline-start:.4rem}
  .btn{display:inline-block;border:1px solid #222;background:#222;color:#fff;border-radius:.5rem;padding:.5rem 1.1rem;text-decoration:none;cursor:pointer}
  .btn.light{background:#fff;color:#222}
  .btn.red{background:#b91c1c;border-color:#b91c1c}
  table{width:100%;border-collapse:collapse;background:#fff}
  th,td{border:1px solid #e5e5e5;padding:.5rem .6rem;text-align:right}
  input,select,textarea{font:inherit;padding:.5rem;border:1px solid #ccc;border-radius:.5rem;width:100%;box-sizing:border-box}
  label{display:block;margin-bottom:.8rem}
  .flash{background:#e8f7ee;border:1px solid #34a853;border-radius:.5rem;padding:.6rem 1rem;margin-bottom:1rem}
  .error{background:#fdecec;border:1px solid #d33;border-radius:.5rem;padding:.6rem 1rem;margin-bottom:1rem}
  .muted{color:#777}
  footer{border-top:1px solid #e5e5e5;margin-top:2rem;padding-top:1rem;color:#777}
  .specs{background:#fff;border:1px solid #e5e5e5;border-radius:.8rem;padding:1rem;margin:1rem 0}
  .specs div{display:flex;justify-content:space-between;border-bottom:1px dashed #eee;padding:.35rem 0}
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
