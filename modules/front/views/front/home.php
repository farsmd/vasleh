<h1>وصله</h1>
<p>هر ماژول یک وصله است که به هسته وصل می‌شود.</p>
<p>این صفحه را ماژول <code>front</code> پاسخ داد.</p>
<p>درخواست <code>/</code> را index جمع کرد، هسته خواند و به ماژول فرانت سپرد.</p>
<h3>ماژول‌های سیستم</h3>
<ul>
  <?php foreach ($modules as $slug => $m): ?>
  <li><?= e($m['title']) ?> — <code><?= e($slug) ?></code> نسخه <?= e($m['version']) ?> (<?= $loader->isEnabled($slug) ? 'فعال' : 'غیرفعال' ?>)</li>
  <?php endforeach; ?>
</ul>
<p><a href="/admin">رفتن به بخش مدیریت</a></p>
