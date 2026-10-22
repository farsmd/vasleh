<div class="card">
  <h1><?= !empty($recipe['id']) ? 'ویرایش فرمول' : 'فرمول تازه' ?></h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>عنوان فرمول
        <input type="text" name="title" required value="<?= e($recipe['title']) ?>" placeholder="مثلاً چراغ خطی یک‌متری">
      </label>
      <label>کالای خروجی (محصول ساخته‌شده از انبار)
        <select name="output_item_id" required>
          <option value="0">— انتخاب کنید —</option>
          <?php foreach ($items as $it): ?>
            <option value="<?= (int) $it['id'] ?>" <?= (int) $recipe['output_item_id'] === (int) $it['id'] ? 'selected' : '' ?>><?= e($it['title']) ?> (<?= e($it['unit']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>خروجی هر بچ
        <input type="number" name="output_qty" step="any" min="0" value="<?= e($recipe['output_qty']) ?>">
      </label>
    </div>
    <label>یادداشت
      <input type="text" name="note" value="<?= e($recipe['note']) ?>">
    </label>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="active" value="1" <?= (int) ($recipe['active'] ?? 1) === 1 ? 'checked' : '' ?>> فعال
    </label>

    <fieldset style="border:1px solid #ddd;border-radius:.5rem;margin:1rem 0;padding:1rem">
      <legend>مواد اولیه هر بچ</legend>
      <div id="line-rows">
        <?php $idx = 0; foreach ($lines as $ln): ?>
        <div class="line-row" style="display:flex;gap:.5rem;align-items:end;margin-bottom:.5rem">
          <label style="flex:2">ماده اولیه
            <select name="line[<?= $idx ?>][material_item_id]">
              <option value="0">— انتخاب کنید —</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int) $it['id'] ?>" <?= (int) ($ln['material_item_id'] ?? 0) === (int) $it['id'] ? 'selected' : '' ?>><?= e($it['title']) ?> (<?= e($it['unit']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </label>
          <label style="flex:1">مقدار مصرف
            <input type="number" name="line[<?= $idx ?>][qty]" step="any" min="0" value="<?= e($ln['qty'] ?? '') ?>">
          </label>
          <button type="button" class="btn red" onclick="this.closest('.line-row').remove()">حذف</button>
        </div>
        <?php $idx++; endforeach; ?>
      </div>
      <template id="line-template">
        <div class="line-row" style="display:flex;gap:.5rem;align-items:end;margin-bottom:.5rem">
          <label style="flex:2">ماده اولیه
            <select name="line[__I__][material_item_id]">
              <option value="0">— انتخاب کنید —</option>
              <?php foreach ($items as $it): ?>
                <option value="<?= (int) $it['id'] ?>"><?= e($it['title']) ?> (<?= e($it['unit']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </label>
          <label style="flex:1">مقدار مصرف
            <input type="number" name="line[__I__][qty]" step="any" min="0">
          </label>
          <button type="button" class="btn red" onclick="this.closest('.line-row').remove()">حذف</button>
        </div>
      </template>
      <p><button type="button" class="btn gray" id="add-line">+ افزودن ماده</button></p>
      <script>
        (function () {
          var idx = <?= (int) $idx ?>;
          document.getElementById('add-line').addEventListener('click', function () {
            var html = document.getElementById('line-template').innerHTML.split('__I__').join(idx++);
            var wrap = document.createElement('div');
            wrap.innerHTML = html;
            document.getElementById('line-rows').appendChild(wrap.firstElementChild);
          });
        })();
      </script>
    </fieldset>

    <p>
      <button class="btn green">ذخیره فرمول</button>
      <a class="btn gray" href="/admin/production/recipes">بازگشت</a>
    </p>
  </form>
</div>
