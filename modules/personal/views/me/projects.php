<div class="card">
  <h2>پروژه تازه</h2>
  <form method="post" action="/me/projects">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان پروژه</label>
    <input type="text" name="title" required placeholder="مثلاً راه‌اندازی فروشگاه">
    <label>توضیح (اختیاری)</label>
    <input type="text" name="description">
    <div class="row">
      <div><label>شروع (اختیاری)</label><input type="date" name="start_date"></div>
      <div><label>زمان مورد نیاز (ساعت)</label><input type="number" name="hours_needed" min="0" value="0"></div>
    </div>
    <p><button class="btn green">ساختن پروژه</button></p>
  </form>
</div>

<div class="card">
  <h2>پروژه‌ها</h2>
  <?php if (empty($projects)): ?>
    <p class="muted">هنوز پروژه‌ای نیست.</p>
  <?php else: ?>
    <?php foreach ($projects as $p): ?>
      <p><a href="/me/projects/<?= (int) $p['id'] ?>"><?= e($p['title']) ?></a>
      <?php if ($p['start_date'] !== ''): ?><span class="muted"> · شروع <?= \Modules\Personal\Personal::bothDates($p['start_date']) ?></span><?php endif; ?>
      <?php if ((int) $p['hours_needed'] > 0): ?><span class="muted"> · <?= (int) $p['hours_needed'] ?> ساعت مورد نیاز</span><?php endif; ?></p>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
