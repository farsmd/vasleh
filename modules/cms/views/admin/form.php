<?php $assetBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/'); ?>
<div class="card">
  <h1><?= e($post['title'] === '' ? 'محتوای تازه' : 'ویرایش محتوا') ?></h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان</label>
    <input name="title" required value="<?= e($post['title']) ?>">
    <label>نامک (آدرس) — خالی بماند از عنوان ساخته می‌شود</label>
    <input name="slug" dir="ltr" value="<?= e($post['slug']) ?>" placeholder="my-post">
    <label>نوع</label>
    <select name="type">
      <option value="post" <?= ($post['type'] ?? 'post') === 'post' ? 'selected' : '' ?>>نوشته (وبلاگ)</option>
      <option value="page" <?= ($post['type'] ?? '') === 'page' ? 'selected' : '' ?>>برگه (صفحه ثابت)</option>
    </select>
    <label>وضعیت</label>
    <select name="status">
      <option value="draft" <?= ($post['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
      <option value="published" <?= ($post['status'] ?? '') === 'published' ? 'selected' : '' ?>>منتشرشده</option>
    </select>
    <label>خلاصه (برای فهرست وبلاگ)</label>
    <textarea name="excerpt" rows="2"><?= e($post['excerpt']) ?></textarea>
    <label>متن (HTML)</label>
    <textarea name="body" id="cmsBody" rows="14" dir="ltr"><?= e($post['body']) ?></textarea>
    <p>
      <button class="btn green">ذخیره</button>
      <a class="btn gray" href="/admin/cms">برگشت به فهرست</a>
    </p>
  </form>
</div>
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/codemirror/codemirror.css">
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/codemirror/theme/material-darker.css">
<style>.CodeMirror{height:42vh;direction:ltr;text-align:left;font-size:13.5px;border-radius:10px}</style>
<script src="<?= e($assetBase) ?>/assets/codemirror/codemirror.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/xml.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/css.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/javascript.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/htmlmixed.js"></script>
<script>
if (window.CodeMirror) {
  CodeMirror.fromTextArea(document.getElementById('cmsBody'), {
    mode: 'htmlmixed', theme: 'material-darker', lineNumbers: true, lineWrapping: true
  });
}
</script>
