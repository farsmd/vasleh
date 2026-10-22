<h1>دسترسی API</h1>
<p>با توکن API، ابزارهای بیرونی (و خودکارسازی) بدون سشن به فایل‌های برنامه و دیتابیس دسترسی دارند.
توکن را مثل رمز نگه دارید؛ متن هر توکن <strong>فقط هنگام ساخت، یک بار</strong> نمایش داده می‌شود و در دیتابیس فقط هش آن می‌ماند.</p>

<?php if (!empty($newToken)): ?>
<div class="flash">
  توکن تازه ساخته شد — همین حالا کپی‌اش کنید، دوباره نمایش داده نمی‌شود:<br>
  <code style="user-select:all"><?= e($newToken) ?></code>
</div>
<?php endif; ?>

<div class="card">
  <h3>ساخت توکن تازه</h3>
  <form method="post" action="/admin/api/tokens">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>نام توکن (مثلاً: ابزار استقرار)
      <input type="text" name="name" required>
    </label>
    <button>ساخت توکن</button>
  </form>
</div>

<div class="card">
  <h3>توکن‌ها</h3>
  <?php if (empty($tokens)): ?>
    <p>هنوز توکنی ساخته نشده است.</p>
  <?php else: ?>
  <table>
    <tr><th>نام</th><th>ساخته‌شده</th><th>آخرین استفاده</th><th>وضعیت</th><th></th></tr>
    <?php foreach ($tokens as $t): ?>
    <tr>
      <td><?= e($t['name']) ?></td>
      <td><?= e($t['created_at']) ?></td>
      <td><?= e($t['last_used_at'] ?? '—') ?></td>
      <td><?= ((int) $t['revoked'] === 1) ? 'لغوشده' : 'فعال' ?></td>
      <td>
        <?php if ((int) $t['revoked'] === 0): ?>
        <form method="post" action="/admin/api/tokens/revoke/<?= (int) $t['id'] ?>" onsubmit="return confirm('این توکن لغو شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn gray">لغو</button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>

<div class="card">
  <h3>راهنمای endpointها</h3>
  <p>آدرس پایه: <code><?= e($baseUrl) ?></code> — هدر احراز روی همه درخواست‌ها:
  <code>Authorization: Bearer &lt;token&gt;</code></p>
  <table>
    <tr><th>متد</th><th>مسیر</th><th>کار</th></tr>
    <tr><td>GET</td><td><code>/api/ping</code></td><td>سلامت و فهرست ماژول‌ها</td></tr>
    <tr><td>GET</td><td><code>/api/files?path=modules</code></td><td>فهرست فایل‌های یک مسیر (نسبت به ریشه)</td></tr>
    <tr><td>GET</td><td><code>/api/file?path=index.php</code></td><td>خواندن فایل (متنی یا base64)</td></tr>
    <tr><td>POST</td><td><code>/api/file</code></td><td>نوشتن فایل — بدنه JSON: <code>{"path":"...","content":"..."}</code></td></tr>
    <tr><td>DELETE</td><td><code>/api/file?path=...</code></td><td>حذف فایل (یا پوشه خالی)</td></tr>
    <tr><td>GET</td><td><code>/api/db/tables</code></td><td>جدول‌های دیتابیس و اسکیما</td></tr>
    <tr><td>POST</td><td><code>/api/db/query</code></td><td>کوئری — بدنه JSON: <code>{"sql":"SELECT ...","params":[]}</code></td></tr>
  </table>
  <p>نمونه:</p>
  <pre style="direction:ltr;text-align:left;background:#0f172a;color:#e2e8f0;padding:12px;border-radius:8px;overflow:auto">curl -H "Authorization: Bearer &lt;token&gt;" <?= e($baseUrl) ?>/api/ping</pre>
  <p>نکته امنیتی: فایل‌های <code>storage/app.key</code> و <code>storage/database.sqlite</code> از راه فایل‌ها در دسترس نیستند؛ دیتابیس فقط از راه کوئری خوانده/نوشته می‌شود.
  برای بستن کامل API، ماژول «دسترسی API» را از «مدیریت ماژول‌ها» غیرفعال کنید.</p>
</div>
