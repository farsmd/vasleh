<h1>حساب کاربری</h1>
<table>
  <tr><th>نام کاربری</th><td><?= e($user['username'] ?? '') ?></td></tr>
  <tr><th>نقش</th><td><?= e(($user['role'] ?? '') === 'owner' ? 'مالک' : 'مدیر') ?></td></tr>
  <?php if (!empty($user['created_at'])): ?>
  <tr><th>تاریخ ساخت</th><td><?= e($user['created_at']) ?></td></tr>
  <?php endif; ?>
</table>
<p>
  <a href="/account/password">تغییر کلمه عبور</a> ·
  <a href="/admin">رفتن به بخش مدیریت</a>
</p>
<form method="post" action="/admin/logout">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <button type="submit">خروج</button>
</form>
