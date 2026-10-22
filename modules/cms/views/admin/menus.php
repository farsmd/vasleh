<div class="card">
  <h1>منوهای سایت</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>منوها را بسازید و آیتم‌هایشان را مدیریت کنید؛ بعد در هدر/فوتر/قالب با <code dir="ltr">{{menu:نامک}}</code> نمایششان دهید.</p>
  <?php if (empty($menus)): ?>
    <p>هنوز منویی نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>نام</th><th>نامک</th><th>آیتم‌ها</th><th>عملیات</th></tr>
    <?php foreach ($menus as $menu): ?>
    <tr>
      <td><?= e($menu['name']) ?></td>
      <td><code dir="ltr"><?= e($menu['slug']) ?></code></td>
      <td><?= (int) ($menu['count'] ?? 0) ?></td>
      <td>
        <a class="btn yellow" href="/admin/cms/menus/<?= (int) $menu['id'] ?>">مدیریت آیتم‌ها</a>
        <form method="post" action="/admin/cms/menus/delete/<?= (int) $menu['id'] ?>" style="display:inline" onsubmit="return confirm('این منو با همه آیتم‌هایش حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف منو</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<div class="card">
  <h3>منوی تازه</h3>
  <form method="post" action="/admin/cms/menus/create">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>نام منو</label>
    <input name="name" required placeholder="منوی فوتر">
    <label>نامک (لاتین) — خالی بماند از نام ساخته می‌شود</label>
    <input name="slug" dir="ltr" placeholder="footer">
    <button class="btn green">ساخت منو</button>
  </form>
</div>
