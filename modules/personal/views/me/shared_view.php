<div class="card">
  <p><span class="pill">فقط خواندنی</span> <span class="muted"><?= e($type_label) ?> اشتراک‌گذاشته‌شده</span></p>
  <h2><?= e($item['title']) ?></h2>
  <?php if ($type === 'project'): ?>
    <?php if (trim((string) $item['description']) !== ''): ?><p><?= e($item['description']) ?></p><?php endif; ?>
    <p class="muted">
      <?php if ($item['start_date'] !== ''): ?>شروع <?= e($item['start_date']) ?> · <?php endif; ?>
      <?php if ((int) $item['hours_needed'] > 0): ?><?= (int) $item['hours_needed'] ?> ساعت مورد نیاز<?php endif; ?>
    </p>
    <h3>لیست نیازها</h3>
    <?php if (empty($needs)): ?>
      <p class="muted">نیازی ثبت نشده است.</p>
    <?php else: ?>
      <?php foreach ($needs as $n): ?>
        <div class="need"><span class="<?= (int) $n['done'] === 1 ? 'done' : '' ?>"><?= (int) $n['done'] === 1 ? '✓ ' : '' ?><?= e($n['title']) ?></span></div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php else: ?>
    <p><?= nl2br(e($item['body'])) ?></p>
  <?php endif; ?>
</div>
