<div class="card">
  <h1>تلگرام</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>ربات و مینی‌اپ فروشگاه وصله. اول در تلگرام از @BotFather یک ربات بسازید (نام نمایشی ربات را «وصله» بگذارید)، توکنش را اینجا ذخیره کنید، بعد با دکمهٔ پایین وبهوک را فعال کنید. توکن و راز وبهوک در دیتابیس خودتان ذخیره می‌شوند و هیچ‌جا نمایش داده نمی‌شوند.</p>

  <h2>وضعیت اتصال</h2>
  <?php if ($settings->token() === ''): ?>
    <p>پیکربندی نشده — هنوز توکن رباتی ثبت نشده است.</p>
  <?php else: ?>
    <?php if ($me !== null): ?>
      <p>ربات متصل است: <strong dir="ltr">@<?= e((string) ($me['username'] ?? '')) ?></strong> (<?= e((string) ($me['first_name'] ?? '')) ?>)</p>
    <?php else: ?>
      <p>وضعیت ربات: نامشخص/خطا<?= $meError !== null ? ' (' . e($meError) . ')' : '' ?></p>
    <?php endif; ?>
    <?php if ($hook !== null): ?>
      <p>وبهوک فعلی: <span dir="ltr"><?= e((string) ($hook['url'] ?? '')) !== '' ? e((string) $hook['url']) : '—' ?></span> — به‌روزرسانی‌های در انتظار: <?= e((string) ($hook['pending_update_count'] ?? 0)) ?></p>
    <?php elseif ($hookError !== null): ?>
      <p>وضعیت وبهوک: نامشخص/خطا (<?= e($hookError) ?>)</p>
    <?php endif; ?>
  <?php endif; ?>

  <form method="post" action="/admin/telegram/settings">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>توکن ربات (از @BotFather) — خالی بماند یعنی بدون تغییر
      <input type="password" name="bot_token" dir="ltr" value="" placeholder="••••••••" autocomplete="off">
    </label>
    <label>نام کاربری ربات (بدون @)
      <input type="text" name="bot_username" dir="ltr" value="<?= e($settings->username()) ?>" placeholder="مثلا vasleh_bot">
    </label>
    <label>متن خوش‌آمد (پیام /start)
      <textarea name="welcome_text" rows="3"><?= e($settings->welcomeText()) ?></textarea>
    </label>
    <p><button class="btn green">ذخیره تنظیمات</button></p>
  </form>

  <h2>وبهوک</h2>
  <p>آدرس وبهوک: <span dir="ltr"><?= e($webhookUrl) ?></span></p>
  <form method="post" action="/admin/telegram/webhook">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <p><button class="btn green">فعال‌سازی وبهوک</button></p>
  </form>
  <p>پس از فعال‌سازی، در تلگرام به ربات /start بدهید؛ باید متن خوش‌آمد با دکمهٔ «باز کردن فروشگاه» بیاید و مینی‌اپ باز شود.</p>
</div>
