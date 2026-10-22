<h1>تغییر کلمه عبور</h1>
<?php
$messages = [
    'wrong_current' => 'کلمه عبور فعلی اشتباه است.',
    'weak'          => 'کلمه عبور تازه باید دست‌کم ۸ کاراکتر باشد.',
    'mismatch'      => 'تکرار کلمه عبور با کلمه عبور تازه یکی نیست.',
    'same'          => 'کلمه عبور تازه نباید همان قبلی باشد.',
];
?>
<?php if (!empty($ok)): ?>
  <p style="background:#dcfce7;padding:10px 14px;border-radius:8px">کلمه عبور با موفقیت تغییر کرد.</p>
<?php endif; ?>
<?php if (!empty($error) && isset($messages[$error])): ?>
  <p style="background:#fee2e2;padding:10px 14px;border-radius:8px"><?= e($messages[$error]) ?></p>
<?php endif; ?>
<form method="post" action="/account/password">
  <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
  <label>کلمه عبور فعلی
    <input type="password" name="current_password" required autocomplete="current-password"
           style="width:100%;padding:9px;border:1px solid #d1d5db;border-radius:8px;margin:4px 0 12px;box-sizing:border-box">
  </label>
  <label>کلمه عبور تازه (دست‌کم ۸ کاراکتر)
    <input type="password" name="new_password" required minlength="8" autocomplete="new-password"
           style="width:100%;padding:9px;border:1px solid #d1d5db;border-radius:8px;margin:4px 0 12px;box-sizing:border-box">
  </label>
  <label>تکرار کلمه عبور تازه
    <input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"
           style="width:100%;padding:9px;border:1px solid #d1d5db;border-radius:8px;margin:4px 0 12px;box-sizing:border-box">
  </label>
  <button type="submit" style="background:#2563eb;color:#fff;border:0;padding:8px 16px;border-radius:8px;cursor:pointer">ثبت کلمه عبور تازه</button>
  <a href="/account">بازگشت به حساب کاربری</a>
</form>
