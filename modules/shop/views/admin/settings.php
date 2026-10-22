<div class="card">
  <h1>تنظیمات فروشگاه</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/shop/settings">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>عنوان فروشگاه
        <input type="text" name="shop_title" value="<?= e($values['shop_title']) ?>">
      </label>
      <label>واحد پول
        <input type="text" name="currency" value="<?= e($values['currency']) ?>">
      </label>
    </div>
    <div class="row">
      <label>هزینه ارسال (ثابت)
        <input type="number" name="shipping_cost" min="0" value="<?= e($values['shipping_cost']) ?>">
      </label>
      <label>ارسال رایگان برای خرید بالای (۰ = غیرفعال)
        <input type="number" name="free_shipping_over" min="0" value="<?= e($values['free_shipping_over']) ?>">
      </label>
      <label>مالیات (درصد — ۰ = بدون مالیات)
        <input type="number" name="tax_percent" min="0" max="100" value="<?= e($values['tax_percent']) ?>">
      </label>
    </div>
    <label>یادداشت ثابت روی صفحه ثبت سفارش (مثلاً شرایط پرداخت یا شماره کارت)
      <textarea name="order_note" rows="3" dir="auto"><?= e($values['order_note']) ?></textarea>
    </label>
    <p><button class="btn green">ذخیره تنظیمات</button></p>
  </form>
</div>
