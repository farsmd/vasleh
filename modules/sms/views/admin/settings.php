<div class="card">
  <h1>تنظیمات پیامک</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>اتصال به سامانه SMS.ir با همان API پکیج smsir-php است؛ کلید وب‌سرویس را از پنل SMS.ir (بخش برنامه‌نویسان) می‌گیرید. کلید در دیتابیس خودتان ذخیره می‌شود و هیچ‌جا نمایش داده نمی‌شود.</p>
  <?php if ($credit !== null): ?><p>اعتبار فعلی: <strong dir="ltr"><?= e($credit) ?></strong></p><?php endif; ?>
  <?php if ($creditError !== null): ?><p>اعتبار: <?= e($creditError) ?></p><?php endif; ?>
  <form method="post" action="/admin/sms/settings">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>کلید وب‌سرویس (API Key) — خالی بماند یعنی بدون تغییر
      <input type="password" name="api_key" dir="ltr" value="" placeholder="••••••••" autocomplete="off">
    </label>
    <label>شماره خط فرستنده
      <input type="text" name="line_number" dir="ltr" value="<?= e($settings->lineNumber()) ?>" placeholder="مثلاً 3000123456">
    </label>
    <label>شناسه قالب کد تأیید (برای ورود با پیامک؛ از پنل SMS.ir)
      <input type="text" name="verify_template_id" dir="ltr" value="<?= $settings->verifyTemplateId() > 0 ? e((string) $settings->verifyTemplateId()) : '' ?>" placeholder="مثلاً 100000">
    </label>
    <label>شناسه قالب پیامک ثبت سفارش (خالی یعنی متن ساده با خط؛ از پنل SMS.ir)
      <input type="text" name="order_template_id" dir="ltr" value="<?= $settings->orderTemplateId() > 0 ? e((string) $settings->orderTemplateId()) : '' ?>" placeholder="مثلاً 220317">
    </label>
    <label>نام پارامتر کد سفارش در قالب (پیش‌فرض Code)
      <input type="text" name="order_template_param" dir="ltr" value="<?= e($settings->orderTemplateParam()) ?>" placeholder="Code">
    </label>
    <label>متن پیامک ثبت سفارش (توکن‌های {{code}} و {{status}} — جایگزین ارسال قالبی)
      <input type="text" name="order_placed_text" value="<?= e($settings->orderPlacedText()) ?>">
    </label>
    <label>متن پیامک تغییر وضعیت سفارش (توکن‌های {{code}} و {{status}})
      <input type="text" name="order_status_text" value="<?= e($settings->orderStatusText()) ?>">
    </label>
    <p><button class="btn green">ذخیره تنظیمات</button></p>
  </form>
  <p>وضعیت: <?= $settings->ready() ? 'آماده ارسال متن' : 'ناقص (کلید و خط لازم است)' ?> — <?= $settings->otpReady() ? 'ورود با پیامک فعال است' : 'ورود با پیامک نیازمند شناسه قالب است' ?>.</p>
</div>
