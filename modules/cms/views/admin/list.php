<div class="card">
  <h1>مدیریت محتوا</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p><a class="btn green" href="/admin/cms/create">+ محتوای تازه</a></p>
  <?php if (empty($posts)): ?>
    <p>هنوز محتوایی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>عنوان</th><th>نوع</th><th>وضعیت</th><th>نامک</th><th>به‌روزرسانی</th><th>عملیات</th></tr>
    <?php foreach ($posts as $post): ?>
    <tr>
      <td><?= e($post['title']) ?></td>
      <td><?= ($post['type'] ?? 'post') === 'page' ? 'برگه' : 'نوشته' ?></td>
      <td><?= ($post['status'] ?? '') === 'published' ? 'منتشرشده' : 'پیش‌نویس' ?></td>
      <td><code dir="ltr"><?= e($post['slug']) ?></code></td>
      <td><?= e($post['updated_at']) ?></td>
      <td>
        <a class="btn yellow" href="/admin/cms/edit/<?= (int) $post['id'] ?>">ویرایش</a>
        <?php if (($post['status'] ?? '') === 'published'): ?>
          <a class="btn gray" target="_blank" href="<?= ($post['type'] ?? 'post') === 'page' ? '/page/' : '/blog/' ?><?= e(rawurlencode((string) $post['slug'])) ?>">مشاهده</a>
        <?php endif; ?>
        <form method="post" action="/admin/cms/delete/<?= (int) $post['id'] ?>" style="display:inline" onsubmit="return confirm('این محتوا حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
