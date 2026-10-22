<?php
$assetBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
$modeMap = [
    'php'  => 'php',
    'css'  => 'css',
    'js'   => 'javascript',
    'html' => 'htmlmixed',
    'json' => 'json',
];
$cmMode = $modeMap[$ext] ?? 'null';
?>
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/codemirror/codemirror.css">
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/codemirror/theme/material-darker.css">
<style>
.CodeMirror{height:65vh;direction:ltr;text-align:left;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13.5px;border-radius:10px}
.editor-head{display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between}
</style>

<div class="card">
  <div class="editor-head">
    <div>
      <h1 style="margin-bottom:4px">ویرایش فایل ماژول «<?= e($module['title'] ?? $slug) ?>»</h1>
      <div><code dir="ltr"><?= e($slug) ?>/<?= e($path) ?></code></div>
    </div>
    <a class="btn gray" href="/admin/modules/edit/<?= e($slug) ?>">برگشت به ویرایش ماژول</a>
  </div>
  <?php if (!empty($module['system'])): ?>
    <div class="error" style="margin-top:12px">این یک ماژول <strong>سیستمی</strong> است؛ ویرایشش قدرتمند و حساس است. قبل از هر ذخیره بکاپ خودکار گرفته می‌شود.</div>
  <?php endif; ?>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="/admin/modules/file/<?= e($slug) ?>" id="editorForm">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="path" value="<?= e($path) ?>">
    <input type="hidden" name="action" value="save">
    <textarea name="content" id="codeEditor" dir="ltr"><?= e($content) ?></textarea>
    <p style="margin-top:12px">
      <button class="btn green">ذخیره فایل</button>
      <small style="color:#6b7280">میانبر: Ctrl+S — قبل از ذخیره، نسخه فعلی خودکار بکاپ می‌شود.</small>
    </p>
  </form>
</div>

<div class="card">
  <h3>بکاپ‌های این فایل</h3>
  <?php if (empty($backups)): ?>
    <p>هنوز بکاپی نیست؛ با اولین ذخیره ساخته می‌شود.</p>
  <?php else: ?>
  <table>
    <tr><th>زمان ساخت</th><th>حجم</th><th>عملیات</th></tr>
    <?php foreach ($backups as $b): ?>
    <tr>
      <td><?= e($b['time']) ?></td>
      <td><?= number_format($b['size']) ?> بایت</td>
      <td>
        <form method="post" action="/admin/modules/file/<?= e($slug) ?>" onsubmit="return confirm('فایل به این بکاپ برگردد؟ (وضعیت فعلی هم بکاپ می‌شود)')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="path" value="<?= e($path) ?>">
          <input type="hidden" name="action" value="restore">
          <input type="hidden" name="backup" value="<?= e($b['name']) ?>">
          <button class="btn yellow">بازیابی</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<script src="<?= e($assetBase) ?>/assets/codemirror/codemirror.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/xml.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/css.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/javascript.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/clike.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/htmlmixed.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/php.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/addon/matchbrackets.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/addon/active-line.js"></script>
<script>
var cmMode = <?= $cmMode === 'json' ? "{name: 'javascript', json: true}" : ($cmMode === 'null' ? 'null' : "'" . $cmMode . "'") ?>;
var editor = CodeMirror.fromTextArea(document.getElementById('codeEditor'), {
  mode: cmMode,
  theme: 'material-darker',
  lineNumbers: true,
  matchBrackets: true,
  styleActiveLine: true,
  indentUnit: 4,
  tabSize: 4,
  extraKeys: {
    'Ctrl-S': function () { document.getElementById('editorForm').submit(); },
    'Cmd-S': function () { document.getElementById('editorForm').submit(); }
  }
});
</script>
