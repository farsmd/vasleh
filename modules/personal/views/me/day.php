<div class="nav2">
  <a class="btn gray" href="/me/day?date=<?= e($prev) ?>">◀ روز قبل</a>
  <a class="btn gray" href="/me/day">امروز</a>
  <a class="btn gray" href="/me/day?date=<?= e($next) ?>">روز بعد ▶</a>
</div>

<div class="card">
  <h2><?= e($label) ?></h2>
  <?php if (empty($events)): ?>
    <p class="muted">برای این روز رویدادی ثبت نشده است.</p>
  <?php else: ?>
    <?php foreach ($events as $ev): ?>
      <div class="ev"><span class="t"><?= e($ev['start_time']) ?> تا <?= e($ev['end_time']) ?></span><br><b><?= e($ev['title']) ?></b>
      <?php if (trim((string) $ev['note']) !== ''): ?><br><span class="muted"><?= e($ev['note']) ?></span><?php endif; ?>
      <form class="inline" method="post" action="/me/events/<?= (int) $ev['id'] ?>/delete" onsubmit="return confirm('این رویداد حذف شود؟')">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="date" value="<?= e($date) ?>">
        <button class="btn red small">حذف</button>
      </form></div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>رویداد تازه (ساعتی)</h2>
  <form method="post" action="/me/events">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان</label>
    <input type="text" name="title" required placeholder="مثلاً جلسه با مشتری">
    <input type="hidden" name="date" value="<?= e($date) ?>">
    <div class="row">
      <div><label>شروع</label><input type="time" name="start_time" required value="09:00"></div>
      <div><label>پایان</label><input type="time" name="end_time" required value="10:00"></div>
    </div>
    <label>یادداشت (اختیاری)</label>
    <input type="text" name="note">
    <p><button class="btn green">افزودن به این روز</button></p>
  </form>
</div>
