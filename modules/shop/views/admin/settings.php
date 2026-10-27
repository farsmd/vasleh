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
      <label>آستانه کمبود موجودی (کالایی با موجودی ≤ این عدد در فهرست «کمبود» می‌خورد)
        <input type="number" name="low_stock_threshold" min="0" value="<?= e($values['low_stock_threshold']) ?>">
      </label>
    </div>
    <label>یادداشت ثابت روی صفحه ثبت سفارش (مثلاً شرایط پرداخت یا شماره کارت)
      <textarea name="order_note" rows="3" dir="auto"><?= e($values['order_note']) ?></textarea>
    </label>
    <div class="row">
      <label>رنگ اصلی فروشگاه
        <input type="color" name="shop_accent" value="<?= e($values['shop_accent']) ?>">
      </label>
      <label>هزینه بسته‌بندی کادویی (۰ = غیرفعال)
        <input type="number" name="gift_wrap_price" min="0" value="<?= e($values['gift_wrap_price']) ?>">
      </label>
    </div>
    <label>بازه‌های تحویل (هر خط یک بازه؛ خالی = بدون انتخاب)
      <textarea name="delivery_slots" rows="3" dir="auto"><?= e($values['delivery_slots']) ?></textarea>
    </label>
    <label>روش‌های پرداخت (هر خط «کلید: برچسب»؛ مثلاً online:پرداخت اینترنتی)
      <textarea name="payment_methods" rows="3" dir="auto"><?= e($values['payment_methods']) ?></textarea>
    </label>
    <p><button class="btn green">ذخیره تنظیمات</button></p>
  </form>
</div>
