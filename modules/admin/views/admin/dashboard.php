<div class="card">
  <h1>داشبورد مدیریت</h1>
  <p>این صفحه را ماژول <code>admin</code> پاسخ داد — خود مدیریت هم یک ماژول است.</p>
  <table>
    <tr><th>ماژول</th><th>وضعیت</th></tr>
    <?php foreach ($modules as $slug => $m): ?>
    <tr>
      <td><?= e($m['title']) ?> — <code><?= e($slug) ?></code></td>
      <td><?php if (!empty($errors[$slug])): ?><span style="color:#b91c1c"><?= e($errors[$slug]) ?></span><?php else: ?><?= $loader->isEnabled($slug) ? 'فعال' : 'غیرفعال' ?><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
