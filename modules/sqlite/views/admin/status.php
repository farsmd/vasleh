<div class="card">
  <h1>وضعیت دیتابیس SQLite</h1>
  <p>مسیر فایل: <code><?= e($info['file']) ?></code></p>
  <p>حجم: <strong><?= e(number_format($info['size'])) ?></strong> بایت — نسخه SQLite: <strong><?= e($info['version']) ?></strong></p>
  <table>
    <tr><th>جدول</th><th>تعداد ردیف</th></tr>
    <?php foreach ($info['tables'] as $t): ?>
    <tr><td><code><?= e($t['name']) ?></code></td><td><?= e($t['rows']) ?></td></tr>
    <?php endforeach; ?>
    <?php if ($info['tables'] === []): ?><tr><td colspan="2">هنوز جدولی ساخته نشده است.</td></tr><?php endif; ?>
  </table>
</div>
