<div class="card">
  <h1>فیلدهای لندینگ</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>هر فیلد یک «عنوان» (برای نمایش در همین فهرست)، یک «نام» (نام توکن) و یک «محتوا» دارد. در قالب لندینگ با <code dir="ltr">{{نام}}</code> به فیلد ارجاع بدهید؛ محتوا موقع نمایش جایگزین می‌شود. محتوا خام رندر می‌شود (HTML هم مجاز است) چون فقط مالک به این صفحه دسترسی دارد.</p>
  <p>فیلد تازه‌ای که می‌سازید تا وقتی توکنش در قالب نباشد نمایش داده نمی‌شود؛ توکن را با ویرایشگر فایل ماژول‌ها در <code>modules/front/views/front/landing.php</code> به قالب اضافه کنید.</p>
  <?php if (empty($fields)): ?>
    <p>هنوز فیلدی نیست.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>عنوان</th><th>نام (توکن)</th><th>محتوا</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($fields as $f): ?>
          <tr>
            <td><?= e($f['title']) ?></td>
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
  <h1>قیمت‌های لندینگ</h1>
  <p>قیمت‌های صفحه اصلی (تومان/ماه). تغییرها بلافاصله روی لندینگ اعمال می‌شوند.</p>
  <form method="post" action="/admin/landing/fields/prices">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>اشتراک پایه
      <input type="number" name="price_base" min="0" required value="<?= (int) $prices['base'] ?>">
    </label>
    <?php foreach ($modules as $slug => $m): ?>
      <label><?= e($m['title']) ?>
        <input type="number" name="price_<?= e($slug) ?>" min="0" required value="<?= (int) $prices[$slug] ?>">
      </label>
    <?php endforeach; ?>
    <p><button class="btn green">ذخیره قیمت‌ها</button></p>
  </form>
</div>

<div class="card">
  <h1><?= !empty($edit) ? 'ویرایش فیلد' : 'افزودن فیلد' ?></h1>
  <form method="post" action="/admin/landing/fields/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <label>عنوان
      <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>" placeholder="مثلاً عنوان اصلی">
    </label>
    <label>نام فیلد (نام توکن — با حرف شروع شود؛ فقط حروف، رقم و زیرخط)
      <input type="text" name="name" dir="ltr" required value="<?= e($edit['name'] ?? '') ?>" placeholder="مثلاً hero_title">
    </label>
    <label>محتوا
      <textarea name="content" rows="4"><?= e($edit['content'] ?? '') ?></textarea>
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت فیلد' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/landing/fields">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>
