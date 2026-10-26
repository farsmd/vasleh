<div class="nav2">
  <a class="btn gray" href="/me/week?date=<?= e($prev) ?>">هفته قبل ▶</a>
  <a class="btn gray" href="/me/week">این هفته</a>
  <a class="btn gray" href="/me/week?date=<?= e($next) ?>">هفته بعد ◀</a>
</div>

<div class="card">
  <h2>هفته <?= \Modules\Personal\Personal::bothDates($monday) ?> تا <?= \Modules\Personal\Personal::bothDates($sunday) ?></h2>
  <?php foreach ($days as $d): ?>
    <div class="dayblock">
      <h3><a href="/me/day?date=<?= e($d) ?>"><?= \Modules\Personal\Personal::dayLabel($d) ?>، <?= \Modules\Personal\Personal::faWeekday($d) ?> <?= \Modules\Personal\Personal::bothDates($d) ?></a></h3>
      <?php $evs = $grouped[$d] ?? []; ?>
      <?php if (empty($evs)): ?>
        <p class="muted">بدون رویداد</p>
      <?php else: ?>
        <?php foreach ($evs as $ev): ?>
          <div class="ev"><span class="t"><?= e($ev['start_time']) ?> تا <?= e($ev['end_time']) ?></span> <b><?= e($ev['title']) ?></b></div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
