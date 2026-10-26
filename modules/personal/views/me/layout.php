<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | مدیریت شخصی</title>
<style>
*{box-sizing:border-box}
body{margin:0;font-family:system-ui,Tahoma,sans-serif;background:#f4f5f7;color:#17233b;line-height:1.8}
a{color:#1d4ed8;text-decoration:none}
.top{position:sticky;top:0;z-index:10;background:#0b1f3a;color:#fff;padding:12px 16px;display:flex;justify-content:space-between;align-items:center}
.top b{font-size:17px}
.top small{opacity:.8}
.tabs{display:flex;gap:8px;overflow-x:auto;padding:10px 16px;background:#fff;border-bottom:1px solid #e2e6ee;position:sticky;top:52px;z-index:9}
.tabs a{white-space:nowrap;padding:6px 14px;border-radius:999px;background:#eef2ff;color:#1d4ed8;font-size:14px}
.tabs a.on{background:#1d4ed8;color:#fff}
.wrap{max-width:760px;margin:0 auto;padding:16px}
.card{background:#fff;border-radius:14px;padding:16px;margin-bottom:12px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.card h2{margin:0 0 8px;font-size:17px}
.card h3{margin:12px 0 6px;font-size:15px}
.muted{color:#667085;font-size:13px}
.flash{background:#dcfce7;border:1px solid #86efac;color:#166534;border-radius:10px;padding:10px 14px;margin-bottom:12px}
.error{background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;border-radius:10px;padding:10px 14px;margin-bottom:12px}
label{display:block;font-size:14px;margin:10px 0 4px}
input[type=text],input[type=date],input[type=time],input[type=number],textarea,select{width:100%;font-size:16px;font-family:inherit;padding:11px 12px;border:1px solid #d0d5dd;border-radius:10px;background:#fff}
textarea{min-height:120px}
.row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.btn{display:inline-block;border:0;cursor:pointer;font-family:inherit;font-size:15px;padding:11px 18px;border-radius:10px;background:#1d4ed8;color:#fff;min-height:44px}
.btn.green{background:#15803d}
.btn.gray{background:#e4e7ec;color:#17233b}
.btn.red{background:#dc2626}
.btn.small{padding:7px 12px;font-size:13px;min-height:36px}
form.inline{display:inline}
.todo{display:flex;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid #eef0f3}
.todo:last-child{border-bottom:0}
.todo input[type=checkbox]{width:22px;height:22px;flex:none}
.todo .done{color:#98a2b3;text-decoration:line-through}
.hour{display:flex;gap:12px;padding:8px 0;border-bottom:1px solid #eef0f3}
.hour .h{width:52px;flex:none;color:#667085;font-size:13px}
.ev{background:#eef2ff;border-radius:10px;padding:8px 12px;margin-bottom:6px}
.ev b{font-size:14px}
.ev .t{font-size:12px;color:#1d4ed8}
.dayblock{margin-bottom:14px}
.dayblock h3{margin:0 0 6px}
.need{display:flex;gap:10px;align-items:center;padding:9px 0;border-bottom:1px solid #eef0f3}
.need input[type=checkbox]{width:22px;height:22px;flex:none}
.need .done{color:#98a2b3;text-decoration:line-through}
.pill{display:inline-block;background:#ecfdf5;color:#15803d;border-radius:999px;padding:2px 10px;font-size:12px}
.pill.warn{background:#fef9c3;color:#854d0e}
.nav2{display:flex;justify-content:space-between;gap:10px;margin-bottom:12px}
.share{border-top:2px dashed #e2e6ee;margin-top:16px;padding-top:12px}
code{word-break:break-all;background:#f2f4f7;border-radius:6px;padding:2px 6px;direction:ltr;display:inline-block}
@media(max-width:560px){.row{grid-template-columns:1fr}.wrap{padding:12px}}
</style>
</head>
<body>
<div class="top"><b>مدیریت شخصی</b><small><?= e($user['username'] ?? '') ?></small></div>
<nav class="tabs">
  <a href="/me" class="<?= $tab === 'home' ? 'on' : '' ?>">خانه</a>
  <a href="/me/day" class="<?= $tab === 'day' ? 'on' : '' ?>">روز</a>
  <a href="/me/week" class="<?= $tab === 'week' ? 'on' : '' ?>">هفته</a>
  <a href="/me/todos" class="<?= $tab === 'todos' ? 'on' : '' ?>">کارها</a>
  <a href="/me/notes" class="<?= $tab === 'notes' ? 'on' : '' ?>">یادداشت‌ها</a>
  <a href="/me/projects" class="<?= $tab === 'projects' ? 'on' : '' ?>">پروژه‌ها</a>
  <a href="/me/shared" class="<?= $tab === 'shared' ? 'on' : '' ?>">اشتراک‌ها</a>
</nav>
<div class="wrap">
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <?= $content ?>
</div>
</body>
</html>
