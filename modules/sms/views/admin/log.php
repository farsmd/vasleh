<div class="card">
  <h1>گزارش ارسال پیامک</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <?php if (empty($rows)): ?>
    <p>هنوز پیامکی ثبت نشده است.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>موبایل</th><th>نوع</th><th>متن</th><th>وضعیت</th><th>پاسخ سامانه</th><th>زمان (UTC)</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td dir="ltr" style="text-align: right;"><?= e($r['phone']) ?></td>
            <td><?= e($r['kind']) ?></td>
            <td><?= e($r['message']) ?></td>
            <td><?= $r['status'] === 'sent' ? 'ارسال شد' : 'ناموفق' ?></td>
            <td><?= e($r['provider_message']) ?></td>
            <td dir="ltr" style="text-align: right;"><?= e($r['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
