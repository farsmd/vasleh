<div class="card">
  <h2>یادداشت تازه</h2>
  <form method="post" action="/me/notes">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان</label>
    <input type="text" name="title" required placeholder="مثلاً ایده محصول">
    <p><button class="btn green">ساختن و نوشتن</button></p>
  </form>
</div>

<div class="card">
  <h2>یادداشت‌ها</h2>
  <?php if (empty($notes)): ?>
    <p class="muted">هنوز یادداشتی نیست.</p>
  <?php else: ?>
    <?php foreach ($notes as $n): ?>
      <p><a href="/me/notes/<?= (int) $n['id'] ?>"><?= e($n['title']) ?></a> <span class="muted"><?= e($n['updated_at']) ?></span></p>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
