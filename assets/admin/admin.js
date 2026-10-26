/* جاوااسکریپت پوسته مدیریت وصله: تم، سایدبار موبایل، آکاردئون منو، جستجوی منو، جدول ریسپانسیو. */
(function () {
  // تم تاریک/روشن (پیش‌فرض تاریک؛ انتخاب در localStorage می‌ماند)
  var themeToggleBtn = document.getElementById('themeToggleBtn');
  var themeIconSun = document.getElementById('themeIconSun');
  var themeIconMoon = document.getElementById('themeIconMoon');
  var htmlEl = document.documentElement;
  function applyTheme(isDark) {
    if (isDark) {
      htmlEl.classList.add('dark');
      if (themeIconSun) themeIconSun.classList.remove('hidden');
      if (themeIconMoon) themeIconMoon.classList.add('hidden');
    } else {
      htmlEl.classList.remove('dark');
      if (themeIconSun) themeIconSun.classList.add('hidden');
      if (themeIconMoon) themeIconMoon.classList.remove('hidden');
    }
    try { localStorage.setItem('vasleh_theme', isDark ? 'dark' : 'light'); } catch (e) {}
  }
  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', function () {
      applyTheme(!htmlEl.classList.contains('dark'));
    });
  }

  // سایدبار موبایل (Offcanvas از راست در RTL)
  var sidebar = document.getElementById('sidebar');
  var openSidebarBtn = document.getElementById('openSidebarBtn');
  var closeSidebarBtn = document.getElementById('closeSidebarBtn');
  var mobileBackdrop = document.getElementById('mobileBackdrop');
  function openMobileSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('translate-x-full');
    if (mobileBackdrop) {
      mobileBackdrop.classList.remove('hidden');
      setTimeout(function () { mobileBackdrop.classList.remove('opacity-0'); }, 10);
    }
  }
  function closeMobileSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('translate-x-full');
    if (mobileBackdrop) {
      mobileBackdrop.classList.add('opacity-0');
      setTimeout(function () { mobileBackdrop.classList.add('hidden'); }, 300);
    }
  }
  if (openSidebarBtn) openSidebarBtn.addEventListener('click', openMobileSidebar);
  if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeMobileSidebar);
  if (mobileBackdrop) mobileBackdrop.addEventListener('click', closeMobileSidebar);

  // منوی گروهی آکاردئونی: فقط یک گروه باز می‌ماند؛ گروه صفحه فعلی را سرور باز کرده است.
  document.querySelectorAll('.group-head').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var group = btn.closest('.menu-group');
      if (!group) return;
      var willOpen = !group.classList.contains('open');
      document.querySelectorAll('.menu-group.open').forEach(function (g) {
        g.classList.remove('open');
        var items = g.querySelector('.group-items');
        if (items) items.classList.add('hidden');
        var h = g.querySelector('.group-head');
        if (h) h.setAttribute('aria-expanded', 'false');
      });
      if (willOpen) {
        group.classList.add('open');
        var own = group.querySelector('.group-items');
        if (own) own.classList.remove('hidden');
      }
      btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });
  });
  // چرخش فلش گروه باز
  var style = document.createElement('style');
  style.textContent = '.menu-group.open .g-chev{transform:rotate(180deg)}.g-chev{transition:transform .2s ease}';
  document.head.appendChild(style);

  // جدول‌های ویوهای ماژول‌ها را ریسپانسیو می‌کند تا روی موبایل بیرون نزنند.
  document.querySelectorAll('.admin-content table').forEach(function (t) {
    if (t.closest('.table-responsive')) return;
    var w = document.createElement('div');
    w.className = 'table-responsive';
    t.parentNode.insertBefore(w, t);
    w.appendChild(t);
  });

  // جستجوی سریع منو (Spotlight): فقط آیتم‌های واقعی منو، بدون پیشنهاد نمایشی.
  var spotlightTrigger = document.getElementById('spotlightTrigger');
  var spotlightModal = document.getElementById('spotlightModal');
  var spotlightCard = document.getElementById('spotlightCard');
  var spotlightInput = document.getElementById('spotlightInput');
  var spotlightResults = document.getElementById('spotlightResults');
  var items = Array.isArray(window.VASLEH_SPOTLIGHT) ? window.VASLEH_SPOTLIGHT : [];
  function renderSpotlight(q) {
    if (!spotlightResults) return;
    q = (q || '').trim();
    var found = items.filter(function (it) {
      return q === '' || it.label.indexOf(q) !== -1;
    });
    if (found.length === 0) {
      spotlightResults.innerHTML = '<div class="p-2.5 text-slate-500">چیزی پیدا نشد.</div>';
      return;
    }
    found.forEach(function (it) {
      var a = document.createElement('a');
      a.href = it.path;
      a.className = 'p-2.5 rounded-xl hover:bg-blue-600/10 border border-transparent cursor-pointer flex items-center justify-between text-slate-600 dark:text-slate-300';
      var span = document.createElement('span');
      span.textContent = it.label;
      a.appendChild(span);
      spotlightResults.appendChild(a);
    });
  }
  function openSpotlight() {
    if (!spotlightModal) return;
    spotlightModal.classList.remove('hidden');
    renderSpotlight(spotlightInput ? spotlightInput.value : '');
    setTimeout(function () {
      spotlightModal.classList.remove('opacity-0');
      if (spotlightCard) spotlightCard.classList.remove('scale-95');
      if (spotlightInput) spotlightInput.focus();
    }, 10);
  }
  function closeSpotlight() {
    if (!spotlightModal) return;
    spotlightModal.classList.add('opacity-0');
    if (spotlightCard) spotlightCard.classList.add('scale-95');
    setTimeout(function () { spotlightModal.classList.add('hidden'); }, 200);
  }
  if (spotlightTrigger) spotlightTrigger.addEventListener('click', openSpotlight);
  if (spotlightInput) spotlightInput.addEventListener('input', function () { renderSpotlight(spotlightInput.value); });
  window.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      openSpotlight();
    }
    if (e.key === 'Escape') closeSpotlight();
  });
  if (spotlightModal) spotlightModal.addEventListener('click', function (e) {
    if (e.target === spotlightModal) closeSpotlight();
  });
})();
