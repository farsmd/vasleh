<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | اشتراک عمومی</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,Tahoma,sans-serif;background:#f4f5f7;color:#17233b;line-height:1.8}
.wrap{max-width:760px;margin:0 auto;padding:16px}
.card{background:#fff;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.muted{color:#667085;font-size:13px}
.pill{display:inline-block;background:#ecfdf5;color:#15803d;border-radius:999px;padding:2px 10px;font-size:12px}
.need{display:flex;gap:10px;align-items:center;padding:9px 0;border-bottom:1px solid #eef0f3}
.need .done{color:#98a2b3;text-decoration:line-through}
</style>
</head>
<body>
<div class="wrap">
  <p class="muted">مشاهده فقط‌خواندنی از طریق لینک اشتراک عمومی</p>
  <?= $content ?>
</div>
</body>
</html>
