<div class="card">
  <h1>فیلدهای لندینگ</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>هر فیلد یک «عنوان» (برای نمایش در همین فهرست)، یک «نام» (نام توکن) و یک «محتوا» دارد. در قالب لندینگ با <code dir="ltr">{{نام}}</code> به فیلد ارجاع بدهید؛ محتوا موقع نمایش جایگزین می‌شود. محتوا خام رندر می‌شود (HTML هم مجاز است) چون فقط مالک به این صفحه دسترسی دارد.</p>
  <p>فیلد تازه‌ای که می‌سازید تا وقتی توکنش در قالب نباشد نمایش داده نمی‌شود؛ توکن را با ویرایشگر فایل ماژول‌ها در <code>modules/front/views/front/landing.php</code> به قالب اضافه کنید.</p>
  <p>قیمت‌ها هم ردیف‌های همین فهرست‌اند (نام‌های <code dir="ltr">price_*</code> با برچسب «قیمت»)؛ محتوایشان باید عدد باشد و مستقیم روی ماشین‌حساب لندینگ اثر می‌گذارد. حذف یک قیمت، قیمت پیش‌فرض را برمی‌گرداند.</p>
  <p><a class="btn green" href="#fieldForm">افزودن فیلد</a></p>
  <?php if (empty($fields)): ?>
    <p>هنوز فیلدی نیست.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>عنوان</th><th>نام (توکن)</th><th>محتوا</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($fields as $f): ?>
          <tr>
            <td><?= e($f['title']) ?><?= str_starts_with((string) $f['name'], 'price_') ? ' <span class="btn green">قیمت</span>' : '' ?></td>
            <td dir="ltr" style="text-align: right;"><?= e('{{' . $f['name'] . '}}') ?></td>
            <td class="wrap"><?= e((string) $f['content']) ?></td>
            <td>
              <a class="btn yellow" href="/admin/landing/fields?edit=<?= (int) $f['id'] ?>">ویرایش</a>
              <form method="post" action="/admin/landing/fields/<?= (int) $f['id'] ?>/delete" onsubmit="return confirm('این فیلد حذف شود؟ توکن‌هایش در قالب خالی می‌مانند.');" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn red">حذف</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<div class="card">
  <h1><?= !empty($edit) ? 'ویرایش فیلد' : 'افزودن فیلد' ?></h1>
  <form method="post" action="/admin/landing/fields/save" id="fieldForm">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <label>عنوان
      <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>" placeholder="مثلاً عنوان اصلی">
    </label>
    <label>نام فیلد (نام توکن — با حرف شروع شود؛ فقط حروف، رقم و زیرخط)
      <input type="text" name="name" id="fieldName" dir="ltr" required value="<?= e($edit['name'] ?? '') ?>" placeholder="مثلاً hero_title">
    </label>
    <p>توکن: <code id="tokenPreview" dir="ltr">{{}}</code>
      <button type="button" class="btn gray" id="copyTokenBtn">کپی توکن</button>
    </p>
    <p id="priceHint" style="display:none">این فیلد قیمت است؛ محتوا باید عدد صحیح (تومان) باشد.</p>
    <label>محتوا
      <textarea name="content" id="fieldContent" rows="8"><?= e($edit['content'] ?? '') ?></textarea>
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت فیلد' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/landing/fields">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<script>
(function () {
  var form = document.getElementById('fieldForm');
  var nameInput = document.getElementById('fieldName');
  var contentInput = document.getElementById('fieldContent');
  var preview = document.getElementById('tokenPreview');
  var hint = document.getElementById('priceHint');
  function sync() {
    var n = nameInput.value.trim();
    preview.textContent = n ? '{{' + n + '}}' : '{{}}';
    hint.style.display = n.indexOf('price_') === 0 ? '' : 'none';
  }
  nameInput.addEventListener('input', sync);
  sync();
  document.getElementById('copyTokenBtn').onclick = function () {
    var n = nameInput.value.trim();
    if (!n) { return; }
    var txt = '{{' + n + '}}', btn = this;
    function done() {
      var old = btn.textContent;
      btn.textContent = 'کپی شد!';
      setTimeout(function () { btn.textContent = old; }, 1500);
    }
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(txt).then(done, function () {});
    }
  };
  form.addEventListener('submit', function (ev) {
    var n = nameInput.value.trim();
    var c = contentInput.value.trim();
    if (n.indexOf('price_') === 0 && !/^\d+$/.test(c)) {
      ev.preventDefault();
      alert('محتوای فیلد قیمت باید عدد صحیح باشد (تومان).');
    }
  });
})();
</script>
<?php if (!empty($edit)): ?>
<script>document.getElementById('fieldForm').scrollIntoView();</script>
<?php endif; ?>
