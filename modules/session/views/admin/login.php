<?php
$assetBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ورود مدیریت — وصله</title>
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/bootstrap/bootstrap.rtl.min.css">
<style>
body{background:#1d2327;font-family:Tahoma,"Segoe UI",sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}
.login-card{background:#fff;border-radius:14px;padding:28px;width:100%;max-width:380px;box-shadow:0 10px 30px rgba(0,0,0,.35)}
.brand{text-align:center;color:#fff;margin-bottom:16px}
.brand strong{font-size:1.6rem;display:block}
.brand small{color:#9ba0a3}
.error{background:#fee2e2;padding:10px 14px;border-radius:8px;margin-bottom:12px}
</style>
</head>
<body>
<div class="w-100" style="max-width:380px">
  <div class="brand"><strong>وصله</strong><small>ورود مدیریت</small></div>
  <div class="login-card mx-auto">
    <?php if (!empty($error)): ?><div class="error">نام کاربری یا رمز اشتباه است.</div><?php endif; ?>
    <form method="post" action="<?= e($assetBase) ?>/admin/login">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <label class="form-label">نام کاربری</label>
      <input class="form-control mb-3" name="username" required autocomplete="username" autofocus>
      <label class="form-label">رمز عبور</label>
      <input class="form-control mb-3" name="password" type="password" required autocomplete="current-password">
      <button class="btn btn-primary w-100">ورود</button>
    </form>
  </div>
</div>
</body>
</html>
