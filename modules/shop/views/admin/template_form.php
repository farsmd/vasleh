<link rel="stylesheet" href="/assets/codemirror/codemirror.css">
<link rel="stylesheet" href="/assets/codemirror/theme/material-darker.css">
<style>
.CodeMirror{height:50vh;direction:ltr;text-align:left;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:13.5px;border-radius:10px}
</style>

<div class="card">
  <h1><?= !empty($tpl) ? 'ویرایش قالب' : 'قالب تازه' ?></h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= !empty($tpl) ? '/admin/shop/templates/edit/' . (int) $tpl['id'] : '/admin/shop/templates/create' ?>" id="tplForm">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>عنوان
      <input type="text" name="title" required value="<?= e($tpl['title'] ?? '') ?>" placeholder="مثلاً نکسوس">
    </label>
    <label>نام (فقط حروف لاتین کوچک، رقم، خط تیره و زیرخط)
      <input type="text" name="name" dir="ltr" required value="<?= e($tpl['name'] ?? '') ?>" placeholder="مثلاً nexus">
    </label>
    <label>محتوای قالب (ویرایشگر حرفه‌ای؛ Ctrl+S ذخیره می‌کند — حتماً جای {{content}} را داشته باشد)</label>
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:10px;">
      <button type="button" class="btn gray" id="copyCodeBtn">کپی کد</button>
      <span>اندازه فونت:</span>
      <button type="button" class="btn gray" id="fontMinusBtn">−</button>
      <span id="fontSizeLabel" dir="ltr">13.5px</span>
      <button type="button" class="btn gray" id="fontPlusBtn">+</button>
    </div>
    <textarea name="content" id="tplEditor" rows="10" dir="ltr" style="text-align: left;"><?= e($tpl['content'] ?? '') ?></textarea>
    <p>
      <button class="btn green"><?= !empty($tpl) ? 'ذخیره ویرایش' : 'ساخت قالب' ?></button>
      <a class="btn gray" href="/admin/shop/templates">انصراف</a>
    </p>
  </form>
</div>

<script src="/assets/codemirror/codemirror.js"></script>
<script src="/assets/codemirror/mode/xml.js"></script>
<script src="/assets/codemirror/mode/css.js"></script>
<script src="/assets/codemirror/mode/javascript.js"></script>
<script src="/assets/codemirror/mode/clike.js"></script>
<script src="/assets/codemirror/mode/htmlmixed.js"></script>
<script src="/assets/codemirror/addon/matchbrackets.js"></script>
<script src="/assets/codemirror/addon/active-line.js"></script>
<script src="/assets/codemirror/editor-tools.js"></script>
<script>
var tplEditor = CodeMirror.fromTextArea(document.getElementById('tplEditor'), {
  mode: 'htmlmixed',
  theme: 'material-darker',
  lineNumbers: true,
  matchBrackets: true,
  styleActiveLine: true,
  indentUnit: 4,
  tabSize: 4,
  extraKeys: {
    'Ctrl-S': function () { tplEditor.save(); document.getElementById('tplForm').submit(); },
    'Cmd-S': function () { tplEditor.save(); document.getElementById('tplForm').submit(); }
  }
});
vaslehEditorTools(tplEditor);
</script>
