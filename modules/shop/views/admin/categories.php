<?php $titles = []; foreach ($categories as $c) { $titles[(int) $c['id']] = $c['title']; } ?>
<div class="card">
  <h1>دسته‌بندی‌های فروشگاه</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>

  <form method="post" action="/admin/shop/categories/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <div class="row">
      <label>عنوان دسته
        <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
      </label>
      <label>نامک (خالی = ساخت خودکار)
        <input type="text" name="slug" dir="ltr" value="<?= e($edit['slug'] ?? '') ?>">
      </label>
      <label>دسته والد
        <select name="parent_id">
          <option value="0">— بدون والد (اصلی) —</option>
          <?php foreach ($categories as $c): if ((int) $c['id'] === (int) ($edit['id'] ?? 0)) continue; ?>
            <option value="<?= (int) $c['id'] ?>" <?= (int) ($edit['parent_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>ترتیب
        <input type="number" name="sort" value="<?= (int) ($edit['sort'] ?? 0) ?>">
      </label>
    </div>
    <label>توضیح
      <input type="text" name="description" value="<?= e($edit['description'] ?? '') ?>">
    </label>
    <p>
      <button class="btn green"><?= !empty($edit) ? 'ذخیره ویرایش' : 'ساخت دسته' ?></button>
      <?php if (!empty($edit)): ?><a class="btn gray" href="/admin/shop/categories">انصراف</a><?php endif; ?>
    </p>
  </form>
</div>

<div class="card">
  <?php if (empty($categories)): ?>
    <p>هنوز دسته‌ای نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>عنوان</th><th>نامک</th><th>والد</th><th>محصولات</th><th>ترتیب</th><th>عملیات</th></tr>
    <?php foreach ($categories as $i => $c): ?>
    <tr>
      <td><?= e($c['title']) ?></td>
      <td><code dir="ltr"><?= e($c['slug']) ?></code></td>
      <td><?= e($titles[(int) $c['parent_id']] ?? '—') ?></td>
      <td><?= (int) ($counts[$i] ?? 0) ?></td>
      <td><?= (int) $c['sort'] ?></td>
      <td>
        <a class="btn yellow" href="/admin/shop/categories?edit=<?= (int) $c['id'] ?>">ویرایش</a>
        <form method="post" action="/admin/shop/categories/delete/<?= (int) $c['id'] ?>" style="display:inline" onsubmit="return confirm('این دسته حذف شود؟ محصولاتش بدون دسته می‌شوند.')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
