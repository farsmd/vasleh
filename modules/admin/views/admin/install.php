<div class="card">
  <h1>نصب ماژول از ZIP</h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>هر نرم‌افزاری که برای وصله ساخته شود، یک ماژول است و از همین‌جا نصب می‌شود.
     بعد از نصب، ماژول خودش را به سیستم وصل می‌کند: روت‌ها، منوی مدیریت (با آیکون خودش)،
     دسترسی‌ها و جدول‌های دیتابیسش — بدون هیچ تغییری در هسته.</p>
  <form method="post" action="/admin/modules/install" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>فایل ZIP ماژول (حداکثر ۱۰ مگابایت)
      <input type="file" name="module_zip" accept=".zip" required>
    </label>
    <button class="btn green">نصب ماژول</button>
  </form>
</div>

<div class="card">
  <h3>ساختار بسته ماژول</h3>
  <p>داخل ZIP باید <strong>دقیقاً یک پوشه</strong> باشد که نامش نامک ماژول است
     (حروف کوچک لاتین، مثل <code>shop</code>) و داخل آن دست‌کم فایل <code>core.php</code>:</p>
  <pre dir="ltr" style="text-align:left">shop.zip
└── shop/
    ├── core.php        تعریف ماژول: slug, title, icon, routes, services, hooks, migrate, permissions, admin_menu
    ├── src/            کلاس‌های ماژول (اختیاری)
    └── views/          ویوها: views/admin/*.php و views/front/*.php (اختیاری)</pre>
  <p>قواعد نصب: روی ماژول موجود نصب نمی‌شود؛ مسیرهای خطرناک داخل ZIP رد می‌شوند؛
     مقدار <code>slug</code> داخل core.php باید با نام پوشه یکی باشد.
     اگر ماژول تازه مشکلی داشته باشد، هسته فقط خودش را کنار می‌گذارد و خطا در فهرست ماژول‌ها دیده می‌شود — سیستم سالم می‌ماند.</p>
  <p><a class="btn gray" href="/admin/modules">برگشت به فهرست ماژول‌ها</a></p>
</div>
