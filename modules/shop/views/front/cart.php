<h1>سبد خرید</h1>
<?php if (empty($items)): ?>
  <p>سبد خرید خالی است.</p>
  <p><a class="btn" href="/shop">رفتن به فروشگاه</a></p>
<?php else: ?>
<table>
  <tr><th>محصول</th><th>قیمت واحد</th><th>تعداد</th><th>جمع</th><th></th></tr>
  <?php foreach ($items as $row): ?>
  <tr>
    <td>
      <a href="/shop/product/<?= e(rawurlencode((string) $row['product']['slug'])) ?>"><?= e($row['product']['title']) ?></a>
      <?php if ($row['options_text'] !== ''): ?><div class="muted"><?= e($row['options_text']) ?></div><?php endif; ?>
      <?php if ($row['stock'] !== null): ?><div class="muted">موجودی: <?= (int) $row['stock'] ?></div><?php endif; ?>
    </td>
    <td><?= number_format($row['unit_price']) ?> <?= e($currency) ?></td>
    <td>
      <form method="post" action="/shop/cart/update" style="display:flex;gap:.3rem">
        <input type="hidden" name="key" value="<?= e($row['key']) ?>">
        <input type="number" name="qty" value="<?= (int) $row['qty'] ?>" min="0" style="width:5rem">
        <button class="btn light" type="submit">به‌روزرسانی</button>
      </form>
    </td>
    <td><?= number_format($row['line_total']) ?> <?= e($currency) ?></td>
    <td>
      <form method="post" action="/shop/cart/remove">
        <input type="hidden" name="key" value="<?= e($row['key']) ?>">
        <button class="btn red" type="submit">حذف</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
  <tr><th colspan="3">جمع ردیف‌ها</th><td colspan="2"><?= number_format($subtotal) ?> <?= e($currency) ?></td></tr>
  <tr><th colspan="3">هزینه ارسال</th><td colspan="2"><?= $shipping > 0 ? number_format($shipping) . ' ' . e($currency) : 'رایگان' ?></td></tr>
  <?php if ($tax > 0): ?>
  <tr><th colspan="3">مالیات (<?= (int) $taxPercent ?>٪)</th><td colspan="2"><?= number_format($tax) ?> <?= e($currency) ?></td></tr>
  <?php endif; ?>
  <tr><th colspan="3">قابل پرداخت</th><td colspan="2"><strong><?= number_format($total) ?> <?= e($currency) ?></strong></td></tr>
</table>
<p><a class="btn" href="/shop/checkout">ثبت سفارش و تسویه</a> <a class="btn light" href="/shop">ادامه خرید</a></p>
<?php endif; ?>
