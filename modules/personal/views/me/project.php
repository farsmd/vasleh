<div class="card">
  <form method="post" action="/me/projects/<?= (int) $project['id'] ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان پروژه</label>
    <input type="text" name="title" required value="<?= e($project['title']) ?>">
    <label>توضیح</label>
    <input type="text" name="description" value="<?= e($project['description']) ?>">
    <div class="row">
      <div><label>شروع</label><input type="date" name="start_date" value="<?= e($project['start_date']) ?>"></div>
      <div><label>زمان مورد نیاز (ساعت)</label><input type="number" name="hours_needed" min="0" value="<?= (int) $project['hours_needed'] ?>"></div>
    </div>
    <p><button class="btn green">ذخیره</button></p>
  </form>
  <form method="post" action="/me/projects/<?= (int) $project['id'] ?>/delete" onsubmit="return confirm('این پروژه با همه نیازهایش حذف شود؟')">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <button class="btn red">حذف پروژه</button>
  </form>
</div>

<div class="card">
  <h2>لیست نیازها</h2>
  <?php
    $done = count(array_filter($needs, static fn ($n) => (int) $n['done'] === 1));
  ?>
  <p class="muted"><?= $done ?> از <?= count($needs) ?> نیاز انجام شده</p>
  <?php if (empty($needs)): ?>
    <p class="muted">هنوز نیازی ثبت نشده است.</p>
  <?php else: ?>
    <?php foreach ($needs as $n): ?>
      <div class="need">
        <form class="inline" method="post" action="/me/projects/needs/<?= (int) $n['id'] ?>/toggle">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
          <input type="checkbox" <?= (int) $n['done'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
        </form>
        <span class="<?= (int) $n['done'] === 1 ? 'done' : '' ?>"><?= e($n['title']) ?></span>
        <form class="inline" method="post" action="/me/projects/needs/<?= (int) $n['id'] ?>/delete" onsubmit="return confirm('این نیاز حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
          <button class="btn red small">حذف</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  <form method="post" action="/me/projects/<?= (int) $project['id'] ?>/needs">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>نیاز تازه</label>
    <input type="text" name="title" required placeholder="مثلاً خرید دامنه">
    <p><button class="btn green">افزودن نیاز</button></p>
  </form>
</div>

<div class="card">
  <?= $shareBox ?>
</div>
