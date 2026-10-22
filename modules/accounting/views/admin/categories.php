<div class="card">
  <h1>دسته‌های درآمد و هزینه</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/accounting/categories/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
      <label>عنوان دسته
        <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
      </label>
      <label>نوع
        <select name="kind">
          <option value="expense" <?= ($edit['kind'] ?? 'expense') === 'expense' ? 'selected' : '' ?>>هزینه</option>
          <option value="income" <?= ($edit['kind'] ?? '') === 'income' ? 'selected' : '' ?>>درآمد</option>
        </select>
      </label>
      <label>ترتیب
        <input type="number" name="sort" value="<?= (int) ($edit['sort'] ?? 0) ?>">
      </label>
    </div>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت دسته' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/accounting/categories">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<div class="card">
  <table>
    <tr><th>عنوان</th><th>نوع</th><th>ترتیب</th><th>عملیات</th></tr>
    <?php foreach ($categories as $c): ?>
    <tr>
      <td><?= e($c['title']) ?></td>
      <td><?= $c['kind'] === 'income' ? 'درآمد' : 'هزینه' ?></td>
      <td><?= (int) $c['sort'] ?></td>
      <td>
        <a class="btn yellow" href="/admin/accounting/categories?edit=<?= (int) $c['id'] ?>">ویرایش</a>
        <form method="post" action="/admin/accounting/categories/delete/<?= (int) $c['id'] ?>" style="display:inline" onsubmit="return confirm('این دسته حذف شود؟ اسنادش بدون دسته می‌شوند.')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
