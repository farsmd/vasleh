<div class="card">
  <h1>کوپن‌های تخفیف</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/shop/coupons/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="0">
    <div class="row">
      <label>کد کوپن
        <input type="text" name="code" required dir="ltr" placeholder="مثلاً OFF20">
      </label>
      <label>نوع
        <select name="kind">
          <option value="percent">درصدی</option>
          <option value="fixed">مبلغ ثابت (تومان)</option>
        </select>
      </label>
      <label>مقدار
        <input type="number" name="value" min="0" value="20">
      </label>
    </div>
    <div class="row">
      <label>انقضا (میلادی YYYY-MM-DD — خالی = بدون انقضا)
        <input type="text" name="expires_at" dir="ltr" placeholder="2026-12-31">
      </label>
      <label>سقف مصرف (۰ = نامحدود)
        <input type="number" name="max_uses" min="0" value="0">
      </label>
      <label><input type="checkbox" name="active" value="1" checked> فعال</label>
    </div>
    <p><button class="btn green">ثبت کوپن تازه</button></p>
  </form>

  <?php if (!empty($coupons)): ?>
  <table>
    <tr><th>کد</th><th>نوع</th><th>مقدار</th><th>انقضا</th><th>مصرف</th><th>وضعیت</th><th></th></tr>
    <?php foreach ($coupons as $cp): ?>
    <tr>
      <td><code dir="ltr"><?= e($cp['code']) ?></code></td>
      <td><?= $cp['kind'] === 'fixed' ? 'مبلغ ثابت' : 'درصدی' ?></td>
      <td><?= $cp['kind'] === 'fixed' ? number_format((int) $cp['value']) : (int) $cp['value'] . '٪' ?></td>
      <td dir="ltr"><?= e($cp['expires_at']) !== '' ? e($cp['expires_at']) : '—' ?></td>
      <td><?= (int) $cp['used'] ?><?= (int) $cp['max_uses'] > 0 ? ' / ' . (int) $cp['max_uses'] : '' ?></td>
      <td><?= (int) $cp['active'] === 1 ? 'فعال' : 'غیرفعال' ?></td>
      <td>
        <form method="post" action="/admin/shop/coupons/delete/<?= (int) $cp['id'] ?>" onsubmit="return confirm('این کوپن حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?>
  <p>هنوز کوپنی ثبت نشده است.</p>
  <?php endif; ?>
</div>
