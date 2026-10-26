<div class="card">
  <h2>اشتراک‌گذاشته‌شده با من</h2>
  <p class="muted">این آیتم‌ها را فقط می‌توانید ببینید؛ ویرایششان دست مالک است.</p>
  <?php if (empty($shared)): ?>
    <p class="muted">هنوز چیزی با شما به اشتراک گذاشته نشده است.</p>
  <?php else: ?>
    <?php foreach ($shared as $s): ?>
      <p><a href="/me/shared/<?= e($s['item_type']) ?>/<?= (int) $s['item_id'] ?>"><?= e($s['title']) ?></a>
      <span class="muted"> · از <?= e($s['owner_name']) ?> · <?= $s['item_type'] === 'project' ? 'پروژه' : 'یادداشت' ?></span></p>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
