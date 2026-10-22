<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | وصله</title>
<style>
  * { box-sizing: border-box; }
  body { font-family: Tahoma, system-ui, sans-serif; margin: 0; background: #f6f7f9; color: #1f2937; line-height: 1.9; }
  header { background: #1d2327; color: #fff; }
  .wrap { max-width: 860px; margin: 0 auto; padding: 0 16px; }
  header .wrap { display: flex; align-items: center; gap: 20px; padding-top: 14px; padding-bottom: 14px; }
  header .brand { font-weight: bold; font-size: 1.15rem; color: #fff; text-decoration: none; }
  header nav a { color: #c3c4c7; text-decoration: none; margin-inline-start: 14px; font-size: .95rem; }
  header nav a:hover { color: #fff; }
  main .wrap { padding-top: 28px; padding-bottom: 40px; }
  .post-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 18px 20px; margin-bottom: 14px; }
  .post-card h2 { margin: 0 0 6px; font-size: 1.2rem; }
  .post-card h2 a { color: #1f2937; text-decoration: none; }
  .post-card p { margin: 0; color: #4b5563; }
  .post-card time { color: #9ca3af; font-size: .85rem; }
  article { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; }
  article h1 { margin-top: 0; font-size: 1.6rem; }
  article time { color: #9ca3af; font-size: .85rem; }
  footer { color: #9ca3af; text-align: center; padding: 20px 0 30px; font-size: .85rem; }
</style>
</head>
<body>
<header>
  <div class="wrap">
    <a class="brand" href="/">وصله</a>
    <nav>
      <a href="/">خانه</a>
      <a href="/blog">وبلاگ</a>
      <a href="/page/about">درباره ما</a>
    </nav>
  </div>
</header>
<main>
  <div class="wrap">
    <?= $content ?>
  </div>
</main>
<footer>ساخته‌شده با وصله — ماژول مدیریت محتوا</footer>
</body>
</html>
