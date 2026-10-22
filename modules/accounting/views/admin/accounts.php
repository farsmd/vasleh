<div class="card">
  <h1>حساب‌های پولی</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/accounting/accounts/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
      <label>عنوان حساب
        <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
      </label>
      <label>نوع
        <select name="type">
          <?php foreach ($types as $k => $label): ?>
            <option value="<?= $k ?>" <?= ($edit['type'] ?? 'bank') === $k ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>موجودی اول دوره
        <input type="number" name="opening_balance" value="<?= (int) ($edit['opening_balance'] ?? 0) ?>">
      </label>
      <label>یادداشت (مثلاً شماره حساب)
        <input type="text" name="note" value="<?= e($edit['note'] ?? '') ?>">
      </label>
    </div>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="active" value="1" <?= !isset($edit) || (int) ($edit['active'] ?? 1) === 1 ? 'checked' : '' ?>> فعال
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت حساب' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/accounting/accounts">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<div class="card">
  <table>
    <tr><th>حساب</th><th>نوع</th><th>اول دوره</th><th>مانده فعلی</th><th>فعال</th><th>عملیات</th></tr>
    <?php foreach ($accounts as $a): ?>
    <tr>
      <td><?= e($a['title']) ?></td>
      <td><?= e($types[$a['type']] ?? $a['type']) ?></td>
      <td><?= number_format((int) $a['opening_balance']) ?></td>
      <td><strong><?= number_format((int) $a['balance']) ?></strong> <?= e($currency) ?></td>
      <td><?= (int) $a['active'] === 1 ? 'بله' : 'خیر' ?></td>
      <td>
        <a class="btn yellow" href="/admin/accounting/accounts?edit=<?= (int) $a['id'] ?>">ویرایش</a>
        <form method="post" action="/admin/accounting/accounts/delete/<?= (int) $a['id'] ?>" style="display:inline" onsubmit="return confirm('این حساب حذف شود؟ (فقط حسابی که سند ندارد)')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
