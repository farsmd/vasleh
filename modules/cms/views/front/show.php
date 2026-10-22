<article>
  <h1><?= e($post['title']) ?></h1>
  <time><?= e($post['created_at']) ?></time>
  <div class="body"><?= $post['body'] ?></div>
</article>
<p><a href="<?= ($post['type'] ?? 'post') === 'page' ? '/' : '/blog' ?>">برگشت</a></p>
