<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($appName) ?> — <?= e($shopTitle) ?></title>
<link rel="stylesheet" href="/assets/bootstrap/bootstrap.rtl.min.css">
<link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
<?php
// نکته: SDK رسمی خودِ تلگرام که initData (امضای ورود) را در اختیار مینی‌اپ
// می‌گذارد؛ بدون آن احراز تلگرامی ممکن نیست. طبق قاعدهٔ بدون-CDN وصله،
// فایل از هاست خودمان سرو می‌شود (assets/telegram/telegram-web-app.js،
// رونوشت همان فایل رسمی) و هیچ اسکریپتی از بیرون لود نمی‌شود.
?>
<script src="/assets/telegram/telegram-web-app.js"></script>
<style>
  body{font-family:'Vazirmatn',system-ui,sans-serif;background:#f1f5f9;color:#0f172a}
  .glass{background:rgba(255,255,255,.85);backdrop-filter:blur(12px);border:1px solid rgba(226,232,240,.9);border-radius:1rem;box-shadow:0 4px 24px rgba(15,23,42,.06)}
  .tg-header{position:sticky;top:0;z-index:30;background:rgba(255,255,255,.9);backdrop-filter:blur(12px);border-bottom:1px solid #e2e8f0}
  .product-img{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:.75rem}
  .price{font-weight:800}
  .old-price{color:#94a3b8;text-decoration:line-through;font-size:.85rem}
  .cart-badge{position:absolute;top:-6px;left:-6px;background:#dc2626;color:#fff;border-radius:999px;font-size:.7rem;padding:0 .35rem}
  .btn-accent{background:#4f46e5;color:#fff;border:none;border-radius:.75rem;padding:.6rem 1rem}
  .btn-accent:disabled{opacity:.6}
  .btn-ghost{background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:.35rem .7rem}
  input,textarea{border:1px solid #e2e8f0;border-radius:.75rem;padding:.6rem .8rem;width:100%;background:#fff}
  input:focus,textarea:focus{outline:none;border-color:#4f46e5}
</style>
</head>
<body>
<header class="tg-header">
  <div class="container py-3 d-flex align-items-center justify-content-between gap-3">
    <div>
      <strong class="fs-5"><?= e($appName) ?></strong>
      <div class="text-muted small"><?= e($shopTitle) ?></div>
    </div>
    <button type="button" class="btn-ghost position-relative" id="cartBtn" title="سبد خرید">
      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      <span class="cart-badge" id="cartCount">0</span>
    </button>
  </div>
</header>

<main class="container py-3">
  <form method="get" action="/tgapp" class="d-flex gap-2 mb-3">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="جستجوی محصولات...">
    <button type="submit" class="btn-accent">جستجو</button>
  </form>

  <?php if ($products === []): ?>
    <div class="glass p-4 text-center text-muted">محصولی پیدا نشد.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($products as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="glass p-2 h-100 d-flex flex-column">
            <img class="product-img" src="<?= e($p['image']) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
            <strong class="mt-2"><?= e($p['title']) ?></strong>
            <?php if ($p['short_desc'] !== ''): ?><div class="text-muted small"><?= e($p['short_desc']) ?></div><?php endif; ?>
            <div class="mt-auto pt-2 d-flex align-items-center justify-content-between gap-2">
              <span>
                <?php if ($p['sale_price'] > 0 && $p['orig_price'] > $p['price']): ?><span class="old-price"><?= number_format($p['orig_price']) ?></span><?php endif; ?>
                <span class="price"><?= number_format($p['price']) ?> <?= e($currency) ?></span>
              </span>
              <button type="button" class="btn-accent add-btn" data-id="<?= (int) $p['id'] ?>" data-title="<?= e($p['title']) ?>" data-price="<?= (int) $p['price'] ?>">افزودن</button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <section class="glass p-3 mt-4" id="cartSection" style="display:none">
    <h2 class="fs-5">سبد خرید</h2>
    <div id="cartItems"></div>
    <div class="d-flex justify-content-between mt-2">
      <span>جمع اقلام:</span>
      <strong><span id="cartTotal">0</span> <?= e($currency) ?></strong>
    </div>
    <p class="text-muted small mt-1">هزینه ارسال و مالیات هنگام ثبت نهایی حساب می‌شود.</p>
  </section>

  <section class="glass p-3 mt-3">
    <h2 class="fs-5">ثبت سفارش</h2>
    <form id="orderForm" class="d-flex flex-column gap-2 mt-2">
      <input type="text" name="name" placeholder="نام و نام خانوادگی" required>
      <input type="tel" name="mobile" placeholder="شماره موبایل" dir="ltr" required>
      <textarea name="address" rows="2" placeholder="نشانی تحویل"></textarea>
      <?php if ($paymentMethods !== []): ?>
        <div class="d-flex flex-column gap-1">
          <span class="small text-muted">روش پرداخت:</span>
          <?php foreach ($paymentMethods as $i => $m): ?>
            <label class="d-flex gap-2 align-items-center">
              <input type="radio" name="payment_method" value="<?= e($m[0]) ?>" style="width:auto" <?= $i === 0 ? 'checked' : '' ?>>
              <?= e($m[1]) ?>
            </label>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <button type="submit" class="btn-accent" id="orderBtn">ثبت سفارش</button>
    </form>
    <div id="orderResult" class="mt-3" style="display:none"></div>
  </section>
</main>

<script>
(function(){
  var tg = (window.Telegram && window.Telegram.WebApp) ? window.Telegram.WebApp : null;
  if (tg) { try { tg.ready(); tg.expand(); } catch (e) {} }
  var initData = tg ? (tg.initData || '') : '';
  var KEY = 'tgapp_cart';
  function load(){ try { return JSON.parse(localStorage.getItem(KEY) || '{}'); } catch (e) { return {}; } }
  function save(c){ localStorage.setItem(KEY, JSON.stringify(c)); }
  function fmt(n){ return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
  function render(){
    var cart = load(), count = 0, total = 0, html = '';
    Object.keys(cart).forEach(function(id){
      var it = cart[id]; count += it.qty; total += it.qty * it.price;
      html += '<div class="d-flex justify-content-between align-items-center border-bottom py-2">'
        + '<span>' + it.title + '</span>'
        + '<span class="d-flex gap-1 align-items-center">'
        + '<button type="button" class="btn-ghost" data-dec="' + id + '">−</button>'
        + '<span>' + it.qty + '</span>'
        + '<button type="button" class="btn-ghost" data-inc="' + id + '">+</button>'
        + '<button type="button" class="btn-ghost" data-del="' + id + '">حذف</button>'
        + '</span></div>';
    });
    document.getElementById('cartCount').textContent = count;
    document.getElementById('cartTotal').textContent = fmt(total);
    document.getElementById('cartItems').innerHTML = html || '<p class="text-muted">سبد خالی است.</p>';
    document.getElementById('cartSection').style.display = count > 0 ? '' : 'none';
    document.querySelectorAll('[data-inc]').forEach(function(b){ b.onclick = function(){ var c = load(); c[b.dataset.inc].qty++; save(c); render(); }; });
    document.querySelectorAll('[data-dec]').forEach(function(b){ b.onclick = function(){ var c = load(); var it = c[b.dataset.dec]; it.qty--; if (it.qty <= 0) delete c[b.dataset.dec]; save(c); render(); }; });
    document.querySelectorAll('[data-del]').forEach(function(b){ b.onclick = function(){ var c = load(); delete c[b.dataset.del]; save(c); render(); }; });
  }
  document.querySelectorAll('.add-btn').forEach(function(b){
    b.onclick = function(){
      var c = load(), id = b.dataset.id;
      if (!c[id]) c[id] = { title: b.dataset.title, price: parseInt(b.dataset.price, 10) || 0, qty: 0 };
      c[id].qty++; save(c); render();
    };
  });
  document.getElementById('cartBtn').onclick = function(){
    document.getElementById('cartSection').scrollIntoView({ behavior: 'smooth' });
  };
  document.getElementById('orderForm').onsubmit = function(ev){
    ev.preventDefault();
    var cart = load(), items = [];
    Object.keys(cart).forEach(function(id){ items.push({ id: parseInt(id, 10), qty: cart[id].qty }); });
    if (items.length === 0) { alert('سبد خرید خالی است.'); return; }
    var btn = document.getElementById('orderBtn'); btn.disabled = true;
    var form = ev.target;
    var body = new URLSearchParams();
    body.set('name', form.name.value);
    body.set('mobile', form.mobile.value);
    body.set('address', form.address.value);
    var pm = form.querySelector('input[name="payment_method"]:checked');
    body.set('payment_method', pm ? pm.value : '');
    body.set('init_data', initData);
    body.set('items', JSON.stringify(items));
    fetch('/tgapp/order', { method: 'POST', body: body })
      .then(function(r){ return r.json(); })
      .then(function(res){
        var box = document.getElementById('orderResult'); box.style.display = '';
        if (res.ok) {
          localStorage.removeItem(KEY); render();
          var html = '<div class="alert alert-success">سفارش شما با کد <strong dir="ltr">' + res.code + '</strong> ثبت شد.</div>';
          if (res.payment_url) {
            html += '<p><a class="btn-accent d-inline-block text-decoration-none" href="' + res.payment_url + '">پرداخت اینترنتی</a></p>';
            if (tg && tg.openLink) { try { tg.openLink(res.payment_url); } catch (e) {} }
          } else if (res.payment_error) {
            html += '<div class="alert alert-warning">' + res.payment_error + '</div>';
          }
          html += '<p><a href="/shop/track">پیگیری سفارش</a></p>';
          box.innerHTML = html;
        } else {
          box.innerHTML = '<div class="alert alert-danger">' + (res.error || 'خطا در ثبت سفارش.') + '</div>';
        }
      })
      .catch(function(){ var box = document.getElementById('orderResult'); box.style.display = ''; box.innerHTML = '<div class="alert alert-danger">خطای اتصال؛ دوباره تلاش کنید.</div>'; })
      .finally(function(){ btn.disabled = false; });
  };
  render();
})();
</script>
</body>
</html>
