<h1 style="margin-top:0">وبلاگ</h1>
<?php if (empty($posts)): ?>
  <p>هنوز نوشته‌ای منتشر نشده است.</p>
<?php endif; ?>
<?php foreach ($posts as $post): ?>
  <div class="post-card">
    <h2><a href="/blog/<?= e(rawurlencode((string) $post['slug'])) ?>"><?= e($post['title']) ?></a></h2>
    <?php if (($post['excerpt'] ?? '') !== ''): ?><p><?= e($post['excerpt']) ?></p><?php endif; ?>
    <time><?= e($post['created_at']) ?></time>
  </div>
<?php endforeach; ?>
