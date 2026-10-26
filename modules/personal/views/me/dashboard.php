<div class="card">
  <h2><?= e($today_label) ?></h2>
  <p class="muted"><?= count($events) ?> رویداد امروز · <?= (int) $open_todos ?> کار باز</p>
  <?php if (empty($events)): ?>
    <p class="muted">برای امروز رویدادی ثبت نشده است.</p>
  <?php else: ?>
    <?php foreach ($events as $ev): ?>
      <div class="ev"><span class="t"><?= e($ev['start_time']) ?> تا <?= e($ev['end_time']) ?></span><br><b><?= e($ev['title']) ?></b>
      <?php if (trim((string) $ev['note']) !== ''): ?><br><span class="muted"><?= e($ev['note']) ?></span><?php endif; ?></div>
    <?php endforeach; ?>
  <?php endif; ?>
  <p><a class="btn gray" href="/me/day">برنامه روز</a> <a class="btn gray" href="/me/week">برنامه هفته</a></p>
</div>

<div class="card">
  <h2>کارهای باز</h2>
  <?php if (empty($todos)): ?>
    <p class="muted">کاری باز نیست. 🎉</p>
  <?php else: ?>
    <?php foreach ($todos as $t): ?>
      <div class="todo">
        <form class="inline" method="post" action="/me/todos/<?= (int) $t['id'] ?>/toggle">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="checkbox" onchange="this.form.submit()">
        </form>
        <span><?= e($t['title']) ?></span>
        <?php if ($t['due_date'] !== ''): ?><span class="muted"><?= e($t['due_date']) ?></span><?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  <form method="post" action="/me/todos">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="from" value="/me">
    <label>کار تازه</label>
    <input type="text" name="title" required placeholder="مثلاً پیگیری سفارش">
    <div class="row">
      <div><label>سررسید (اختیاری)</label><input type="date" name="due_date"></div>
      <div><label>&nbsp;</label><button class="btn green">افزودن</button></div>
    </div>
  </form>
  <p><a href="/me/todos">همه کارها</a></p>
</div>

<div class="card">
  <h2>پروژه‌ها</h2>
  <?php if (empty($projects)): ?>
    <p class="muted">هنوز پروژه‌ای نیست.</p>
  <?php else: ?>
    <?php foreach ($projects as $p): ?>
      <p><a href="/me/projects/<?= (int) $p['id'] ?>"><?= e($p['title']) ?></a>
      <?php if ($p['start_date'] !== ''): ?><span class="muted"> · شروع <?= e($p['start_date']) ?></span><?php endif; ?>
      <?php if ((int) $p['hours_needed'] > 0): ?><span class="muted"> · <?= (int) $p['hours_needed'] ?> ساعت</span><?php endif; ?></p>
    <?php endforeach; ?>
  <?php endif; ?>
  <p><a class="btn gray" href="/me/projects">همه پروژه‌ها</a></p>
</div>

<div class="card">
  <h2>یادداشت‌ها</h2>
  <?php if (empty($notes)): ?>
    <p class="muted">هنوز یادداشتی نیست.</p>
  <?php else: ?>
    <?php foreach (array_slice($notes, 0, 5) as $n): ?>
      <p><a href="/me/notes/<?= (int) $n['id'] ?>"><?= e($n['title']) ?></a> <span class="muted"><?= e($n['updated_at']) ?></span></p>
    <?php endforeach; ?>
  <?php endif; ?>
  <p><a class="btn gray" href="/me/notes">همه یادداشت‌ها</a></p>
</div>

<?php if (!empty($shared)): ?>
<div class="card">
  <h2>اشتراک‌گذاشته‌شده با من</h2>
  <?php foreach ($shared as $s): ?>
    <p><a href="/me/shared/<?= e($s['item_type']) ?>/<?= (int) $s['item_id'] ?>"><?= e($s['title']) ?></a>
    <span class="muted"> · از <?= e($s['owner_name']) ?> · <?= $s['item_type'] === 'project' ? 'پروژه' : 'یادداشت' ?></span></p>
  <?php endforeach; ?>
</div>
<?php endif; ?>
