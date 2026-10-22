<div class="card">
  <h1>ویرایش ماژول «<?= e($module['title']) ?>»</h1>
  <form method="post" action="/admin/modules/edit/<?= e($module['slug']) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان</label>
    <input name="title" required value="<?= e($module['title']) ?>">
    <label>توضیح</label>
    <textarea name="description" rows="3"><?= e($module['description'] ?? '') ?></textarea>
    <label>نسخه</label>
    <input name="version" required value="<?= e($module['version']) ?>">
    <button class="btn">ذخیره</button>
    <a class="btn gray" href="/admin/modules">برگشت</a>
  </form>
  <p><small>این فیلدها روی لایه مشخصات اعمال می‌شوند. بقیه مقادیر ماژول پایین‌تر فقط نمایش‌اند، چون رفتاری‌اند و از خودِ کد ماژول می‌آیند. ویرایش مستقیم کد هم از بخش «فایل‌های ماژول» پایین صفحه ممکن است (فقط مالک، با بکاپ خودکار).</small></p>
</div>

<div class="card">
  <h3>مقادیر ماژول</h3>
  <table>
    <tr><th>نام لاتین (پوشه)</th><td><code><?= e($module['slug']) ?></code></td></tr>
    <tr><th>مسیر پوشه</th><td><code><?= e($details['dir']) ?></code></td></tr>
    <tr><th>نوع</th><td><?= !empty($module['system']) ? 'سیستمی (قابل غیرفعال‌کردن نیست)' : 'عادی' ?></td></tr>
    <tr><th>وضعیت</th><td><?= $details['enabled'] ? 'فعال' : 'غیرفعال' ?><?php if (!empty($details['error'])): ?> — خطا: <?= e($details['error']) ?><?php endif; ?></td></tr>
    <tr><th>وابستگی‌ها</th><td>
      <?php if (empty($details['requires'])): ?>—
      <?php else: foreach ($details['requires'] as $req): ?><code><?= e($req) ?></code> <?php endforeach; endif; ?>
    </td></tr>
    <tr><th>سرویس‌های ثبت‌شده</th><td>
      <?php if (empty($details['services'])): ?>—
      <?php else: foreach ($details['services'] as $service): ?><code><?= e($service) ?></code> <?php endforeach; endif; ?>
    </td></tr>
    <tr><th>هوک‌ها</th><td>
      <?php if (empty($details['hooks'])): ?>—
      <?php else: foreach ($details['hooks'] as $hook): ?><code><?= e($hook) ?></code> <?php endforeach; endif; ?>
    </td></tr>
  </table>
</div>

<div class="card">
  <h3>روت‌ها</h3>
  <?php if (empty($details['routes'])): ?>
    <p>این ماژول روتی ثبت نکرده است.</p>
  <?php else: ?>
  <table>
    <tr><th>متد</th><th>آدرس</th></tr>
    <?php foreach ($details['routes'] as $route): ?>
    <tr><td><code><?= e($route['method']) ?></code></td><td><code><?= e($route['pattern']) ?></code></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<div class="card">
  <h3>آدرس ویوها</h3>
  <?php if (empty($details['views'])): ?>
    <p>این ماژول ویویی ندارد.</p>
  <?php else: ?>
  <table>
    <tr><th>آدرس ویو</th><th>ناحیه</th><th>مسیر فایل</th></tr>
    <?php foreach ($details['views'] as $view): ?>
    <tr>
      <td><code><?= e($view['address']) ?></code></td>
      <td><?= $view['area'] === 'admin' ? 'مدیریت' : ($view['area'] === 'front' ? 'فرانت' : e($view['area'])) ?></td>
      <td><code><?= e($view['path']) ?></code></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<div class="card">
  <h3>دسترسی‌های اعلام‌شده</h3>
  <?php $permissions = (array) ($module['permissions'] ?? []); ?>
  <?php if (empty($permissions)): ?>
    <p>این ماژول دسترسی‌ای اعلام نکرده است.</p>
  <?php else: ?>
  <table>
    <tr><th>کلید دسترسی</th><th>عنوان</th></tr>
    <?php foreach ($permissions as $key => $label): ?>
    <tr><td><code><?= e($key) ?></code></td><td><?= e($label) ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <h3>آیتم‌های منوی مدیریت این ماژول</h3>
  <?php if (empty($details['menu'])): ?>
    <p>آیتمی در منوی مدیریت ندارد.</p>
  <?php else: ?>
  <table>
    <tr><th>عنوان منو</th><th>آدرس</th><th>دسترسی لازم</th></tr>
    <?php foreach ($details['menu'] as $item): ?>
    <tr>
      <td><?= e($item['label']) ?></td>
      <td><code><?= e($item['path']) ?></code></td>
      <td><?= !empty($item['permission']) ? '<code>' . e($item['permission']) . '</code>' : '—' ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<?php if (!empty($isOwner)): ?>
<div class="card">
  <h3>فایل‌های ماژول — ویرایشگر کد</h3>
  <p>ویرایش مستقیم فایل‌های این ماژول با ویرایشگر حرفه‌ای (فقط مالک). قبل از هر ذخیره، از نسخه فعلی بکاپ خودکار در <code>storage/backups</code> گرفته می‌شود و از همان‌جا قابل بازیابی است. اگر فایلی خراب شود، هسته فقط همین ماژول را کنار می‌گذارد و سیستم سالم می‌ماند.</p>
  <?php if (empty($files)): ?>
    <p>فایلی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>فایل</th><th>حجم</th><th></th></tr>
    <?php foreach ($files as $f): ?>
    <tr>
      <td><code dir="ltr"><?= e($f['path']) ?></code></td>
      <td><?= number_format($f['size']) ?> بایت</td>
      <td>
        <?php if ($f['editable']): ?>
          <a class="btn yellow" href="/admin/modules/file/<?= e($module['slug']) ?>?path=<?= urlencode($f['path']) ?>">ویرایش</a>
        <?php else: ?>
          <small>قابل ویرایش نیست</small>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php endif; ?>
