<?php
// لی‌اوت مدیریت وصله — بوت‌استرپ ۵ RTL (لوکال، بدون CDN) + سایدبار تیره سبک وردپرس.
// قرارداد داده مثل قبل: $title, $content, $menu, $user, $csrf
$assetBase = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
$currentPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
if ($assetBase !== '' && str_starts_with($currentPath, $assetBase)) {
    $currentPath = substr($currentPath, strlen($assetBase)) ?: '/';
}
$appVersion = '';
$versionFile = dirname(__DIR__, 4) . '/version.json';
if (is_file($versionFile)) {
    $parts = json_decode((string) file_get_contents($versionFile), true);
    if (is_array($parts)) {
        $appVersion = (int) ($parts['core'] ?? 0) . '.' . (int) ($parts['system'] ?? 0) . '.' . (int) ($parts['other'] ?? 0);
    }
}
$isActive = static function (string $path) use ($currentPath): bool {
    if ($path === '/admin') {
        return $currentPath === '/admin';
    }
    if (str_starts_with($path, '/admin')) {
        return $currentPath === $path || str_starts_with($currentPath, rtrim($path, '/') . '/');
    }
    return $currentPath === $path;
};
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — مدیریت وصله</title>
<link rel="stylesheet" href="<?= e($assetBase) ?>/assets/bootstrap/bootstrap.rtl.min.css">
<style>
:root{--wp-dark:#1d2327;--wp-dark-2:#2c3338;--wp-blue:#2271b1;--admin-bg:#f0f0f1}
html,body{overflow-x:clip}
body{font-family:Tahoma,"Segoe UI",sans-serif;background:var(--admin-bg)}
.admin-shell{min-height:100vh}
/* سایدبار وردپرسی */
.admin-sidebar{background:var(--wp-dark);color:#c3c4c7;width:236px;flex:0 0 236px}
.admin-sidebar .brand{display:flex;align-items:baseline;gap:8px;padding:16px 16px 10px;color:#fff}
.admin-sidebar .brand strong{font-size:1.25rem}
.admin-sidebar .brand small{color:#9ba0a3}
.admin-sidebar nav{padding:6px 0}
.admin-sidebar nav a{display:block;color:#c3c4c7;text-decoration:none;padding:9px 16px;border-inline-start:4px solid transparent;font-size:.95rem}
.admin-sidebar nav a:hover{background:var(--wp-dark-2);color:#fff}
.admin-sidebar nav a.active{background:var(--wp-blue);color:#fff;border-inline-start-color:#fff}
.admin-sidebar .side-user{border-top:1px solid #3c434a;margin-top:10px;padding:12px 16px;font-size:.9rem}
.admin-sidebar .side-user form{margin-top:8px}
.admin-sidebar .side-user button{background:var(--wp-dark-2);color:#fff;border:0;padding:6px 12px;border-radius:6px;cursor:pointer}
.admin-sidebar .offcanvas-header{color:#fff;border-bottom:1px solid #3c434a}
@media (max-width:991.98px){.admin-sidebar{width:260px;flex-basis:auto}}
/* بدنه */
.admin-main{min-width:0;flex:1 1 auto}
.admin-topbar{background:#fff;border-bottom:1px solid #dcdcde;padding:10px 16px;display:flex;align-items:center;gap:12px;position:sticky;top:0;z-index:1020}
.admin-topbar h1{font-size:1.1rem;margin:0;font-weight:700}
.admin-content{padding:20px 16px;max-width:1200px;margin:0 auto}
/* سازگاری ویوهای ماژول‌ها */
.admin-content .card{display:block;background:#fff;border:1px solid #dcdcde;border-radius:12px;padding:20px;box-shadow:0 1px 2px rgba(0,0,0,.04);margin-bottom:16px}
.admin-content h1{font-size:1.45rem;margin-bottom:12px}
.admin-content h3{font-size:1.1rem;margin:14px 0 10px}
.admin-content table{width:100%;border-collapse:collapse;margin-bottom:8px}
.admin-content th,.admin-content td{padding:9px 10px;border-bottom:1px solid #e5e7eb;text-align:right;vertical-align:top}
.admin-content th{background:#f6f7f7;font-weight:700;white-space:nowrap}
.admin-content code{background:#eef;padding:2px 6px;border-radius:6px;word-break:break-word}
.admin-content pre{overflow:auto;max-width:100%}
.admin-content img,.admin-content svg,.admin-content video,.admin-content iframe{max-width:100%}
.admin-content input:not([type=checkbox]):not([type=radio]):not([type=submit]),
.admin-content select,.admin-content textarea{width:100%;padding:9px;border:1px solid #d1d5db;border-radius:8px;margin:4px 0 12px;box-sizing:border-box;background:#fff;font-family:inherit}
.admin-content .btn{display:inline-block;background:#2563eb;color:#fff;border:0;padding:8px 16px;border-radius:8px;cursor:pointer;text-decoration:none;font-size:.95rem;line-height:1.5}
.admin-content .btn:hover{background:#1d4ed8;color:#fff}
.admin-content .btn.green{background:#16a34a}.admin-content .btn.green:hover{background:#15803d}
.admin-content .btn.gray{background:#6b7280}.admin-content .btn.gray:hover{background:#4b5563}
.admin-content .btn.yellow{background:#eab308;color:#111}.admin-content .btn.yellow:hover{background:#ca8a04}
.admin-content .flash{background:#dcfce7;padding:10px 14px;border-radius:8px;margin-bottom:12px}
.admin-content .error{background:#fee2e2;padding:10px 14px;border-radius:8px;margin-bottom:12px}
.admin-content form{margin:0}
.table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch}
@media (max-width:575.98px){.admin-content{padding:14px 10px}.admin-content .card{padding:14px}}
</style>
</head>
<body>
<div class="admin-shell d-lg-flex">

  <aside class="offcanvas-lg offcanvas-start admin-sidebar" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-header d-lg-none">
      <span id="adminSidebarLabel">منوی مدیریت</span>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="بستن"></button>
    </div>
    <div class="brand"><strong>وصله</strong><?php if ($appVersion !== ''): ?><small>نسخه <?= e($appVersion) ?></small><?php endif; ?></div>
    <nav>
      <?php foreach (($menu ?? []) as $item): ?>
        <a href="<?= e($item['path']) ?>" class="<?= $isActive((string) $item['path']) ? 'active' : '' ?>"><?= e($item['label']) ?></a>
      <?php endforeach; ?>
      <a href="<?= e($assetBase === '' ? '/' : $assetBase . '/') ?>">مشاهده سایت</a>
    </nav>
    <?php if (!empty($user)): ?>
    <div class="side-user">
      <div><?= e($user['username']) ?> <small>(<?= e($user['role'] === 'owner' ? 'مالک' : 'مدیر') ?>)</small></div>
      <form method="post" action="<?= e($assetBase) ?>/admin/logout">
        <input type="hidden" name="_csrf" value="<?= e($csrf ?? '') ?>">
        <button type="submit">خروج</button>
      </form>
    </div>
    <?php endif; ?>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="منو">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/></svg>
      </button>
      <h1><?= e($title) ?></h1>
      <?php if (!empty($user)): ?>
      <span class="ms-auto d-none d-sm-inline text-muted small"><?= e($user['username']) ?></span>
      <?php endif; ?>
    </header>
    <main class="admin-content"><?= $content ?></main>
  </div>

</div>
<script src="<?= e($assetBase) ?>/assets/bootstrap/bootstrap.bundle.min.js"></script>
<script>
// جدول‌های ویوهای ماژول‌ها را ریسپانسیو می‌کند تا روی موبایل بیرون نزنند.
document.querySelectorAll('.admin-content table').forEach(function (t) {
  if (t.closest('.table-responsive')) return;
  var w = document.createElement('div');
  w.className = 'table-responsive';
  t.parentNode.insertBefore(w, t);
  w.appendChild(t);
});
</script>
</body>
</html>
