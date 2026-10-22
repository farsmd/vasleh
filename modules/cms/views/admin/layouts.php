<?php
$sections = [
    'header'   => $headers,
    'template' => $templates,
    'footer'   => $footers,
];
$assetBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
?>
<div class="card">
  <h1>CSS کلی سایت</h1>
  <p>این استایل روی همه صفحات فرانت (وبلاگ، برگه‌ها و صفحه اصلیِ محتوا) اعمال می‌شود و از آدرس <code dir="ltr">/cms.css</code> سرو می‌شود. هر چیدان هم می‌تواند CSS اختصاصی خودش را داشته باشد.</p>
  <form method="post" action="/admin/cms/layouts/css">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <textarea name="site_css" id="siteCss" rows="14" dir="ltr"><?= e($siteCss) ?></textarea>
    <p><button class="btn green">ذخیره CSS کلی</button></p>
  </form>
</div>
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/codemirror/codemirror.css">
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/codemirror/theme/material-darker.css">
<style>.CodeMirror{height:38vh;direction:ltr;text-align:left;font-size:13.5px;border-radius:10px}</style>
<script src="<?= e($assetBase) ?>/assets/codemirror/codemirror.js"></script>
<script src="<?= e($assetBase) ?>/assets/codemirror/mode/css.js"></script>
<script>
if (window.CodeMirror) {
  CodeMirror.fromTextArea(document.getElementById('siteCss'), {
    mode: 'css', theme: 'material-darker', lineNumbers: true, lineWrapping: true
  });
}
</script>

<div class="card">
  <h1>هدر، فوتر و قالب‌ها</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>هر محتوا از هدر، فوتر و قالبِ انتخابی خودش استفاده می‌کند؛ اگر انتخابی نداشته باشد، «پیش‌فرض» همان نوع می‌آید. داخل HTML می‌توانید از جانگهدارها استفاده کنید:
  <code dir="ltr">{{content}}</code> متن محتوا (فقط قالب)، <code dir="ltr">{{title}}</code>، <code dir="ltr">{{date}}</code>، <code dir="ltr">{{site_name}}</code> و <code dir="ltr">{{menu:main}}</code> برای نمایش یک منو.</p>
</div>

<?php foreach ($sections as $kind => $rows): ?>
<div class="card">
  <h3><?= e($labels[$kind]) ?>ها</h3>
  <p><a class="btn green" href="/admin/cms/layouts/create?kind=<?= e($kind) ?>">+ <?= e($labels[$kind]) ?> تازه</a></p>
  <?php if (empty($rows)): ?>
    <p>هنوز <?= e($labels[$kind]) ?>ی نیست؛ نمایش با نمونه داخلی انجام می‌شود.</p>
  <?php else: ?>
  <table>
    <tr><th>نام</th><th>نامک</th><th>وضعیت</th><th>به‌روزرسانی</th><th>عملیات</th></tr>
    <?php foreach ($rows as $row): ?>
    <tr>
      <td><?= e($row['name']) ?></td>
      <td><code dir="ltr"><?= e($row['slug']) ?></code></td>
      <td><?= !empty($row['is_default']) ? 'پیش‌فرض' : '' ?></td>
      <td><?= e($row['updated_at']) ?></td>
      <td>
        <a class="btn yellow" href="/admin/cms/layouts/edit/<?= (int) $row['id'] ?>">ویرایش</a>
        <?php if (empty($row['is_default'])): ?>
        <form method="post" action="/admin/cms/layouts/default/<?= (int) $row['id'] ?>" style="display:inline">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn gray">پیش‌فرض کن</button>
        </form>
        <?php endif; ?>
        <form method="post" action="/admin/cms/layouts/delete/<?= (int) $row['id'] ?>" style="display:inline" onsubmit="return confirm('این چیدمان حذف شود؟ محتواهایی که از آن استفاده می‌کنند به پیش‌فرض برمی‌گردند.')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php endforeach; ?>
