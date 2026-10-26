<link rel="stylesheet" href="/assets/codemirror/codemirror.css">
<link rel="stylesheet" href="/assets/codemirror/theme/material-darker.css">
<style>
.CodeMirror{height:65vh;direction:ltr;text-align:left;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13.5px;border-radius:10px}
</style>

<div class="card">
  <h1>ویرایش صفحه لندینگ</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>این قالبِ خودِ صفحه اصلی است (<code dir="ltr">modules/front/views/front/landing.php</code>) — ساختار، بخش‌ها و ماشین‌حساب. برای تغییر متن‌ها از «فیلدهای لندینگ» استفاده کنید؛ اینجا فقط وقتی ساختار را می‌خواهید عوض کنید.</p>
  <p><strong>احتیاط:</strong> خطای نوشتاری در این فایل صفحه اصلی را از کار می‌اندازد. قبل از هر ذخیره، بکاپ خودکار گرفته می‌شود و از فهرست زیر قابل بازیابی است. تغییرها معمولاً ظرف چند ثانیه (کش PHP) روی صفحه اصلی می‌نشینند.</p>
  <form method="post" action="/admin/landing/edit" id="landingEditForm">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="save">
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:10px;">
      <button type="button" class="btn gray" id="copyCodeBtn">کپی کد</button>
      <span>اندازه فونت:</span>
      <button type="button" class="btn gray" id="fontMinusBtn">−</button>
      <span id="fontSizeLabel" dir="ltr">13.5px</span>
      <button type="button" class="btn gray" id="fontPlusBtn">+</button>
    </div>
    <textarea name="content" id="landingEditor" dir="ltr" style="text-align: left;"><?= e($content) ?></textarea>
    <p><button class="btn green">ذخیره (Ctrl+S)</button></p>
  </form>
</div>

<?php if (!empty($backups)): ?>
<div class="card">
  <h1>بکاپ‌ها (۱۰ نسخه آخر)</h1>
  <table>
    <thead><tr><th>نام</th><th>زمان</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($backups as $b): ?>
        <tr>
          <td dir="ltr" style="text-align: right;"><?= e($b['name']) ?></td>
          <td><?= e($b['time']) ?></td>
          <td>
            <form method="post" action="/admin/landing/edit" onsubmit="return confirm('نسخه فعلی با بکاپ جایگزین شود؟ (از نسخه فعلی هم بکاپ گرفته می‌شود)');" style="display:inline;">
              <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="action" value="restore">
              <input type="hidden" name="backup" value="<?= e($b['name']) ?>">
              <button class="btn yellow">بازیابی</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<script src="/assets/codemirror/codemirror.js"></script>
<script src="/assets/codemirror/mode/xml.js"></script>
<script src="/assets/codemirror/mode/css.js"></script>
<script src="/assets/codemirror/mode/javascript.js"></script>
<script src="/assets/codemirror/mode/clike.js"></script>
<script src="/assets/codemirror/mode/htmlmixed.js"></script>
<script src="/assets/codemirror/mode/php.js"></script>
<script src="/assets/codemirror/addon/matchbrackets.js"></script>
<script src="/assets/codemirror/addon/active-line.js"></script>
<script src="/assets/codemirror/editor-tools.js"></script>
<script>
var landingEditor = CodeMirror.fromTextArea(document.getElementById('landingEditor'), {
  mode: 'php',
  theme: 'material-darker',
  lineNumbers: true,
  matchBrackets: true,
  styleActiveLine: true,
  indentUnit: 4,
  tabSize: 4,
  extraKeys: {
    'Ctrl-S': function () { landingEditor.save(); document.getElementById('landingEditForm').submit(); },
    'Cmd-S': function () { landingEditor.save(); document.getElementById('landingEditForm').submit(); }
  }
});
vaslehEditorTools(landingEditor);
</script>
