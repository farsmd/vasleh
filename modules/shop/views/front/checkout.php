<h1>ثبت سفارش</h1>
<?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<?php if (($orderNote ?? '') !== ''): ?><div class="flash"><?= e($orderNote) ?></div><?php endif; ?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(18rem,1fr));gap:1rem;align-items:start">
  <form method="post" action="/shop/checkout" style="background:var(--admin-panel-bg);border:1px solid var(--admin-border);border-radius:var(--admin-radius);padding:1rem">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>نام و نام خانوادگی *
      <input type="text" name="name" required value="<?= e($old['name'] ?? '') ?>">
    </label>
    <label>شماره موبایل *
      <input type="tel" name="phone" required dir="ltr" value="<?= e($old['phone'] ?? '') ?>">
    </label>
    <label>ایمیل (اختیاری)
      <input type="email" name="email" dir="ltr" value="<?= e($old['email'] ?? '') ?>">
    </label>
    <label>نشانی ارسال
      <textarea name="address" rows="3"><?= e($old['address'] ?? '') ?></textarea>
    </label>
    <label>یادداشت (اختیاری)
      <textarea name="note" rows="2"><?= e($old['note'] ?? '') ?></textarea>
    </label>
    <button class="btn" type="submit">ثبت نهایی سفارش</button>
  </form>
  <div style="background:var(--admin-panel-bg);border:1px solid var(--admin-border);border-radius:var(--admin-radius);padding:1rem">
    <h3 style="margin-top:0">خلاصه سفارش</h3>
    <table>
      <?php foreach ($items as $row): ?>
      <tr>
        <td><?= e($row['product']['title']) ?><?php if ($row['options_text'] !== ''): ?> <span class="muted">(<?= e($row['options_text']) ?>)</span><?php endif; ?> × <?= (int) $row['qty'] ?></td>
        <td><?= number_format($row['line_total']) ?></td>
      </tr>
      <?php endforeach; ?>
      <tr><th>جمع</th><td><?= number_format($subtotal) ?> <?= e($currency) ?></td></tr>
      <tr><th>ارسال</th><td><?= $shipping > 0 ? number_format($shipping) . ' ' . e($currency) : 'رایگان' ?></td></tr>
      <?php if ($tax > 0): ?><tr><th>مالیات</th><td><?= number_format($tax) ?> <?= e($currency) ?></td></tr><?php endif; ?>
      <tr><th>قابل پرداخت</th><td><strong><?= number_format($total) ?> <?= e($currency) ?></strong></td></tr>
    </table>
  </div>
</div>
