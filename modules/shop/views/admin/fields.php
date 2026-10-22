<?php $typeLabels = ['text' => 'متن', 'number' => 'عدد', 'select' => 'انتخابی', 'textarea' => 'متن بلند', 'checkbox' => 'تیک‌زدنی']; ?>
<div class="card">
  <h1>فیلدهای محصول</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p class="muted">هر فیلدی که اینجا بسازی، در فرم همه محصولات ظاهر می‌شود و مقدارش در صفحه همان محصول نمایش داده می‌شود؛ مثلاً «جنس»، «گارانتی»، «کشور سازنده» و…</p>
  <form method="post" action="/admin/shop/fields/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
      <label>عنوان فیلد
        <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
      </label>
      <label>کلید (خالی = ساخت خودکار)
        <input type="text" name="fkey" dir="ltr" value="<?= e($edit['fkey'] ?? '') ?>">
      </label>
      <label>نوع
        <select name="type">
          <?php foreach ($typeLabels as $k => $label): ?>
            <option value="<?= $k ?>" <?= ($edit['type'] ?? 'text') === $k ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>ترتیب
        <input type="number" name="sort" value="<?= (int) ($edit['sort'] ?? 0) ?>">
      </label>
    </div>
    <label>گزینه‌ها (فقط برای نوع «انتخابی» — هر گزینه در یک خط)
      <textarea name="options" rows="3" dir="auto"><?= e($edit['options'] ?? '') ?></textarea>
    </label>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="required" value="1" <?= (int) ($edit['required'] ?? 0) === 1 ? 'checked' : '' ?>> الزامی در فرم محصول
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت فیلد' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/shop/fields">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<div class="card">
  <?php if (empty($fields)): ?>
    <p>هنوز فیلدی تعریف نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>عنوان</th><th>کلید</th><th>نوع</th><th>الزامی</th><th>ترتیب</th><th>عملیات</th></tr>
    <?php foreach ($fields as $f): ?>
    <tr>
      <td><?= e($f['title']) ?></td>
      <td><code dir="ltr"><?= e($f['fkey']) ?></code></td>
      <td><?= e($typeLabels[$f['type']] ?? $f['type']) ?></td>
      <td><?= (int) $f['required'] === 1 ? 'بله' : '—' ?></td>
      <td><?= (int) $f['sort'] ?></td>
      <td>
        <a class="btn yellow" href="/admin/shop/fields?edit=<?= (int) $f['id'] ?>">ویرایش</a>
        <form method="post" action="/admin/shop/fields/delete/<?= (int) $f['id'] ?>" style="display:inline" onsubmit="return confirm('این فیلد و مقدارهایش در همه محصولات حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
