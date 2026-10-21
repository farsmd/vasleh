<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ورود مدیریت</title>
<style>body{font-family:Tahoma,sans-serif;background:#111827;margin:0;display:flex;min-height:100vh;align-items:center;justify-content:center}.box{background:#fff;border-radius:16px;padding:28px;width:min(360px,90vw)}input{width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin:4px 0 12px;box-sizing:border-box}button{background:#2563eb;color:#fff;border:0;padding:10px;border-radius:8px;width:100%;cursor:pointer}.error{background:#fee2e2;padding:10px;border-radius:8px;margin-bottom:12px}</style>
</head>
<body>
<div class="box">
  <h1>ورود مدیریت</h1>
  <?php if (!empty($error)): ?><div class="error">نام کاربری یا رمز اشتباه است.</div><?php endif; ?>
  <form method="post" action="/admin/login">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>نام کاربری</label>
    <input name="username" required autocomplete="username">
    <label>رمز عبور</label>
    <input name="password" type="password" required autocomplete="current-password">
    <button>ورود</button>
  </form>
</div>
</body>
</html>
