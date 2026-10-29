/* Service worker تسک‌یار — نسخهٔ اول PWA برای ‎/me‎.
 * اسکوپ: ‎/me/‎ (این فایل باید از مسیر ‎/me/sw.js‎ سرو شود، نه از ‎/assets/‎؛
 * ورکرِ سرو‌شده از ‎/assets/‎ فقط همان پوشه را کنترل می‌کند و صفحه‌های ‎/me‎ را نه).
 *
 * رفتار نسخهٔ اول، عامدانه محافظه‌کارانه است:
 * - فایل‌های استاتیک (CSS، فونت، آیکون): cache-first، با به‌روزرسانی در پس‌زمینه.
 * - صفحه‌های ‎/me‎: network-first؛ اگر شبکه نبود، صفحهٔ کش‌شده، و در نهایت
 *   صفحهٔ آفلاین ‎/assets/pwa/offline.html‎ نشان داده می‌شود.
 * - درخواست‌های POST (ثبت/تیک/حذف) هرگز کش نمی‌شوند و همیشه به شبکه می‌روند؛
 *   پس ثبت آفلاین در نسخهٔ اول وجود ندارد و آفلاین بودن یعنی «مشاهدهٔ صفحهٔ
 *   آفلاین»، نه «ثبت کار بدون اینترنت». همگام‌سازی آفلاین قدم بعدی است، نه این نسخه.
 */
'use strict';

var CACHE = 'taskyar-pwa-v2';
var OFFLINE_URL = '/assets/pwa/offline.html';
var STATIC_ASSETS = [
  '/assets/taskyar/me-shell.css',
  '/assets/taskyar/tailwind.css',
  '/assets/taskyar/fonts/vazirmatn.css',
  '/assets/taskyar/fa/css/all.min.css',
  '/assets/pwa/icon-192.png',
  '/assets/pwa/icon-512.png',
  OFFLINE_URL
];

self.addEventListener('install', function (event) {
  event.waitUntil(
    caches.open(CACHE).then(function (cache) {
      return cache.addAll(STATIC_ASSETS);
    }).then(function () {
      return self.skipWaiting();
    })
  );
});

self.addEventListener('activate', function (event) {
  event.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(keys.filter(function (k) { return k !== CACHE; }).map(function (k) {
        return caches.delete(k);
      }));
    }).then(function () {
      return self.clients.claim();
    })
  );
});

function isStaticAsset(url) {
  return url.pathname.indexOf('/assets/') === 0;
}

self.addEventListener('fetch', function (event) {
  var request = event.request;
  // فقط GET را لمس کن؛ POST و بقیه همیشه مستقیم به شبکه می‌روند.
  if (request.method !== 'GET') { return; }
  var url = new URL(request.url);
  // فقط درخواست‌های همین مبدأ (atroweb.ir) را مدیریت کن.
  if (url.origin !== self.location.origin) { return; }

  if (isStaticAsset(url)) {
    // cache-first برای استاتیک، با به‌روزرسانی آرام در پس‌زمینه.
    // نکتهٔ مهم: ignoreSearch عمداً حذف شده — چون نسخهٔ CSS با ?v= در URL
    // می‌آید و باید کلید کشِ جدا داشته باشد؛ وگرنه CSS قدیمیِ کش‌شده به‌جای
    // نسخهٔ تازه سرو می‌شد و چیدمانِ تازه خراب دیده می‌شد.
    event.respondWith(
      caches.match(request).then(function (cached) {
        var network = fetch(request).then(function (response) {
          if (response && response.status === 200) {
            var copy = response.clone();
            caches.open(CACHE).then(function (cache) { cache.put(request, copy); });
          }
          return response;
        }).catch(function () { return cached; });
        return cached || network;
      })
    );
    return;
  }

  // صفحه‌ها (‎/me‎ و زیرمسیرهایش): network-first.
  if (request.mode === 'navigate' || url.pathname.indexOf('/me') === 0) {
    event.respondWith(
      fetch(request).then(function (response) {
        return response;
      }).catch(function () {
        return caches.match(request, { ignoreSearch: true }).then(function (cached) {
          return cached || caches.match(OFFLINE_URL);
        });
      })
    );
  }
});
