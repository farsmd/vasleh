<div class="card">
  <h1>مدیریت ماژول‌ها</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <a class="btn green" href="/admin/modules/create">+ ساخت ماژول تازه</a>
  <table>
    <tr><th>ماژول</th><th>نامک</th><th>نسخه</th><th>وضعیت</th><th>عملیات</th></tr>
    <?php foreach ($modules as $slug => $m): ?>
    <tr>
      <td><?= e($m['title']) ?><?= !empty($m['system']) ? ' <small>(سیستمی)</small>' : '' ?><br><small><?= e($m['description'] ?? '') ?></small><?php if (!empty($m['error'])): ?><br><small style="color:#b91c1c"><?= e($m['error']) ?></small><?php endif; ?></td>
      <td><code><?= e($slug) ?></code></td>
      <td><?= e($m['version']) ?></td>
      <td><?= $m['enabled'] ? 'فعال' : 'غیرفعال' ?></td>
      <td>
        <a class="btn yellow" href="/admin/modules/edit/<?= e($slug) ?>">ویرایش</a>
        <?php if (empty($m['system'])): ?>
        <form method="post" action="/admin/modules/toggle/<?= e($slug) ?>" style="display:inline">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn gray"><?= $m['enabled'] ? 'غیرفعال' : 'فعال' ?></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
