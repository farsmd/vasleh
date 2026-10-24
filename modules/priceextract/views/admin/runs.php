<div class="card">
  <h1>سوابق استخراج — <?= e($source['title']) ?></h1>
  <p><a class="btn gray" href="/admin/priceextract">بازگشت به منابع</a></p>
  <?php if (empty($runs)): ?>
    <p>هنوز اجرایی ثبت نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>تاریخ اجرا</th><th>آیتم‌های استخراج‌شده</th><th>خطا</th></tr>
    <?php foreach ($runs as $r): ?>
    <tr>
      <td><?= e($r['created_at']) ?></td>
      <td><?= (int) $r['items'] ?></td>
      <td class="wrap"><?= e($r['error']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
