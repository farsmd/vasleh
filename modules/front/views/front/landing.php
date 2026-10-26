<?php
// تم یکپارچه: متغیرهای قالب ادمین (storage/admin-theme.json) روی لندینگ هم می‌نشیند؛
// حالت تاریک/روشن با همان کلید localStorage ادمین خوانده می‌شود.
$__theme = \Modules\Admin\AdminTheme::load(dirname(__DIR__, 4));
$__dark = ($__theme['default_theme'] ?? 'dark') === 'dark';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl"<?= $__dark ? ' class="dark"' : '' ?>>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>وصله (Vasleh) — سیستم یکپارچه مدیریت کسب‌وکار، تولید و فروش</title>
  <script>try{var _t=localStorage.getItem('vasleh_theme');if(_t==='light'){document.documentElement.classList.remove('dark')}else if(_t==='dark'){document.documentElement.classList.add('dark')}}catch(e){}</script>
  <link rel="stylesheet" href="/assets/landing/landing.css">
  <link rel="stylesheet" href="/assets/taskyar/fonts/vazirmatn.css">
  <style>
    <?= \Modules\Admin\AdminTheme::varsCss($__theme) ?>
    html {
      scroll-behavior: smooth;
    }

    body {
      background-color: var(--admin-main-bg);
      color: var(--admin-text);
      font-family: 'Vazirmatn', system-ui, sans-serif;
      overflow-x: hidden;
    }

    /* اثر پس‌زمینه شیشه‌ای و هاله نوری */
    .glass-card {
      background: rgba(17, 27, 48, 0.65);
      background: color-mix(in srgb, var(--admin-panel-bg) 65%, transparent);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid var(--admin-border);
    }

    .glass-card-hover {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .glass-card-hover:hover {
      border-color: color-mix(in srgb, var(--admin-accent) 35%, transparent);
      transform: translateY(-4px);
      box-shadow: 0 12px 30px -10px color-mix(in srgb, var(--admin-accent) 20%, transparent);
    }

    .glow-bg {
      background: radial-gradient(circle at 50% 20%, color-mix(in srgb, var(--admin-accent) 15%, transparent) 0%, rgba(6, 182, 212, 0.05) 45%, transparent 70%);
    }

    /* حالت روشن: کلاس‌های تیره‌ی ثابت لندینگ با متغیرهای تم جایگزین می‌شوند */
    html:not(.dark) [class*="text-white"]{color:var(--admin-text)}
    html:not(.dark) [class*="text-slate-300"]{color:var(--admin-text)}
    html:not(.dark) [class*="text-slate-400"], html:not(.dark) [class*="text-slate-500"]{color:var(--admin-muted)}
    html:not(.dark) [class*="bg-slate-950"], html:not(.dark) [class*="bg-slate-900"]{background-color:var(--admin-panel-bg)}
    html:not(.dark) [class*="border-slate-800"]{border-color:var(--admin-border)}
    html:not(.dark) [class*="text-blue-400"]{color:var(--admin-accent)}
    html:not(.dark) [class*="bg-blue-600"]{background-color:var(--admin-accent);color:#fff}
    html:not(.dark) [class*="bg-blue-500"]{background-color:color-mix(in srgb, var(--admin-accent) 10%, transparent)}

    /* پنهان‌سازی اسکرول‌بار در پنل‌های افقی */
    .no-scrollbar::-webkit-scrollbar {
      display: none;
    }
    .no-scrollbar {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
  </style>
</head>
<body class="selection:bg-blue-500 selection:text-white">

  <!-- هاله نورانی پس‌زمینه سراسری -->
  <div class="fixed inset-0 glow-bg pointer-events-none z-0"></div>

  <!-- نوار ناوبری بالا (Sticky Glass Navbar) -->
  <header class="sticky top-0 z-50 w-full glass-card border-b border-slate-800/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
      
      <!-- لوگو و نسخه -->
      <div class="flex items-center gap-4">
        <a href="#" class="flex items-center gap-3 group">
          <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 via-indigo-600 to-cyan-400 p-0.5 shadow-lg shadow-blue-500/25 flex items-center justify-center">
            <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center">
              <!-- آیکون وصله (تکه پازل یا ماژولار) -->
              <svg class="w-6 h-6 text-blue-400 group-hover:scale-110 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v4m0 12v4M2 12h4m12 0h4"/>
                <rect x="6" y="6" width="12" height="12" rx="3" stroke-width="2.2"/>
                <circle cx="12" cy="12" r="2" fill="currentColor"/>
              </svg>
            </div>
          </div>
          <div class="flex flex-col">
            <span class="text-xl font-black tracking-tight text-white flex items-center gap-1.5">
              وصله
              <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">Vasleh</span>
            </span>
            <span class="text-[10px] text-slate-400 -mt-0.5">سیستم یکپارچه کسب‌وکار و تولید</span>
          </div>
        </a>

        <!-- وضعیت زنده -->
        <div class="hidden md:flex items-center gap-2 pr-4 border-r border-slate-800 text-xs text-slate-400">
          <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
          </span>
          <span>نسخه پایدار: <strong class="text-slate-200 font-mono">5.8.17</strong></span>
        </div>
      </div>

      <!-- منوهای ناوبری دسکتاپ -->
      <nav class="hidden lg:flex items-center gap-8 text-sm font-medium text-slate-300">
        <a href="#features" class="hover:text-blue-400 transition-colors">قابلیت‌ها</a>
        <a href="#pipeline" class="hover:text-blue-400 transition-colors">زنجیره ارزش</a>
        <a href="#modules" class="hover:text-blue-400 transition-colors">ماژول‌ها</a>
        <a href="#pricing" class="hover:text-blue-400 transition-colors">قیمت‌گذاری و سفارش</a>
        <a href="#comparison" class="hover:text-blue-400 transition-colors">چرا وصله؟</a>
        <a href="#faq" class="hover:text-blue-400 transition-colors">سوالات متداول</a>
      </nav>

      <!-- دکمه CTA و دمو -->
      <div class="flex items-center gap-3">
        <a href="#pricing" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-sm font-bold shadow-lg shadow-blue-600/30 transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center gap-2">
          <span>شروع ۱۵ روز تست رایگان</span>
          <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M5 12h14M12 5l7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>
  </header>

  <main class="relative z-10">

    <!-- بخش اول: Hero Section -->
    <section class="pt-16 pb-20 sm:pt-24 sm:pb-28">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- بنر خبر/ویژگی در هیرو -->
        <div class="flex justify-center mb-8">
          <div class="inline-flex items-center gap-3 px-4 py-1.5 rounded-full glass-card border border-blue-500/20 text-xs sm:text-sm text-slate-300">
            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-xs">جدید</span>
            <span>بدون فریم‌ورک، بدون نیاز به کامپوزر، آماده اجرا روی هاست اشتراکی</span>
            <svg class="w-4 h-4 text-slate-400 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </div>
        </div>

        <div class="text-center max-w-4xl mx-auto">
          <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight">
            کسب‌وکارتان را وصله کنید؛
            <br>
            <span class="bg-gradient-to-r from-blue-400 via-cyan-300 to-emerald-400 bg-clip-text text-transparent">
              از سفارش اینستاگرام تا انبارداری، تولید و حسابداری
            </span>
          </h1>

          <p class="mt-6 text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
            «وصله» نرم‌افزار ماژولار با <strong class="text-white">PHP خالص و SQLite</strong> است. دیگر برای داشتن یک سیستم تجاری کامل نیازی به پرداخت هزینه سرورهای سنگین لینوکسی یا درگیر شدن با استک‌های پیچیده ندارید. نصب، تنها با کپی کردن فایل‌هاست!
          </p>

          <!-- دکمه‌های اقدام و نشان‌ها -->
          <div class="mt-10 flex flex-wrap justify-center items-center gap-4">
            <a href="#pricing" class="px-8 py-3.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-base shadow-xl shadow-blue-600/30 transition-all hover:scale-105 active:scale-95">
              تست رایگان ۱۵ روزه (فعال‌سازی آنی)
            </a>
            <a href="https://atroweb.ir" target="_blank" rel="noopener" class="px-7 py-3.5 rounded-xl glass-card hover:bg-slate-800 text-slate-200 font-semibold text-base transition-all flex items-center gap-2 border border-slate-700">
              <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
              <span>مشاهده نسخه زنده دمو</span>
            </a>
          </div>

          <!-- مزایای کلیدی به شکل کپسول -->
          <div class="mt-12 flex flex-wrap justify-center items-center gap-3 sm:gap-6 text-xs sm:text-sm text-slate-400">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              <span>بدون نیاز به MySQL (بانک اطلاعاتی سبک SQLite)</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-blue-400"></span>
              <span>۱۰۰٪ مستقل از اینترنت بین‌الملل (بدون CDN خارجی)</span>
            </div>
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full bg-purple-400"></span>
              <span>تقویم خورشیدی و ارقام فارسی اصیل</span>
            </div>
          </div>
        </div>

        <!-- پیش‌نمایش گرافیکی معماری و پنل وصله -->
        <div class="mt-16 max-w-5xl mx-auto">
          <div class="glass-card rounded-2xl p-2 sm:p-4 border border-slate-700 shadow-2xl relative overflow-hidden group">
            
            <!-- نوار کنترل سیستم شبیه‌سازی شده -->
            <div class="flex items-center justify-between pb-3 px-3 border-b border-slate-800 text-xs text-slate-400">
              <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-rose-500/80 inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-amber-500/80 inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-emerald-500/80 inline-block"></span>
                <span class="mr-2 text-slate-400 font-mono text-[11px] hidden sm:inline">vasleh://core.kernel.loaded [16 modules active]</span>
              </div>
              <div class="bg-slate-900/80 px-3 py-1 rounded-md text-[11px] font-mono text-cyan-400 border border-slate-800">
                هاست اشتراکی • PHP 8.1+ • بدون وابستگی
              </div>
            </div>

            <!-- محتوای داخلی پنجره -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 p-4 mt-1 bg-slate-950/70 rounded-xl">
              
              <!-- سایدبار شبیه‌سازی شده -->
              <div class="md:col-span-3 border-b md:border-b-0 md:border-l border-slate-800/80 pb-4 md:pb-0 md:pl-4 space-y-2 text-xs">
                <div class="text-slate-500 font-semibold px-2 py-1">ماژول‌های فعال</div>
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-blue-600/10 text-blue-400 border border-blue-500/20 font-medium">
                  <span class="flex items-center gap-2">📦 انبارداری و کالا</span>
                  <span class="text-[10px] bg-blue-500/20 px-1.5 py-0.5 rounded">Active</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-900 text-slate-300">
                  <span class="flex items-center gap-2">⚙️ فرمول ساخت (BOM)</span>
                  <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded">Ready</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-900 text-slate-300">
                  <span class="flex items-center gap-2">💳 حسابداری و مالی</span>
                  <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-1.5 py-0.5 rounded">Sync</span>
                </div>
                <div class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-900 text-slate-300">
                  <span class="flex items-center gap-2">🛍️ فروشگاه اینترنتی</span>
                  <span class="text-[10px] bg-cyan-500/20 text-cyan-400 px-1.5 py-0.5 rounded">v1.0</span>
                </div>
              </div>

              <!-- پنل اصلی شبیه‌سازی شده -->
              <div class="md:col-span-9 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                  <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                    <span class="text-xs text-slate-400 block mb-1">فروش ماه جاری</span>
                    <span class="text-base sm:text-lg font-black text-emerald-400">۴۲,۸۵۰,۰۰۰ <span class="text-[10px] font-normal text-slate-500">ت</span></span>
                  </div>
                  <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                    <span class="text-xs text-slate-400 block mb-1">دستور تولید جاری</span>
                    <span class="text-base sm:text-lg font-black text-cyan-400">۱۲ <span class="text-[10px] font-normal text-slate-500">سفارش</span></span>
                  </div>
                  <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                    <span class="text-xs text-slate-400 block mb-1">هشدار کسری مواد</span>
                    <span class="text-base sm:text-lg font-black text-rose-400">۲ <span class="text-[10px] font-normal text-slate-500">قلم</span></span>
                  </div>
                  <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                    <span class="text-xs text-slate-400 block mb-1">بک‌آپ اتمی آخر</span>
                    <span class="text-base sm:text-lg font-black text-blue-400">امروز <span class="text-[10px] font-normal text-slate-500">۰۴:۰۰</span></span>
                  </div>
                </div>

                <!-- خط سیر هوشمند سفارش -->
                <div class="p-4 rounded-xl bg-slate-900/50 border border-slate-800 space-y-2">
                  <div class="flex items-center justify-between text-xs text-slate-300 font-bold mb-2">
                    <span>چرخه پردازش سفارش زنده: #VSL-94821</span>
                    <span class="text-emerald-400">تطبیق اتوماتیک انبار و سند مالی</span>
                  </div>
                  <div class="w-full bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 via-cyan-400 to-emerald-400 h-full w-4/5 rounded-full"></div>
                  </div>
                  <div class="flex justify-between text-[11px] text-slate-400 pt-1">
                    <span>ثبت سفارش (فروشگاه)</span>
                    <span>کسر مواد (انبارداری)</span>
                    <span>خط مونتاژ (تولید)</span>
                    <span class="text-emerald-400 font-semibold">ثبت سند دوبل (حسابداری)</span>
                  </div>
                </div>

              </div>

            </div>
          </div>
        </div>

      </div>
    </section>

    <!-- بخش دوم: چرخه یکپارچه ارزش (Pipeline) -->
    <section id="pipeline" class="py-20 border-t border-slate-800/60 bg-slate-950/40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
          <h2 class="text-xs uppercase tracking-widest text-blue-400 font-bold">زنجیره ارزش یکپارچه</h2>
          <p class="mt-2 text-3xl font-extrabold text-white sm:text-4xl">
            چرا کسب‌وکارهای در حال رشد عاشق «وصله» می‌شوند؟
          </p>
          <p class="mt-4 text-slate-400 text-sm sm:text-base">
            دیگر نیازی به استفاده از چند اکسل جداگانه، یک نرم‌افزار حسابداری گران، و یک سایت ووکامرسی مجزا نیست. تمام فرآیندها در وصله مانند مهره‌های دومینو به هم متصل هستند.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          
          <!-- گام ۱ -->
          <div class="glass-card rounded-2xl p-6 relative border-t-4 border-t-blue-500">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-lg mb-4">
              ۱
            </div>
            <h3 class="text-lg font-bold text-white mb-2">ثبت سفارش</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
              سفارش مشتری از طریق فروشگاه اختصاصی یا ثبت دستی در پنل با کد پیگیری اختصاصی ثبت می‌شود.
            </p>
          </div>

          <!-- گام ۲ -->
          <div class="glass-card rounded-2xl p-6 relative border-t-4 border-t-cyan-500">
            <div class="w-10 h-10 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center font-bold text-lg mb-4">
              ۲
            </div>
            <h3 class="text-lg font-bold text-white mb-2">تطبیق انبار و کسری</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
              موجودی کالا یا مواد اولیه در انبار بررسی می‌شود. در صورت نیاز به تولید، هشدار کسری صادر می‌گردد.
            </p>
          </div>

          <!-- گام ۳ -->
          <div class="glass-card rounded-2xl p-6 relative border-t-4 border-t-indigo-500">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-lg mb-4">
              ۳
            </div>
            <h3 class="text-lg font-bold text-white mb-2">دستور ساخت (BOM)</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
              با شروع تولید مواد اولیه کسر و با اتمام آن، محصول نهایی وارد انبار و آماده ارسال می‌شود.
            </p>
          </div>

          <!-- گام ۴ -->
          <div class="glass-card rounded-2xl p-6 relative border-t-4 border-t-emerald-500">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-lg mb-4">
              ۴
            </div>
            <h3 class="text-lg font-bold text-white mb-2">سند مالی خودکار</h3>
            <p class="text-xs text-slate-400 leading-relaxed">
              سفارش پرداخت‌شده با یک کلیک و بدون خطا به سند فروش، سود، زیان و دفاتر حسابداری اضافه می‌شود.
            </p>
          </div>

        </div>

      </div>
    </section>

    <!-- بخش سوم: ماژول‌های فعال (Features & Modules) -->
    <section id="modules" class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-16">
          <h2 class="text-xs uppercase tracking-widest text-cyan-400 font-bold">معماری ۱۰۰٪ ماژولار</h2>
          <p class="mt-2 text-3xl font-extrabold text-white sm:text-4xl">
            ماژول‌هایی که کسب‌وکار شما را خودکار می‌کنند
          </p>
          <p class="mt-4 text-slate-400 text-sm sm:text-base">
            هسته هیچ پیش‌فرضی ندارد. هر پوشه در `modules/` یک افزونه کامل، دارای روت‌ها، سرویس‌ها و پایگاه داده خود است.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

          <!-- ماژول حسابداری -->
          <div class="glass-card rounded-2xl p-6 glass-card-hover">
            <div class="w-12 h-12 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center mb-5 border border-blue-500/20">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2 flex items-center justify-between">
              <span>حسابداری و مالی</span>
              <span class="text-xs font-mono text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded">accounting</span>
            </h3>
            <p class="text-slate-400 text-sm leading-relaxed mb-4">
              مدیریت حساب‌ها، ثبت هزینه و درآمد (مواد اولیه، کرایه، حقوق)، و انتقال یک‌کلیکه فاکتورهای فروشگاه به عنوان سند قطعی بدون دوبل‌کاری.
            </p>
            <div class="text-xs text-slate-500 flex items-center gap-2 border-t border-slate-800 pt-3">
              <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>گزارش تراز، دسته‌بندی و دفتر معین</span>
            </div>
          </div>

          <!-- ماژول فروشگاه -->
          <div class="glass-card rounded-2xl p-6 glass-card-hover">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-5 border border-cyan-500/20">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2 flex items-center justify-between">
              <span>فروشگاه هوشمند</span>
              <span class="text-xs font-mono text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded">shop</span>
            </h3>
            <p class="text-slate-400 text-sm leading-relaxed mb-4">
              محصولات با فیلدهای متغیر، واریانت قیمت، کنترل SKU، سبد خرید سبک نشستی، کدهای سفارش اختصاصی VSL و فاکتور استاندارد چاپی.
            </p>
            <div class="text-xs text-slate-500 flex items-center gap-2 border-t border-slate-800 pt-3">
              <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>پیگیری سریع سفارش فقط با کد و شماره موبایل</span>
            </div>
          </div>

          <!-- ماژول تولید (BOM) -->
          <div class="glass-card rounded-2xl p-6 glass-card-hover">
            <div class="w-12 h-12 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-5 border border-purple-500/20">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2 flex items-center justify-between">
              <span>فرمول ساخت و تولید</span>
              <span class="text-xs font-mono text-purple-400 bg-purple-500/10 px-2 py-0.5 rounded">production</span>
            </h3>
            <p class="text-slate-400 text-sm leading-relaxed mb-4">
              تعریف درخت محصول و مواد اولیه (BOM). کسر مواد در شروع فرآیند و افزایش موجودی کالای آماده پس از تأیید پایان خط تولید.
            </p>
            <div class="text-xs text-slate-500 flex items-center gap-2 border-t border-slate-800 pt-3">
              <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>قفل شدن مشخصات فرمول در سفارش‌های قبلی</span>
            </div>
          </div>

          <!-- ماژول انبارداری -->
          <div class="glass-card rounded-2xl p-6 glass-card-hover">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-5 border border-emerald-500/20">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2 flex items-center justify-between">
              <span>انبارداری چندگانه</span>
              <span class="text-xs font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded">inventory</span>
            </h3>
            <p class="text-slate-400 text-sm leading-relaxed mb-4">
              مدیریت انبارهای موازی، حواله ورود و خروج، انتقال بین انبارها، تعیین نقطه بحرانی و هشدار کمبود موجودی با کاردکس لحظه‌ای.
            </p>
            <div class="text-xs text-slate-500 flex items-center gap-2 border-t border-slate-800 pt-3">
              <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>سرویس stock برای استفاده سایر ماژول‌ها</span>
            </div>
          </div>

          <!-- ماژول مدیریت شخصی /me -->
          <div class="glass-card rounded-2xl p-6 glass-card-hover">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-5 border border-amber-500/20">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2 flex items-center justify-between">
              <span>مدیریت شخصی و کارها</span>
              <span class="text-xs font-mono text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded">personal</span>
            </h3>
            <p class="text-slate-400 text-sm leading-relaxed mb-4">
              پوسته فوق‌العاده سریع بدون استایل خارجی در مسیر `/me`. تسک‌ها، رویدادهای تقویم، یادداشت‌ها، پروژه‌ها و چک‌لیست کارهای روزمره مدیر.
            </p>
            <div class="text-xs text-slate-500 flex items-center gap-2 border-t border-slate-800 pt-3">
              <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>اشتراک‌گذاری امن پروژه‌ها با لینک‌های زمان‌دار</span>
            </div>
          </div>

          <!-- ماژول بک‌آپ و ایمنی دیتابیس -->
          <div class="glass-card rounded-2xl p-6 glass-card-hover">
            <div class="w-12 h-12 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center mb-5 border border-rose-500/20">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-white mb-2 flex items-center justify-between">
              <span>بک‌آپ اتمی و امنیت</span>
              <span class="text-xs font-mono text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded">backup & api</span>
            </h3>
            <p class="text-slate-400 text-sm leading-relaxed mb-4">
              تهیه بک‌آپ لحظه‌ای مطمئن با فناوری `VACUUM INTO` بدون قفل شدن دیتابیس، بازیابی یک‌کلیکه و توکن‌های هش‌شده برای ارتباط امن با API.
            </p>
            <div class="text-xs text-slate-500 flex items-center gap-2 border-t border-slate-800 pt-3">
              <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
              <span>عدم امکان دسترسی به دایرکتوری storage از وب</span>
            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- بخش چهارم: مقایسه با وردپرس و سیستم‌های سنگین -->
    <section id="comparison" class="py-20 border-y border-slate-800/80 bg-slate-900/30">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-14">
          <h2 class="text-xs uppercase tracking-widest text-indigo-400 font-bold">انتخاب عاقلانه فنی</h2>
          <p class="mt-2 text-3xl font-extrabold text-white">چرا وصله به جای وردپرس و ووکامرس؟</p>
          <p class="mt-3 text-slate-400 text-sm">تفاوت فاحش در هزینه، سرعت و پایداری برای کسب‌وکارهای عملیاتی</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-right border-collapse glass-card rounded-2xl overflow-hidden">
            <thead>
              <tr class="bg-slate-950/80 border-b border-slate-800 text-slate-300 text-sm">
                <th class="p-4 sm:p-5">معیار مقایسه</th>
                <th class="p-4 sm:p-5 text-blue-400 font-bold text-base">سیستم ماژولار وصله (Vasleh)</th>
                <th class="p-4 sm:p-5 text-slate-400 font-normal">وردپرس + ووکامرس + افزونه‌های متفرقه</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-sm text-slate-300">
              <tr>
                <td class="p-4 font-medium text-white">نیاز سخت‌افزاری و هاست</td>
                <td class="p-4 text-emerald-400 font-medium">هاست اشتراکی ارزان (حداقل رم ۱۲۸ مگابایت)</td>
                <td class="p-4 text-rose-400/90">سرور اختصاصی/ابری گران با رم بالا</td>
              </tr>
              <tr>
                <td class="p-4 font-medium text-white">پایگاه داده</td>
                <td class="p-4 text-emerald-400 font-medium">SQLite فوق‌سریع فایل‌محور (بدون نیاز به کانفیگ)</td>
                <td class="p-4 text-slate-400">MySQL سنگین با اتصالات متعدد پردازنده</td>
              </tr>
              <tr>
                <td class="p-4 font-medium text-white">یکپارچگی انبار و تولید</td>
                <td class="p-4 text-emerald-400 font-medium">طراحی‌شده به صورت بومی در زنجیره سفارش</td>
                <td class="p-4 text-slate-400">نیاز به افزونه‌های تجاری ناسازگار با یکدیگر</td>
              </tr>
              <tr>
                <td class="p-4 font-medium text-white">پایداری در اینترنت محدود</td>
                <td class="p-4 text-emerald-400 font-medium">کاملاً مستقل و خودکفا بدون CDN خارجی</td>
                <td class="p-4 text-rose-400/90">افت شدید سرعت به دلیل فراخوانی فایل‌های خارجی</td>
              </tr>
              <tr>
                <td class="p-4 font-medium text-white">روش ارتقا و به‌روزرسانی</td>
                <td class="p-4 text-emerald-400 font-medium">آپدیت یک‌کلیکه اتمی همراه با بک‌آپ اتوماتیک</td>
                <td class="p-4 text-slate-400">خطر شکستن سایت به دلیل تداخل نسخه‌های افزونه‌ها</td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </section>

    <!-- بخش پنجم: ماشین‌حساب هوشمند قیمت و فرم سفارش -->
    <section id="pricing" class="py-24">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto mb-12">
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold mb-3">
            🎁 ۱۵ روز استفاده کاملاً رایگان بدون نیاز به پرداخت
          </div>
          <h2 class="text-3xl sm:text-4xl font-black text-white">
            تعرفه شفاف، دقیقاً بر اساس نیازهای واقعی
          </h2>
          <p class="mt-4 text-slate-400 text-sm sm:text-base">
            پایه سیستم را فعال کنید و ماژول‌ها را مطابق چرخه کاری فروشگاه یا کارگاه‌تان انتخاب نمایید.
          </p>

          <!-- دکمه سوییچ ماهانه / سالانه -->
          <div class="mt-8 inline-flex items-center p-1.5 rounded-xl glass-card border border-slate-700">
            <button id="btnMonthly" type="button" class="px-5 py-2 rounded-lg text-sm font-bold bg-blue-600 text-white shadow-md transition-all">
              پرداخت ماهانه
            </button>
            <button id="btnYearly" type="button" class="px-5 py-2 rounded-lg text-sm font-bold text-slate-400 hover:text-white transition-all flex items-center gap-1.5">
              <span>پرداخت سالانه</span>
              <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-2 py-0.5 rounded-full font-bold">۲۰٪ تخفیف</span>
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          
          <!-- ستون چپ و وسط: انتخاب ماژول‌ها -->
          <div class="lg:col-span-7 space-y-4">
            
            <!-- کارت اشتراک پایه ثابت -->
            <div class="glass-card rounded-2xl p-6 border-2 border-blue-500/40 relative overflow-hidden">
              <div class="absolute -left-12 -top-12 w-28 h-28 bg-blue-500/10 rounded-full blur-xl pointer-events-none"></div>
              <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                  <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <h3 class="text-lg font-bold text-white">اشتراک پایه هسته وصله</h3>
                    <span class="text-xs bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded-full font-bold">اجباری</span>
                  </div>
                  <p class="text-xs text-slate-400 mt-1">
                    شامل هسته فوق‌سبک، پنل مدیریت شیشه‌ای، CMS وبلاگ، مدیریت وظایف /me، گزارش‌ها و بک‌آپ اتمی
                  </p>
                </div>
                <div class="text-left sm:text-right shrink-0">
                  <div class="text-xl font-black text-white" id="displayBasePrice">۱۹۹,۰۰۰</div>
                  <div class="text-[11px] text-slate-400" id="displayBaseCycle">تومان در ماه</div>
                </div>
              </div>
            </div>

            <!-- عنوان بخش ماژول‌های اختیاری -->
            <div class="pt-2 flex items-center justify-between text-xs text-slate-400 font-bold px-1">
              <span>ماژول‌های تخصصی افزونه:</span>
              <span class="text-cyan-400">به دلخواه فعال یا غیرفعال کنید</span>
            </div>

            <!-- ماژول‌ها با چک باکس تعاملی -->
            <div class="space-y-3" id="modulesList">

              <!-- ماژول حسابداری -->
              <label class="module-card glass-card rounded-xl p-4 flex items-center justify-between cursor-pointer border border-slate-800 transition-all hover:border-slate-700 block" data-price="<?= (int)($prices['accounting'] ?? 399000) ?>">
                <div class="flex items-center gap-3">
                  <input type="checkbox" class="module-checkbox w-5 h-5 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-0 focus:ring-offset-0 cursor-pointer" checked>
                  <div>
                    <div class="text-sm font-bold text-white flex items-center gap-2">
                      <span>ماژول حسابداری و اسناد مالی</span>
                      <span class="text-[10px] text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded">پیشنهادی</span>
                    </div>
                    <div class="text-xs text-slate-400 mt-0.5">دفاتر حساب، ثبت تراکنش‌ها و گزارش سود و زیان دوره‌ای</div>
                  </div>
                </div>
                <div class="text-left shrink-0">
                  <span class="text-sm font-black text-slate-200 module-price-text">۳۹۹,۰۰۰</span>
                  <span class="text-[10px] text-slate-500 block">تومان / ماه</span>
                </div>
              </label>

              <!-- ماژول فروشگاه اینترنتی -->
              <label class="module-card glass-card rounded-xl p-4 flex items-center justify-between cursor-pointer border border-slate-800 transition-all hover:border-slate-700 block" data-price="<?= (int)($prices['shop'] ?? 299000) ?>">
                <div class="flex items-center gap-3">
                  <input type="checkbox" class="module-checkbox w-5 h-5 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-0 focus:ring-offset-0 cursor-pointer" checked>
                  <div>
                    <div class="text-sm font-bold text-white">ماژول فروشگاه اینترنتی و تسویه</div>
                    <div class="text-xs text-slate-400 mt-0.5">کاتالوگ واریانت‌دار، سبد خرید و صدور فاکتور رسمی</div>
                  </div>
                </div>
                <div class="text-left shrink-0">
                  <span class="text-sm font-black text-slate-200 module-price-text">۲۹۹,۰۰۰</span>
                  <span class="text-[10px] text-slate-500 block">تومان / ماه</span>
                </div>
              </label>

              <!-- ماژول انبارداری -->
              <label class="module-card glass-card rounded-xl p-4 flex items-center justify-between cursor-pointer border border-slate-800 transition-all hover:border-slate-700 block" data-price="<?= (int)($prices['inventory'] ?? 249000) ?>">
                <div class="flex items-center gap-3">
                  <input type="checkbox" class="module-checkbox w-5 h-5 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-0 focus:ring-offset-0 cursor-pointer" checked>
                  <div>
                    <div class="text-sm font-bold text-white">ماژول انبارداری و کنترل کالا</div>
                    <div class="text-xs text-slate-400 mt-0.5">مدیریت موجودی، کاردکس، هشدارهای کسری انبار</div>
                  </div>
                </div>
                <div class="text-left shrink-0">
                  <span class="text-sm font-black text-slate-200 module-price-text">۲۴۹,۰۰۰</span>
                  <span class="text-[10px] text-slate-500 block">تومان / ماه</span>
                </div>
              </label>

              <!-- ماژول فرمول تولید -->
              <label class="module-card glass-card rounded-xl p-4 flex items-center justify-between cursor-pointer border border-slate-800 transition-all hover:border-slate-700 block" data-price="<?= (int)($prices['production'] ?? 349000) ?>">
                <div class="flex items-center gap-3">
                  <input type="checkbox" class="module-checkbox w-5 h-5 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                  <div>
                    <div class="text-sm font-bold text-white">ماژول خط تولید و فرمول ساخت (BOM)</div>
                    <div class="text-xs text-slate-400 mt-0.5">کسر مواد مصرفی، کنترل ضایعات و ثبت کالای ساخته شده</div>
                  </div>
                </div>
                <div class="text-left shrink-0">
                  <span class="text-sm font-black text-slate-200 module-price-text">۳۴۹,۰۰۰</span>
                  <span class="text-[10px] text-slate-500 block">تومان / ماه</span>
                </div>
              </label>

              <!-- ماژول استخراج قیمت -->
              <label class="module-card glass-card rounded-xl p-4 flex items-center justify-between cursor-pointer border border-slate-800 transition-all hover:border-slate-700 block" data-price="<?= (int)($prices['priceextract'] ?? 189000) ?>">
                <div class="flex items-center gap-3">
                  <input type="checkbox" class="module-checkbox w-5 h-5 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                  <div>
                    <div class="text-sm font-bold text-white">ماژول استخراج و پایش قیمت رقبا</div>
                    <div class="text-xs text-slate-400 mt-0.5">دریافت اتوماتیک قیمت‌ها از وب‌سرویس و جدول‌های آنلاین</div>
                  </div>
                </div>
                <div class="text-left shrink-0">
                  <span class="text-sm font-black text-slate-200 module-price-text">۱۸۹,۰۰۰</span>
                  <span class="text-[10px] text-slate-500 block">تومان / ماه</span>
                </div>
              </label>

              <!-- ماژول API پیشرفته -->
              <label class="module-card glass-card rounded-xl p-4 flex items-center justify-between cursor-pointer border border-slate-800 transition-all hover:border-slate-700 block" data-price="149000">
                <div class="flex items-center gap-3">
                  <input type="checkbox" class="module-checkbox w-5 h-5 rounded border-slate-700 bg-slate-900 text-blue-600 focus:ring-0 focus:ring-offset-0 cursor-pointer">
                  <div>
                    <div class="text-sm font-bold text-white">ماژول دسترسی API و یکپارچه‌سازی ابری</div>
                    <div class="text-xs text-slate-400 mt-0.5">کلیدهای Bearer، ارتباط با اپلیکیشن موبایل یا نرم‌افزارهای خارجی</div>
                  </div>
                </div>
                <div class="text-left shrink-0">
                  <span class="text-sm font-black text-slate-200 module-price-text">۱۴۹,۰۰۰</span>
                  <span class="text-[10px] text-slate-500 block">تومان / ماه</span>
                </div>
              </label>

            </div>

          </div>

          <!-- ستون راست: فرم ثبت سفارش و خلاصه فاکتور هوشمند -->
          <div class="lg:col-span-5">
            <div class="glass-card rounded-2xl p-6 sm:p-7 border border-slate-700/80 sticky top-28 shadow-2xl">
              
              <h3 class="text-lg font-black text-white flex items-center gap-2">
                <span>پیش‌فاکتور و ثبت درخواست</span>
              </h3>
              <p class="text-xs text-slate-400 mt-1 mb-5">
                تکمیل فرم زیر برای راه‌اندازی فوری دوره تست ۱۵ روزه رایگان است.
              </p>

              <!-- جزئیات فاکتور لحظه‌ای -->
              <div class="space-y-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800 text-xs">
                <div class="flex justify-between text-slate-300">
                  <span>اشتراک پایه:</span>
                  <span id="invoiceBasePrice">۱۹۹,۰۰۰ تومان</span>
                </div>
                <div class="flex justify-between text-slate-300">
                  <span>ماژول‌های انتخابی (<span id="invoiceModuleCount" class="text-blue-400 font-bold">۳</span> مورد):</span>
                  <span id="invoiceModulesPrice">۹۴۷,۰۰۰ تومان</span>
                </div>
                <div id="rowDiscount" class="flex justify-between text-emerald-400 hidden">
                  <span>تخفیف سالانه (۲۰٪):</span>
                  <span id="invoiceDiscount">۰ تومان</span>
                </div>
                <div class="border-t border-slate-800 pt-3 flex justify-between items-baseline font-bold">
                  <span class="text-white text-sm">مجموع قابل پرداخت آتی:</span>
                  <span class="text-emerald-400 text-lg font-black" id="invoiceFinalTotal">۱,۱۴۶,۰۰۰ <span class="text-xs font-normal text-slate-400">تومان</span></span>
                </div>
                <div class="text-[11px] text-slate-500 pt-1 text-center">
                  ✨ مبلغ قابل پرداخت امروز در درگاه: <strong class="text-emerald-400">۰ تومان (۱۵ روز رایگان)</strong>
                </div>
              </div>

              <!-- فرم ثبت اطلاعات -->
              <form id="checkoutForm" class="mt-6 space-y-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-300 mb-1">نام و نام خانوادگی *</label>
                  <input type="text" id="custName" required placeholder="مثلاً: علی رضایی" class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">شماره موبایل *</label>
                    <input type="tel" id="custMobile" required placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 text-left focus:outline-none focus:border-blue-500 transition-colors">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">نام کسب‌وکار / برند *</label>
                    <input type="text" id="custBrand" required placeholder="مثلاً: کارگاه چرم راد" class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors">
                  </div>
                </div>

                <div>
                  <label class="block text-xs font-semibold text-slate-300 mb-1">دامنه یا هاست مد نظر (اختیاری)</label>
                  <input type="text" id="custDomain" placeholder="مثلاً: mybrand.ir یا ساب‌دامین رایگان" dir="ltr" class="w-full bg-slate-900/90 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-slate-500 text-left focus:outline-none focus:border-blue-500 transition-colors">
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-400 hover:to-teal-500 text-slate-950 font-black text-sm shadow-lg shadow-emerald-500/20 transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                  <span>تأیید و دریافت نسخه تستی ۱۵ روزه</span>
                  <svg class="w-4 h-4 rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>

                <p class="text-[11px] text-slate-500 text-center">
                  با کلیک روی دکمه، لینک دسترسی و مشخصات موقت ادمین برای شما پیامک خواهد شد.
                </p>
              </form>

            </div>
          </div>

        </div>

      </div>
    </section>

    <!-- بخش ششم: سوالات متداول (FAQ) -->
    <section id="faq" class="py-20 border-t border-slate-800/80 bg-slate-950/50">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-12">
          <h2 class="text-xs uppercase tracking-widest text-cyan-400 font-bold">پاسخ به ابهامات</h2>
          <p class="mt-2 text-3xl font-extrabold text-white">سوالات متداول کاربران</p>
        </div>

        <div class="space-y-4" id="faqAccordion">

          <!-- سوال ۱ -->
          <div class="glass-card rounded-xl border border-slate-800 overflow-hidden">
            <button type="button" class="faq-btn w-full p-5 text-right flex items-center justify-between text-white font-bold text-sm sm:text-base hover:text-blue-400 transition-colors">
              <span>آیا وصله واقعاً روی ارزان‌ترین هاست‌های لینوکس ایران هم اجرا می‌شود؟</span>
              <svg class="w-5 h-5 transform transition-transform duration-200 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-content px-5 pb-5 text-slate-400 text-xs sm:text-sm leading-relaxed hidden">
              بله، وصله بدون نیاز به Composer، بدون نیاز به Node.js و حتی بدون دیتابیس MySQL طراحی شده است. تنها پیش‌نیاز آن PHP نسخه ۸.۱ به بالا به همراه اکستنشن سبک `pdo_sqlite` است که تقریباً روی ۱۰۰٪ هاست‌های اشتراکی سی‌پنل، دایرکت‌ادمین یا پلسک به صورت پیش‌فرض فعال است.
            </div>
          </div>

          <!-- سوال ۲ -->
          <div class="glass-card rounded-xl border border-slate-800 overflow-hidden">
            <button type="button" class="faq-btn w-full p-5 text-right flex items-center justify-between text-white font-bold text-sm sm:text-base hover:text-blue-400 transition-colors">
              <span>در صورت قطع یا اختلال اینترنت بین‌الملل، سایت از کار می‌افتد؟</span>
              <svg class="w-5 h-5 transform transition-transform duration-200 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-content px-5 pb-5 text-slate-400 text-xs sm:text-sm leading-relaxed hidden">
              خیر! در وصله تمامی فایل‌های ظاهری (Bootstrap راست‌چین، فونت‌ها، آیکون‌های SVG و ویرایشگر CodeMirror) به شکل محلی داخل بسته قرار دارند. هیچ کتابخانه یا تگی به CDNهای خارجی وابسته نیست و سیستم روی اینترنت ملی (اینترانت) با همان سرعت اصلی کار می‌کند.
            </div>
          </div>

          <!-- سوال ۳ -->
          <div class="glass-card rounded-xl border border-slate-800 overflow-hidden">
            <button type="button" class="faq-btn w-full p-5 text-right flex items-center justify-between text-white font-bold text-sm sm:text-base hover:text-blue-400 transition-colors">
              <span>دوره تست ۱۵ روزه چگونه کار می‌کند و آیا نیاز به کارت بانکی دارد؟</span>
              <svg class="w-5 h-5 transform transition-transform duration-200 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-content px-5 pb-5 text-slate-400 text-xs sm:text-sm leading-relaxed hidden">
              هیچ نیازی به پرداخت وجه یا وارد کردن اطلاعات کارت بانکی در دوره تست نیست. بلافاصله پس از ثبت فرم بالا، تمامی ماژول‌ها برای شما باز شده و می‌توانید در عمل، تولید، انبارداری و فروش خود را آزمایش کنید. قبل از پایان مهلت ۱۵ روزه می‌توانید اشتراک ماهانه یا سالانه را تمدید فرمایید.
            </div>
          </div>

          <!-- سوال ۴ -->
          <div class="glass-card rounded-xl border border-slate-800 overflow-hidden">
            <button type="button" class="faq-btn w-full p-5 text-right flex items-center justify-between text-white font-bold text-sm sm:text-base hover:text-blue-400 transition-colors">
              <span>اگر بعداً بخواهم ماژول جدیدی اضافه یا یک ماژول را حذف کنم چه می‌شود؟</span>
              <svg class="w-5 h-5 transform transition-transform duration-200 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-content px-5 pb-5 text-slate-400 text-xs sm:text-sm leading-relaxed hidden">
              به دلیل معماری Zero-Knowledge در هسته وصله، با اضافه کردن یا خاموش کردن هر ماژول هیچ خطایی در سایر بخش‌ها به وجود نمی‌آید. دیتابیس جداول آن ماژول حفظ می‌شود و در صورت فعال‌سازی مجدد، اطلاعات شما دقیقاً مثل روز اول در دسترس خواهد بود.
            </div>
          </div>

        </div>

      </div>
    </section>

  </main>

  <!-- فوتر کامل و مدرن -->
  <footer class="border-t border-slate-800 bg-slate-950/80 pt-16 pb-12 relative z-10 text-slate-400 text-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-4 gap-8 mb-12">
      
      <div class="space-y-4 md:col-span-2">
        <div class="flex items-center gap-2 text-white font-bold text-lg">
          <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4m0 12v4M2 12h4m12 0h4"/></svg>
          </div>
          <span>پروژه وصله (Vasleh)</span>
        </div>
        <p class="text-slate-400 text-xs leading-relaxed max-w-md">
          طراحی‌شده برای شکستن مرزهای سنتی نرم‌افزارهای پیچیده ERP و حسابداری در ایران. سادگی، سرعت خارق‌العاده و کارایی بی‌وقفه در هاست‌های ارزان قیمت.
        </p>
        <div class="flex items-center gap-4 text-slate-400 pt-2">
          <a href="https://github.com/farsmd/vasleh" target="_blank" rel="noopener" class="hover:text-white transition-colors flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/></svg>
            <span>مخزن گیت‌هاب</span>
          </a>
          <span>•</span>
          <a href="https://atroweb.ir" target="_blank" rel="noopener" class="hover:text-white transition-colors">نمایش زنده در atroweb.ir</a>
        </div>
      </div>

      <div>
        <h4 class="text-white font-bold mb-3 text-sm">ماژول‌های محبوب</h4>
        <ul class="space-y-2">
          <li><a href="#modules" class="hover:text-blue-400">حسابداری و تراز مالی</a></li>
          <li><a href="#modules" class="hover:text-blue-400">فروشگاه با کد رهگیری اختصاصی</a></li>
          <li><a href="#modules" class="hover:text-blue-400">کنترل خط تولید و مصرف مواد</a></li>
          <li><a href="#modules" class="hover:text-blue-400">انبارداری و هشدار کسری</a></li>
          <li><a href="#modules" class="hover:text-blue-400">مدیریت وظایف موبایل‌محور (/me)</a></li>
        </ul>
      </div>

      <div>
        <h4 class="text-white font-bold mb-3 text-sm">پشتیبانی و ارتباط</h4>
        <ul class="space-y-2">
          <li class="text-slate-300">ساعت پاسخگویی: ۹ الی ۱۸ روزهای کاری</li>
          <li class="text-slate-300">پشتیبانی سریع: تیکت پنل ادمین و تلگرام</li>
          <li class="pt-2 text-emerald-400 font-semibold">تضمین بازگشت وجه تا ۷ روز پس از خرید</li>
        </ul>
      </div>

    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 border-t border-slate-900 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
      <div>تمامی حقوق محفوظ است © ۱۴۰۳ وصله (Vasleh) • نسخه ۵٫۸٫۱۷</div>
      <div>طراحی‌شده با عشق برای رونق تولیدکنندگان و بازرگانان ایرانی</div>
    </div>
  </footer>

  <!-- مودال پیش‌فاکتور و تأیید موفقیت‌آمیز سفارش -->
  <div id="orderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md hidden opacity-0 transition-opacity duration-300">
    <div class="glass-card rounded-2xl max-w-lg w-full p-6 sm:p-8 border border-emerald-500/30 text-right transform scale-95 transition-transform duration-300 shadow-2xl" id="modalCard">
      
      <div class="w-14 h-14 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto mb-5 border border-emerald-500/30">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
      </div>

      <h3 class="text-xl font-black text-white text-center mb-1">سفارش و تست ۱۵ روزه شما با موفقیت ثبت شد!</h3>
      <p class="text-xs text-slate-400 text-center mb-6">کد رهگیری درخواست: <strong class="text-cyan-400 font-mono" id="modalTrackId">VSL-TRIAL-8942</strong></p>

      <div class="bg-slate-950/70 p-4 rounded-xl border border-slate-800 text-xs space-y-2.5 text-slate-300 mb-6">
        <div class="flex justify-between">
          <span class="text-slate-400">نام متقاضی:</span>
          <span class="font-bold text-white" id="modalName">-</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400">نام برند:</span>
          <span class="font-bold text-white" id="modalBrand">-</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400">شماره پیامک:</span>
          <span class="font-bold text-white font-mono" id="modalMobile">-</span>
        </div>
        <div class="flex justify-between">
          <span class="text-slate-400">تعداد ماژول‌های فعال:</span>
          <span class="font-bold text-blue-400" id="modalModulesCount">۳ ماژول</span>
        </div>
        <div class="border-t border-slate-800 pt-2 flex justify-between font-bold">
          <span class="text-slate-300">مبلغ شارژ در روز شانزدهم:</span>
          <span class="text-emerald-400 font-black" id="modalPrice">-</span>
        </div>
      </div>

      <div class="p-3 rounded-lg bg-blue-500/10 border border-blue-500/20 text-[11px] text-blue-300 leading-relaxed mb-6">
        اطلاعات ورود موقت به همراه فایل راهنمای استقرار تا چند دقیقه دیگر به شماره همراه شما پیامک خواهد شد.
      </div>

      <button id="closeModalBtn" type="button" class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm transition-colors">
        بستن و بازگشت به صفحه اصلی
      </button>

    </div>
  </div>

  <!-- اسکریپت جاوااسکریپت برای محاسبات زنده قیمت، دوره‌ها، فرم و آکاردئون -->
  <script>
    // مقادیر ثابت تعرفه‌ها
    const MONTHLY_BASE = <?= (int)($prices['base'] ?? 199000) ?>;
    let isYearly = false;

    // عناصر رابط کاربری
    const btnMonthly = document.getElementById('btnMonthly');
    const btnYearly = document.getElementById('btnYearly');
    const displayBasePrice = document.getElementById('displayBasePrice');
    const displayBaseCycle = document.getElementById('displayBaseCycle');

    const moduleCards = document.querySelectorAll('.module-card');
    const checkboxes = document.querySelectorAll('.module-checkbox');
    
    // فاکتور
    const invoiceBasePrice = document.getElementById('invoiceBasePrice');
    const invoiceModuleCount = document.getElementById('invoiceModuleCount');
    const invoiceModulesPrice = document.getElementById('invoiceModulesPrice');
    const rowDiscount = document.getElementById('rowDiscount');
    const invoiceDiscount = document.getElementById('invoiceDiscount');
    const invoiceFinalTotal = document.getElementById('invoiceFinalTotal');

    // فرم و مودال
    const checkoutForm = document.getElementById('checkoutForm');
    const orderModal = document.getElementById('orderModal');
    const modalCard = document.getElementById('modalCard');
    const closeModalBtn = document.getElementById('closeModalBtn');

    // تبدیل اعداد به فرمت فارسی با جداکننده سه رقمی
    function toPersianNumber(number) {
      return number.toLocaleString('fa-IR');
    }

    // به‌روزرسانی قیمت‌ها و محاسبات فاکتور
    function updatePricing() {
      const discountMultiplier = isYearly ? 0.8 : 1.0;
      const cycleMonths = isYearly ? 12 : 1;

      // محاسبه پایه
      const calculatedBaseMonthly = Math.round(MONTHLY_BASE * discountMultiplier);
      const calculatedBaseTotal = calculatedBaseMonthly * cycleMonths;

      displayBasePrice.innerText = toPersianNumber(calculatedBaseMonthly);
      displayBaseCycle.innerText = isYearly ? 'تومان در ماه (پرداخت سالانه)' : 'تومان در ماه';
      invoiceBasePrice.innerText = toPersianNumber(calculatedBaseTotal) + ' تومان' + (isYearly ? ' (۱۲ ماه)' : '');

      let rawModulesSum = 0;
      let activeCount = 0;

      // بررسی ماژول‌های تیک خورده
      moduleCards.forEach(card => {
        const checkbox = card.querySelector('.module-checkbox');
        const rawPrice = parseInt(card.dataset.price, 10);
        const priceTextEl = card.querySelector('.module-price-text');

        // قیمت نمایشی داخل هر کارت ماژول
        const discountedModuleMonthly = Math.round(rawPrice * discountMultiplier);
        priceTextEl.innerText = toPersianNumber(discountedModuleMonthly);

        if (checkbox.checked) {
          card.classList.add('border-blue-500', 'bg-blue-950/20');
          card.classList.remove('border-slate-800');
          rawModulesSum += rawPrice;
          activeCount++;
        } else {
          card.classList.remove('border-blue-500', 'bg-blue-950/20');
          card.classList.add('border-slate-800');
        }
      });

      // جمع ماژول‌ها برای کل دوره
      const calculatedModulesTotal = Math.round(rawModulesSum * discountMultiplier) * cycleMonths;

      invoiceModuleCount.innerText = activeCount.toLocaleString('fa-IR');
      invoiceModulesPrice.innerText = toPersianNumber(calculatedModulesTotal) + ' تومان';

      const subtotalWithoutDiscount = (MONTHLY_BASE + rawModulesSum) * cycleMonths;
      const finalPayableTotal = (calculatedBaseTotal + calculatedModulesTotal);

      if (isYearly) {
        const totalSaved = subtotalWithoutDiscount - finalPayableTotal;
        rowDiscount.classList.remove('hidden');
        invoiceDiscount.innerText = toPersianNumber(totalSaved) + ' تومان';
      } else {
        rowDiscount.classList.add('hidden');
      }

      invoiceFinalTotal.innerHTML = toPersianNumber(finalPayableTotal) + ' <span class="text-xs font-normal text-slate-400">تومان</span>';
    }

    // سوییچ دوره‌های پرداخت
    btnMonthly.addEventListener('click', () => {
      isYearly = false;
      btnMonthly.className = 'px-5 py-2 rounded-lg text-sm font-bold bg-blue-600 text-white shadow-md transition-all';
      btnYearly.className = 'px-5 py-2 rounded-lg text-sm font-bold text-slate-400 hover:text-white transition-all flex items-center gap-1.5';
      updatePricing();
    });

    btnYearly.addEventListener('click', () => {
      isYearly = true;
      btnYearly.className = 'px-5 py-2 rounded-lg text-sm font-bold bg-blue-600 text-white shadow-md transition-all flex items-center gap-1.5';
      btnMonthly.className = 'px-5 py-2 rounded-lg text-sm font-bold text-slate-400 hover:text-white transition-all';
      updatePricing();
    });

    // رصد تغییر وضعیت ماژول‌ها
    checkboxes.forEach(cb => {
      cb.addEventListener('change', updatePricing);
    });

    // مدیریت آکاردئون سوالات متداول
    const faqButtons = document.querySelectorAll('.faq-btn');
    faqButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const content = btn.nextElementSibling;
        const icon = btn.querySelector('svg');
        const isHidden = content.classList.contains('hidden');

        // بستن بقیه آیتم‌ها
        document.querySelectorAll('.faq-content').forEach(c => c.classList.add('hidden'));
        document.querySelectorAll('.faq-btn svg').forEach(i => i.classList.remove('rotate-180'));

        if (isHidden) {
          content.classList.remove('hidden');
          icon.classList.add('rotate-180');
        }
      });
    });

    // ثبت فرم و باز کردن مودال تایید سفارش
    checkoutForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const name = document.getElementById('custName').value.trim();
      const mobile = document.getElementById('custMobile').value.trim();
      const brand = document.getElementById('custBrand').value.trim();

      if (!name || !mobile || !brand) {
        return;
      }

      let activeCount = 0;
      checkboxes.forEach(cb => { if(cb.checked) activeCount++; });

      // پر کردن فیلدهای مودال
      document.getElementById('modalName').innerText = name;
      document.getElementById('modalBrand').innerText = brand;
      document.getElementById('modalMobile').innerText = mobile;
      document.getElementById('modalModulesCount').innerText = activeCount.toLocaleString('fa-IR') + ' ماژول تخصصی';
      document.getElementById('modalPrice').innerText = invoiceFinalTotal.innerText + (isYearly ? ' (برای ۱۲ ماه)' : ' (برای یک ماه)');
      document.getElementById('modalTrackId').innerText = 'VSL-' + Math.floor(100000 + Math.random() * 900000);

      // نمایش انیمیشنی مودال
      orderModal.classList.remove('hidden');
      setTimeout(() => {
        orderModal.classList.remove('opacity-0');
        modalCard.classList.remove('scale-95');
        modalCard.classList.add('scale-100');
      }, 10);
    });

    // بستن مودال
    closeModalBtn.addEventListener('click', () => {
      orderModal.classList.add('opacity-0');
      modalCard.classList.remove('scale-100');
      modalCard.classList.add('scale-95');
      setTimeout(() => {
        orderModal.classList.add('hidden');
        checkoutForm.reset();
        updatePricing();
      }, 250);
    });

    // راه‌اندازی اولیه محاسبات
    window.onload = function() {
      updatePricing();
    };
  </script>
<?php if (!empty($customHtml)): ?><section class="custom-html"><?= $customHtml ?></section><?php endif; ?>
</body>
</html>