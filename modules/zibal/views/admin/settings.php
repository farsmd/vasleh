<div class="card">
  <h1>تنظیمات درگاه زیبال</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>مرچنت را در پنل زیبال خودتان می‌گیرید و اینجا وارد می‌کنید. در حالت تست، مرچنت تست رسمی زیبال استفاده می‌شود و پول واقعی جابه‌جا نمی‌شود.</p>
  <p>وضعیت: <?= $ready ? 'آماده برای تسویه' : 'آماده نیست (حالت واقعی بدون مرچنت)' ?> — مرچنت مؤثر: <code dir="ltr"><?= e($effective) !== '' ? e($effective) : '—' ?></code></p>
  <form method="post" action="/admin/zibal/settings">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>مرچنت زیبال (حالت واقعی)
      <input type="text" name="merchant" dir="ltr" value="<?= e($merchant) ?>" placeholder="مثال: 1234abcd-...">
    </label>
    <label><input type="checkbox" name="test_mode" value="1" <?= $testMode ? 'checked' : '' ?>> حالت تست (مرچنت تست زیبال، بدون پول واقعی)</label>
    <p><button class="btn green">ذخیره تنظیمات</button></p>
  </form>
  <p>آدرس بازگشت درگاه (در زیبال همین آدرس ثبت می‌شود — دامنه سایت شما): <code dir="ltr">/zibal/callback</code></p>
</div>
