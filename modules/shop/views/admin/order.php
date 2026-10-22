<div class="card">
  <h1>سفارش <code dir="ltr"><?= e($order['code']) ?></code></h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <table>
    <tr><th>مشتری</th><td><?= e($order['customer_name']) ?></td><th>موبایل</th><td dir="ltr"><?= e($order['phone']) ?></td></tr>
    <tr><th>ایمیل</th><td dir="ltr"><?= e($order['email']) ?></td><th>تاریخ ثبت</th><td><?= e($order['created_at']) ?></td></tr>
    <tr><th>نشانی</th><td colspan="3" class="wrap"><?= e($order['address']) ?></td></tr>
    <?php if (($order['note'] ?? '') !== ''): ?>
    <tr><th>یادداشت مشتری</th><td colspan="3" class="wrap"><?= e($order['note']) ?></td></tr>
    <?php endif; ?>
  </table>

  <h2>ردیف‌ها</h2>
  <table>
    <tr><th>محصول</th><th>ویژگی‌ها</th><th>قیمت واحد</th><th>تعداد</th><th>جمع</th></tr>
    <?php foreach ($items as $it): ?>
    <tr>
      <td class="wrap"><?= e($it['title']) ?></td>
      <td class="wrap"><?= e($it['options_text']) ?></td>
      <td><?= number_format((int) $it['unit_price']) ?></td>
      <td><?= (int) $it['qty'] ?></td>
      <td><?= number_format((int) $it['line_total']) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr><th colspan="4">جمع ردیف‌ها</th><td><?= number_format((int) $order['subtotal']) ?></td></tr>
    <tr><th colspan="4">ارسال</th><td><?= number_format((int) $order['shipping']) ?></td></tr>
    <tr><th colspan="4">مالیات</th><td><?= number_format((int) $order['tax']) ?></td></tr>
    <tr><th colspan="4">قابل پرداخت (<?= e($currency) ?>)</th><td><strong><?= number_format((int) $order['total']) ?></strong></td></tr>
  </table>

  <form method="post" action="/admin/shop/orders/<?= (int) $order['id'] ?>/status" style="margin-top:1rem">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <div class="row">
      <label>وضعیت سفارش
        <select name="status">
          <?php foreach ($statuses as $k => $label): ?>
            <option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>وضعیت پرداخت
        <select name="payment_status">
          <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>پرداخت نشده</option>
          <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>پرداخت شده</option>
        </select>
      </label>
    </div>
    <p>
      <button class="btn green">ذخیره وضعیت</button>
      <a class="btn gray" target="_blank" href="/admin/shop/invoice/<?= (int) $order['id'] ?>">چاپ فاکتور فروش</a>
      <a class="btn gray" href="/admin/shop/orders">بازگشت به سفارش‌ها</a>
    </p>
  </form>
  <form method="post" action="/admin/shop/orders/<?= (int) $order['id'] ?>/delete" onsubmit="return confirm('این سفارش کامل حذف شود؟')">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <button class="btn red">حذف سفارش</button>
  </form>
</div>
