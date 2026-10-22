<?php $assetBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/'); ?>
<div class="card">
  <h1><?= e(($layout['name'] ?? '') === '' ? 'چیدمان تازه' : 'ویرایش چیدمان') ?> — <?= e($labels[$layout['kind']] ?? '') ?></h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="kind" value="<?= e($layout['kind']) ?>">
    <label>نام</label>
    <input name="name" required value="<?= e($layout['name']) ?>">
    <label>نامک (لاتین) — خالی بماند از نام ساخته می‌شود</label>
    <input name="slug" dir="ltr" value="<?= e($layout['slug']) ?>" placeholder="my-header">
    <label>HTML چیدمان</label>
    <textarea name="html" id="layoutHtml" rows="16" dir="ltr"><?= e($layout['html']) ?></textarea>
    <label>CSS اختصاصی این چیدمان (فقط روی صفحاتی که از این چیدمان استفاده می‌کنند)</label>
    <textarea name="css" id="layoutCss" rows="10" dir="ltr"><?= e($layout['css'] ?? '') ?></textarea>
    <p><small>جانگهدارها: <code dir="ltr">{{content}}</code> (فقط قالب)، <code dir="ltr">{{title}}</code>، <code dir="ltr">{{excerpt}}</code>، <code dir="ltr">{{date}}</code>، <code dir="ltr">{{site_name}}</code>، <code dir="ltr">{{menu:main}}</code></small></p>
    <label style="display:flex;gap:8px;align-items:center">
      <input type="checkbox" name="is_default" value="1" <?= !empty($layout['is_default']) ? 'checked' : '' ?> style="width:auto">
      این چیدمان پیش‌فرضِ نوع خودش باشد
    </label>
    <p>
      <button class="btn green">ذخیره</button>
      <a class="btn gray" href="/admin/cms/layouts">برگشت</a>
    </p>
  </form>
</div>
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/codemirror/codemirror.css">
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/codemirror/theme/material-darker.css">
<style>.CodeMirror{height:48vh;direction:ltr;text-align:left;font-size:13.5px;border-radius:10px}</style>
<script src="<?= e($assetBase) ?>/assets/codemirror/codemirror.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/xml.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/css.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/javascript.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/htmlmixed.js"></script>
<script>
if (window.CodeMirror) {
  CodeMirror.fromTextArea(document.getElementById('layoutHtml'), {
    mode: 'htmlmixed', theme: 'material-darker', lineNumbers: true, lineWrapping: true
  });
  CodeMirror.fromTextArea(document.getElementById('layoutCss'), {
    mode: 'css', theme: 'material-darker', lineNumbers: true, lineWrapping: true
  });
}
</script>
