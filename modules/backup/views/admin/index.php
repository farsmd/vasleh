<?php
$typeLabels = ['full' => 'کامل', 'database' => 'دیتابیس', 'custom' => 'انتخابی', 'pre-restore' => 'ایمنیِ قبل از بازگردانی'];
$partLabels = ['files' => 'فایل‌ها', 'database' => 'دیتابیس', 'storage' => 'داده‌های storage'];
$freqLabels = ['daily' => 'روزانه', 'weekly' => 'هفتگی', 'monthly' => 'ماهانه'];
?>
<div class="card">
  <h1>پشتیبان‌گیری</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    تعداد بکاپ‌ها: <strong><?= count($backups) ?></strong> —
    حجم کل: <strong><?= number_format($totalSize) ?></strong> بایت —
    بکاپ دوره‌ای: <strong><?= !empty($periodic['enabled']) ? 'فعال (' . e($freqLabels[$periodic['freq']] ?? 'روزانه') . ')' : 'خاموش' ?></strong>
    <?php if ($lastRun > 0): ?>— آخرین اجرای خودکار: <?= e(date('Y-m-d H:i:s', $lastRun)) ?><?php endif; ?>
  </p>
</div>

<div class="card">
  <h3>ساخت بکاپ تازه</h3>
  <form method="post" action="/admin/backup/create" style="display:inline">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="kind" value="full">
    <button class="btn green">بکاپ کامل (فایل‌ها + دیتابیس + داده‌ها)</button>
  </form>
  <form method="post" action="/admin/backup/create" style="display:inline">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="kind" value="database">
    <button class="btn">فقط دیتابیس</button>
  </form>
  <form method="post" action="/admin/backup/create" style="margin-top:14px">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="kind" value="custom">
    <strong>بکاپ انتخابی:</strong>
    <label style="display:inline-flex;gap:6px;align-items:center;margin-inline-start:10px"><input type="checkbox" name="part_files" value="1" checked style="width:auto"> فایل‌های برنامه</label>
    <label style="display:inline-flex;gap:6px;align-items:center"><input type="checkbox" name="part_database" value="1" checked style="width:auto"> دیتابیس</label>
    <label style="display:inline-flex;gap:6px;align-items:center"><input type="checkbox" name="part_storage" value="1" style="width:auto"> بقیه داده‌های storage (کلید، تنظیمات، آپلودها)</label>
    <button class="btn yellow">ساخت بکاپ انتخابی</button>
  </form>
  <p><small>بکاپ‌ها در <code>storage/backups</code> می‌نشینند و پوشه بکاپ‌ها هیچ‌وقت داخل بکاپ نمی‌آید. دیتابیس با روش سازگار (VACUUM INTO) کپی می‌شود تا وسط نوشتن گرفته نشود.</small></p>
</div>

<div class="card">
  <h3>بکاپ دوره‌ای (خودکار)</h3>
  <form method="post" action="/admin/backup/settings">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label style="display:flex;gap:8px;align-items:center">
      <input type="checkbox" name="enabled" value="1" <?= !empty($periodic['enabled']) ? 'checked' : '' ?> style="width:auto">
      بکاپ خودکار فعال باشد
    </label>
    <label>دوره</label>
    <select name="freq">
      <?php foreach ($freqLabels as $k => $label): ?>
      <option value="<?= e($k) ?>" <?= ($periodic['freq'] ?? 'daily') === $k ? 'selected' : '' ?>><?= e($label) ?></option>
      <?php endforeach; ?>
    </select>
    <label>نوع بکاپ خودکار</label>
    <select name="type">
      <option value="full" <?= ($periodic['type'] ?? 'full') === 'full' ? 'selected' : '' ?>>کامل</option>
      <option value="database" <?= ($periodic['type'] ?? '') === 'database' ? 'selected' : '' ?>>فقط دیتابیس</option>
    </select>
    <label>چند بکاپ خودکار نگه داشته شود؟</label>
    <input name="keep" type="number" min="1" max="50" value="<?= (int) ($periodic['keep'] ?? 5) ?>">
    <button class="btn green">ذخیره تنظیمات دوره‌ای</button>
  </form>
  <p><small>روی هاست اشتراکی کرون واقعی همیشه در دسترس نیست؛ وصله در اولین بازدید بعد از موعد، بکاپ را خودکار می‌سازد و اضافه‌های قدیمی را هرس می‌کند.</small></p>
</div>

<div class="card">
  <h3>بازگردانی از فایل بکاپ</h3>
  <form method="post" action="/admin/backup/restore-upload" enctype="multipart/form-data">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>فایل ZIP بکاپ وصله</label>
    <input type="file" name="backup_zip" accept=".zip" required>
    <label style="display:flex;gap:8px;align-items:center">
      <input type="checkbox" name="confirm" value="1" style="width:auto">
      می‌دانم بازگردانی، وضعیت فعلی را بازنویسی می‌کند (قبلش بکاپ ایمنی گرفته می‌شود)
    </label>
    <button class="btn red">بازگردانی از فایل</button>
  </form>
</div>

<div class="card">
  <h3>بکاپ‌های موجود</h3>
  <?php if (empty($backups)): ?>
    <p>هنوز بکاپی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>نام</th><th>نوع</th><th>بخش‌ها</th><th>حجم</th><th>تاریخ ساخت</th><th>عملیات</th></tr>
    <?php foreach ($backups as $b): ?>
    <tr>
      <td><code dir="ltr"><?= e($b['name']) ?></code></td>
      <td><?= e($typeLabels[$b['type']] ?? $b['type']) ?><?= $b['auto'] ? ' (خودکار)' : '' ?></td>
      <td>
        <?php foreach (($b['parts'] ?? []) as $k => $on): ?>
          <?php if ($on): ?><small><?= e($partLabels[$k] ?? $k) ?></small> <?php endif; ?>
        <?php endforeach; ?>
      </td>
      <td><?= number_format((int) $b['size']) ?> بایت</td>
      <td><?= e($b['created'] !== '' ? $b['created'] : $b['time']) ?></td>
      <td>
        <a class="btn" href="/admin/backup/download/<?= e($b['name']) ?>">دانلود ZIP</a>
        <form method="post" action="/admin/backup/restore/<?= e($b['name']) ?>" style="display:inline" onsubmit="return confirm('این بکاپ بازگردانده شود؟ وضعیت فعلی بازنویسی می‌شود (بکاپ ایمنی گرفته می‌شود).')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="confirm" value="1">
          <button class="btn yellow">بازگردانی</button>
        </form>
        <form method="post" action="/admin/backup/delete/<?= e($b['name']) ?>" style="display:inline" onsubmit="return confirm('این بکاپ حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
