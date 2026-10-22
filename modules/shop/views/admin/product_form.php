<div class="card">
  <h1><?= !empty($product['id']) ? 'ویرایش محصول' : 'محصول تازه' ?></h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="<?= e($action) ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>عنوان *
        <input type="text" name="title" required value="<?= e($product['title']) ?>">
      </label>
      <label>نامک (خالی = ساخت خودکار از عنوان)
        <input type="text" name="slug" dir="ltr" value="<?= e($product['slug']) ?>">
      </label>
      <label>کد کالا (SKU)
        <input type="text" name="sku" dir="ltr" value="<?= e($product['sku']) ?>">
      </label>
      <label>دسته
        <select name="category_id">
          <option value="0">— بدون دسته —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) $product['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </div>
    <div class="row">
      <label>قیمت پایه
        <input type="number" name="price" min="0" value="<?= (int) $product['price'] ?>">
      </label>
      <label>قیمت فروش (۰ = بدون تخفیف)
        <input type="number" name="sale_price" min="0" value="<?= (int) $product['sale_price'] ?>">
      </label>
      <label>موجودی
        <input type="number" name="stock" value="<?= (int) $product['stock'] ?>">
      </label>
      <label>وضعیت
        <select name="status">
          <option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
          <option value="published" <?= $product['status'] === 'published' ? 'selected' : '' ?>>منتشرشده</option>
        </select>
      </label>
      <label>ترتیب
        <input type="number" name="sort" value="<?= (int) $product['sort'] ?>">
      </label>
    </div>
    <label class="check">
      <input type="checkbox" name="track_stock" value="1" <?= (int) $product['track_stock'] === 1 ? 'checked' : '' ?>>
      ردیابی موجودی (تمام‌شدن موجودی = جلوگیری از سفارش بیشتر)
    </label>
    <label>آدرس تصویر محصول (اختیاری)
      <input type="text" name="image" dir="ltr" value="<?= e($product['image']) ?>">
    </label>
    <label>توضیح کوتاه
      <input type="text" name="short_desc" value="<?= e($product['short_desc']) ?>">
    </label>
    <label>توضیح کامل (HTML مجاز است)
      <textarea name="description" rows="7" dir="auto"><?= e($product['description']) ?></textarea>
    </label>

    <?php if (!empty($fields)): ?>
    <fieldset>
      <legend>فیلدهای اختصاصی محصول</legend>
      <?php foreach ($fields as $f): $val = $values[(int) $f['id']] ?? ''; ?>
        <?php if ($f['type'] === 'textarea'): ?>
          <label><?= e($f['title']) ?><?= (int) $f['required'] === 1 ? ' *' : '' ?>
            <textarea name="field_<?= (int) $f['id'] ?>" rows="3"><?= e($val) ?></textarea>
          </label>
        <?php elseif ($f['type'] === 'select'): ?>
          <label><?= e($f['title']) ?><?= (int) $f['required'] === 1 ? ' *' : '' ?>
            <select name="field_<?= (int) $f['id'] ?>">
              <option value="">—</option>
              <?php foreach (preg_split('/\r\n|\r|\n/', (string) $f['options']) as $opt): $opt = trim($opt); if ($opt === '') continue; ?>
                <option value="<?= e($opt) ?>" <?= $val === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
        <?php elseif ($f['type'] === 'checkbox'): ?>
          <label class="check">
            <input type="hidden" name="field_<?= (int) $f['id'] ?>" value="">
            <input type="checkbox" name="field_<?= (int) $f['id'] ?>" value="1" <?= $val === '1' ? 'checked' : '' ?>>
            <?= e($f['title']) ?>
          </label>
        <?php else: ?>
          <label><?= e($f['title']) ?><?= (int) $f['required'] === 1 ? ' *' : '' ?>
            <input type="<?= $f['type'] === 'number' ? 'number' : 'text' ?>" name="field_<?= (int) $f['id'] ?>" value="<?= e($val) ?>">
          </label>
        <?php endif; ?>
      <?php endforeach; ?>
    </fieldset>
    <?php endif; ?>

    <fieldset>
      <legend>واریانت‌ها (ترکیب ویژگی‌ها — مثل رنگ × سایز، با قیمت و موجودی جدا)</legend>
      <?php if (empty($attributes)): ?>
        <p class="muted">هنوز ویژگی‌ای تعریف نشده است. اول از «ویژگی‌های محصول» چند ویژگی (رنگ، سایز…) بسازید، بعد اینجا برای محصول واریانت تعریف کنید.</p>
      <?php else: ?>
      <div id="variant-rows">
        <?php $rowIdx = 0; foreach ($variants as $v): $opts = $v['options'] ?? []; ?>
        <div class="variant-row">
          <?php foreach ($attributes as $a): ?>
            <label><?= e($a['title']) ?>
              <select name="variant[<?= $rowIdx ?>][attr][<?= e($a['slug']) ?>]">
                <option value="">—</option>
                <?php foreach ($a['values'] as $av): ?>
                  <option value="<?= e($av['value']) ?>" <?= ($opts[$a['slug']] ?? '') === $av['value'] ? 'selected' : '' ?>><?= e($av['value']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
          <?php endforeach; ?>
          <label>قیمت (خالی = قیمت پایه)
            <input type="number" name="variant[<?= $rowIdx ?>][price]" min="0" value="<?= $v['price'] !== null ? (int) $v['price'] : '' ?>">
          </label>
          <label>قیمت فروش
            <input type="number" name="variant[<?= $rowIdx ?>][sale_price]" min="0" value="<?= $v['sale_price'] !== null ? (int) $v['sale_price'] : '' ?>">
          </label>
          <label>موجودی
            <input type="number" name="variant[<?= $rowIdx ?>][stock]" value="<?= (int) $v['stock'] ?>">
          </label>
          <label>کد کالا
            <input type="text" name="variant[<?= $rowIdx ?>][sku]" dir="ltr" value="<?= e($v['sku']) ?>">
          </label>
          <button type="button" class="btn red" onclick="this.closest('.variant-row').remove()">حذف</button>
        </div>
        <?php $rowIdx++; endforeach; ?>
      </div>
      <template id="variant-template">
        <div class="variant-row">
          <?php foreach ($attributes as $a): ?>
            <label><?= e($a['title']) ?>
              <select name="variant[__I__][attr][<?= e($a['slug']) ?>]">
                <option value="">—</option>
                <?php foreach ($a['values'] as $av): ?>
                  <option value="<?= e($av['value']) ?>"><?= e($av['value']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
          <?php endforeach; ?>
          <label>قیمت (خالی = قیمت پایه)<input type="number" name="variant[__I__][price]" min="0"></label>
          <label>قیمت فروش<input type="number" name="variant[__I__][sale_price]" min="0"></label>
          <label>موجودی<input type="number" name="variant[__I__][stock]" value="0"></label>
          <label>کد کالا<input type="text" name="variant[__I__][sku]" dir="ltr"></label>
          <button type="button" class="btn red" onclick="this.closest('.variant-row').remove()">حذف</button>
        </div>
      </template>
      <p><button type="button" class="btn gray" id="add-variant">+ افزودن واریانت</button></p>
      <script>
        (function () {
          var idx = <?= (int) $rowIdx ?>;
          document.getElementById('add-variant').addEventListener('click', function () {
            var html = document.getElementById('variant-template').innerHTML.split('__I__').join(idx++);
            var wrap = document.createElement('div');
            wrap.innerHTML = html;
            document.getElementById('variant-rows').appendChild(wrap.firstElementChild);
          });
        })();
      </script>
      <?php endif; ?>
    </fieldset>

    <p>
      <button class="btn green">ذخیره محصول</button>
      <a class="btn gray" href="/admin/shop/products">بازگشت</a>
    </p>
  </form>
</div>
<style>
  .variant-row{display:flex;flex-wrap:wrap;gap:.5rem;align-items:end;border:1px solid #ddd;border-radius:.5rem;padding:.6rem;margin-bottom:.6rem}
  .variant-row label{flex:1 1 8rem}
  fieldset{border:1px solid #ddd;border-radius:.5rem;margin:1rem 0;padding:1rem}
  label.check{display:flex;gap:.4rem;align-items:center}
</style>
