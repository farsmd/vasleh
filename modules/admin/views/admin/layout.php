<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — مدیریت</title>
<style>
body{font-family:Tahoma,sans-serif;background:#f6f7f9;margin:0}
header{background:#1f2937;color:#fff;padding:12px 20px;display:flex;gap:18px;align-items:center;flex-wrap:wrap}
header a{color:#dbeafe;text-decoration:none}
header .user{margin-inline-start:auto;display:flex;gap:12px;align-items:center}
header form{margin:0}
header button{background:#374151;color:#fff;border:0;padding:6px 12px;border-radius:8px;cursor:pointer}
main{max-width:900px;margin:24px auto;padding:0 16px}
.card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 4px 20px rgba(0,0,0,.05);margin-bottom:16px}
table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid #eee;text-align:right;vertical-align:top}
input,textarea,select{width:100%;padding:9px;border:1px solid #d1d5db;border-radius:8px;margin:4px 0 12px;box-sizing:border-box}
button,.btn{background:#2563eb;color:#fff;border:0;padding:8px 16px;border-radius:8px;cursor:pointer;text-decoration:none;display:inline-block}
.btn.green{background:#16a34a}.btn.gray{background:#6b7280}.btn.yellow{background:#eab308;color:#111}
.flash{background:#dcfce7;padding:10px 14px;border-radius:8px;margin-bottom:12px}
.error{background:#fee2e2;padding:10px 14px;border-radius:8px;margin-bottom:12px}
code{background:#eef;padding:2px 6px;border-radius:6px}
</style>
</head>
<body>
<header>
  <strong>بخش مدیریت</strong>
  <?php foreach (($menu ?? []) as $item): ?>
    <a href="<?= e($item['path']) ?>"><?= e($item['label']) ?></a>
  <?php endforeach; ?>
  <a href="/">مشاهده سایت</a>
  <?php if (!empty($user)): ?>
  <div class="user">
    <span><?= e($user['username']) ?> (<?= e($user['role'] === 'owner' ? 'مالک' : 'مدیر') ?>)</span>
    <form method="post" action="/admin/logout">
      <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
      <button>خروج</button>
    </form>
  </div>
  <?php endif; ?>
</header>
<main><?= $content ?></main>
</body>
</html>
