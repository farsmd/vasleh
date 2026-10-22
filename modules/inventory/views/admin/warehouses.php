<div class="card">
  <h1>انبارها</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/inventory/warehouses/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
      <label>عنوان انبار
        <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
      </label>
      <label>موقعیت
        <input type="text" name="location" value="<?= e($edit['location'] ?? '') ?>">
      </label>
      <label>ترتیب
        <input type="number" name="sort" value="<?= (int) ($edit['sort'] ?? 0) ?>">
      </label>
    </div>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="is_default" value="1" <?= (int) ($edit['is_default'] ?? 0) === 1 ? 'checked' : '' ?>> انبار پیش‌فرض
    </label>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="active" value="1" <?= !isset($edit) || (int) ($edit['active'] ?? 1) === 1 ? 'checked' : '' ?>> فعال
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت انبار' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/inventory/warehouses">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<div class="card">
  <?php if (empty($warehouses)): ?>
    <p>هنوز انباری نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>عنوان</th><th>موقعیت</th><th>پیش‌فرض</th><th>فعال</th><th>ترتیب</th><th>عملیات</th></tr>
    <?php foreach ($warehouses as $w): ?>
    <tr>
      <td><?= e($w['title']) ?></td>
      <td><?= e($w['location']) ?></td>
      <td><?= (int) $w['is_default'] === 1 ? 'بله' : '—' ?></td>
      <td><?= (int) $w['active'] === 1 ? 'بله' : 'خیر' ?></td>
      <td><?= (int) $w['sort'] ?></td>
      <td>
        <a class="btn yellow" href="/admin/inventory/warehouses?edit=<?= (int) $w['id'] ?>">ویرایش</a>
        <form method="post" action="/admin/inventory/warehouses/delete/<?= (int) $w['id'] ?>" style="display:inline" onsubmit="return confirm('این انبار حذف شود؟ (فقط وقتی موجودیش صفر است)')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
