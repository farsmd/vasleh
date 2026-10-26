<div class="card">
  <h2>کارها</h2>
  <form method="post" action="/me/todos">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>کار تازه</label>
    <input type="text" name="title" required placeholder="مثلاً پیگیری سفارش">
    <div class="row">
      <div><label>سررسید (اختیاری)</label><input type="date" name="due_date"></div>
      <div><label>&nbsp;</label><button class="btn green">افزودن</button></div>
    </div>
  </form>
</div>

<div class="card">
  <?php if (empty($todos)): ?>
    <p class="muted">هنوز کاری نیست.</p>
  <?php else: ?>
    <?php foreach ($todos as $t): ?>
      <div class="todo">
        <form class="inline" method="post" action="/me/todos/<?= (int) $t['id'] ?>/toggle">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="checkbox" <?= (int) $t['done'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()">
        </form>
        <span class="<?= (int) $t['done'] === 1 ? 'done' : '' ?>"><?= e($t['title']) ?></span>
        <?php if ($t['due_date'] !== ''): ?><span class="muted"><?= e($t['due_date']) ?></span><?php endif; ?>
        <form class="inline" method="post" action="/me/todos/<?= (int) $t['id'] ?>/delete" onsubmit="return confirm('این کار حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red small">حذف</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
