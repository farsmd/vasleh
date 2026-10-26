<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>وصله — سیستم یکپارچه مدیریت کسب‌وکار و تولید</title>
  <link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
  <style>
    :root {
      --bg: #0f172a;
      --card-bg: rgba(30, 41, 59, 0.7);
      --card-border: rgba(255, 255, 255, 0.08);
      --primary: #3b82f6;
      --primary-hover: #2563eb;
      --accent: #10b981;
      --text: #f8fafc;
      --text-muted: #94a3b8;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Vazirmatn', system-ui, Tahoma, sans-serif; }
    body { background-color: var(--bg); color: var(--text); line-height: 1.6; padding-bottom: 80px; }
    .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }
    header { text-align: center; padding: 70px 20px 40px; }
    .badge { display: inline-block; padding: 6px 14px; background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 999px; font-size: 0.85rem; margin-bottom: 16px; }
    .badge svg { vertical-align: -3px; margin-left: 4px; }
    h1 { font-size: 2.6rem; font-weight: 800; line-height: 1.25; margin-bottom: 16px; }
    h1 span { color: var(--primary); }
    header p { font-size: 1.15rem; color: var(--text-muted); max-width: 650px; margin: 0 auto 30px; }
    .cta-btn { display: inline-block; background: var(--primary); color: #fff; padding: 12px 32px; border-radius: 10px; text-decoration: none; font-weight: 600; transition: background 0.2s; }
    .cta-btn:hover { background: var(--primary-hover); }
    .pricing-grid { display: grid; grid-template-columns: 1fr 1.3fr; gap: 24px; margin-top: 40px; }
    @media (max-width: 768px) { .pricing-grid { grid-template-columns: 1fr; } h1 { font-size: 1.9rem; } }
    .card { background: var(--card-bg); border: 1px solid var(--card-border); backdrop-filter: blur(12px); border-radius: 16px; padding: 30px; }
    .card-title { font-size: 1.3rem; font-weight: 700; margin-bottom: 8px; }
    .card-desc { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 24px; }
    .base-features { list-style: none; margin-bottom: 24px; }
    .base-features li { padding: 8px 0; display: flex; align-items: center; gap: 10px; font-size: 0.95rem; border-bottom: 1px solid rgba(255, 255, 255, 0.04); }
    .check-icon { flex: none; display: inline-flex; }
    .module-item { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--card-border); border-radius: 12px; margin-bottom: 12px; cursor: pointer; transition: border-color 0.2s, background 0.2s; }
    .module-item:hover { border-color: rgba(59, 130, 246, 0.4); }
    .module-item.active { border-color: var(--primary); background: rgba(59, 130, 246, 0.08); }
    .module-info { display: flex; align-items: center; gap: 12px; }
    .module-info input[type="checkbox"] { width: 18px; height: 18px; accent-color: var(--primary); cursor: pointer; }
    .module-name { font-weight: 600; font-size: 0.95rem; }
    .module-desc { font-size: 0.8rem; color: var(--text-muted); }
    .module-price { font-weight: 700; font-size: 0.9rem; color: #93c5fd; white-space: nowrap; }
    .summary-box { margin-top: 24px; padding: 20px; background: rgba(0, 0, 0, 0.3); border-radius: 12px; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; color: var(--text-muted); }
    .summary-total { display: flex; justify-content: space-between; align-items: baseline; padding-top: 14px; border-top: 1px solid var(--card-border); font-size: 1.1rem; font-weight: 700; color: #fff; }
    .total-price { font-size: 1.6rem; color: var(--accent); }
    .trial-notice { text-align: center; font-size: 0.85rem; color: var(--text-muted); margin-top: 16px; }
    .btn-submit { width: 100%; padding: 14px; background: var(--accent); color: #042f2e; border: none; border-radius: 10px; font-size: 1.05rem; font-weight: 700; cursor: pointer; margin-top: 16px; transition: filter 0.2s; }
    .btn-submit:hover { filter: brightness(1.1); }
    .lead-field { width: 100%; padding: 12px 14px; border-radius: 10px; border: 1px solid var(--card-border); background: rgba(15, 23, 42, 0.5); color: var(--text); font-size: 0.95rem; margin-bottom: 12px; }
    .lead-field::placeholder { color: var(--text-muted); }
    .banner { max-width: 1100px; margin: 24px auto 0; padding: 14px 20px; border-radius: 12px; font-size: 0.95rem; text-align: center; }
    .banner.ok { background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; }
    .banner.err { background: rgba(244, 63, 94, 0.15); border: 1px solid rgba(244, 63, 94, 0.4); color: #fda4af; }
    .note { font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; display: flex; gap: 8px; }
    .note svg { flex: none; margin-top: 3px; }
  </style>
</head>
<body>

  <div class="container">
    <header>
      <div class="badge"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg> نرم‌افزار وصله نسخه <?= e($version) ?></div>
      <h1>{{hero_title}} <span>{{hero_highlight}}</span></h1>
      <p>{{hero_subtitle}}</p>
    </header>

    <?php if ($leadOk): ?>
      <div class="banner ok">{{success_msg}}</div>
    <?php endif; ?>
    <?php if ($err === 'phone'): ?>
      <div class="banner err">{{err_phone}}</div>
    <?php elseif ($err === 'name'): ?>
      <div class="banner err">{{err_name}}</div>
    <?php endif; ?>

    <div class="pricing-grid">

      <!-- ستون ۱: پلن پایه -->
      <div class="card">
        <div class="badge" style="background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3);">
          {{trial_badge}}
        </div>
        <div class="card-title">{{base_title}}</div>
        <p class="card-desc">{{base_desc}}</p>

        <div style="margin-bottom: 24px;">
          <span style="font-size: 2rem; font-weight: 800;"><?= \Modules\Front\Landing::faNum((int) $prices['base']) ?></span>
          <span style="color: var(--text-muted); font-size: 0.9rem;">تومان / ماهانه</span>
        </div>

        <ul class="base-features">
          <li><span class="check-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> {{feat_1}}</li>
          <li><span class="check-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> {{feat_2}}</li>
          <li><span class="check-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> {{feat_3}}</li>
          <li><span class="check-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> {{feat_4}}</li>
          <li><span class="check-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> {{feat_5}}</li>
          <li><span class="check-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></span> {{feat_6}}</li>
        </ul>

        <div class="note">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fbbf24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7c.6.5 1 1.4 1 2.3h6c0-.9.4-1.8 1-2.3A7 7 0 0 0 12 2z"/></svg>
          <span>{{trial_note}}</span>
        </div>
      </div>

      <!-- ستون ۲: ماژول‌های انتخابی و ماشین‌حساب -->
      <div class="card">
        <div class="card-title">{{modules_title}}</div>
        <p class="card-desc">{{modules_desc}}</p>

        <form id="orderForm" action="/lead" method="POST">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

          <?php foreach ($modules as $slug => $m): ?>
          <label class="module-item" for="mod_<?= e($slug) ?>">
            <div class="module-info">
              <input type="checkbox" id="mod_<?= e($slug) ?>" name="modules[]" value="<?= e($slug) ?>" data-price="<?= (int) $prices[$slug] ?>">
              <div>
                <div class="module-name"><?= e($m['title']) ?></div>
                <div class="module-desc"><?= e($m['desc']) ?></div>
              </div>
            </div>
            <div class="module-price">+ <?= \Modules\Front\Landing::faNum((int) $prices[$slug]) ?> ت/ماه</div>
          </label>
          <?php endforeach; ?>

          <div class="summary-box">
            <div class="summary-row">
              <span>اشتراک پایه</span>
              <span><?= \Modules\Front\Landing::faNum((int) $prices['base']) ?> تومان</span>
            </div>
            <div class="summary-row">
              <span>ماژول‌های انتخابی (<span id="selectedCount">۰</span> مورد)</span>
              <span id="modulesCost">۰ تومان</span>
            </div>
            <div class="summary-total">
              <span>مبلغ ماهانه بعد از دوره تست:</span>
              <span class="total-price"><span id="finalTotal"><?= \Modules\Front\Landing::faNum((int) $prices['base']) ?></span> <span style="font-size: 0.85rem; font-weight: normal; color: var(--text-muted);">تومان</span></span>
            </div>
          </div>

          <div style="margin-top: 20px;">
            <input type="text" name="name" required maxlength="100" placeholder="نام و نام خانوادگی" class="lead-field">
            <input type="tel" name="phone" required placeholder="شماره تماس (مثل 09123456789)" class="lead-field" style="direction: rtl; text-align: right;">
          </div>

          <button type="submit" class="btn-submit">{{cta_button}}</button>
          <div class="trial-notice">{{trial_notice}}</div>
        </form>

      </div>

    </div>

    <?php if ($customHtml !== ''): ?>
    <section class="custom-html" style="margin-top: 40px;">
      <?= $customHtml ?>
    </section>
    <?php endif; ?>
  </div>

  <script>
    const BASE_PRICE = <?= (int) $prices['base'] ?>;
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const modulesCostEl = document.getElementById('modulesCost');
    const finalTotalEl = document.getElementById('finalTotal');
    const selectedCountEl = document.getElementById('selectedCount');

    function formatNumber(num) {
      return num.toLocaleString('fa-IR');
    }

    function calculate() {
      let modulesTotal = 0;
      let count = 0;
      checkboxes.forEach(cb => {
        const itemContainer = cb.closest('.module-item');
        if (cb.checked) {
          modulesTotal += parseInt(cb.dataset.price, 10);
          count++;
          itemContainer.classList.add('active');
        } else {
          itemContainer.classList.remove('active');
        }
      });
      const total = BASE_PRICE + modulesTotal;
      selectedCountEl.innerText = count.toLocaleString('fa-IR');
      modulesCostEl.innerText = formatNumber(modulesTotal) + ' تومان';
      finalTotalEl.innerText = formatNumber(total);
    }

    checkboxes.forEach(cb => {
      cb.addEventListener('change', calculate);
    });
    calculate();
  </script>
</body>
</html>
