<div class="card">
  <form method="post" action="/me/notes/<?= (int) $note['id'] ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان</label>
    <input type="text" name="title" required value="<?= e($note['title']) ?>">
    <label>متن</label>
    <textarea name="body" rows="14"><?= e($note['body']) ?></textarea>
    <p><button class="btn green">ذخیره</button></p>
  </form>
  <form method="post" action="/me/notes/<?= (int) $note['id'] ?>/delete" onsubmit="return confirm('این یادداشت حذف شود؟')">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <button class="btn red">حذف یادداشت</button>
  </form>
  <?= $shareBox ?>
</div>
