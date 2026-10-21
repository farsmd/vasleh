<div class="card">
  <h1>کاربران مدیریت</h1>
  <?php if (!empty($error)): ?><div class="error">ساخت کاربر نشد — نام تکراری، رمز کوتاه‌تر از ۸ رقم یا نقش نامعتبر.</div><?php endif; ?>
  <table>
    <tr><th>نام کاربری</th><th>نقش</th><th>ساخته‌شده</th></tr>
    <?php foreach ($users as $u): ?>
    <tr><td><?= e($u['username']) ?></td><td><?= e($u['role'] === 'owner' ? 'مالک' : 'مدیر') ?></td><td><?= e($u['created_at']) ?></td></tr>
    <?php endforeach; ?>
  </table>
  <h3>کاربر تازه</h3>
  <form method="post" action="/admin/users">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input name="username" required placeholder="نام کاربری">
    <input name="password" type="password" required placeholder="رمز (حداقل ۸ کاراکتر)">
    <select name="role"><option value="admin">مدیر</option><option value="owner">مالک</option></select>
    <button class="btn green">ساخت کاربر</button>
  </form>
</div>
<div class="card">
  <h2>دسترسی‌های اعلام‌شده از ماژول‌ها</h2>
  <p>هر ماژول دسترسی‌های خودش را در core.php اعلام می‌کند؛ این فهرست از همه ماژول‌ها جمع شده است.</p>
  <ul>
    <?php foreach ($permissions as $key => $label): ?>
    <li><code><?= e($key) ?></code> — <?= e($label) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
