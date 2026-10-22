<h1>آپدیت سیستم</h1>
<p>نسخه‌بندی وصله سه‌بخشی است: <strong>هسته . ماژول‌های سیستمی . بقیه</strong> — مثلاً <code>1.1.4</code> یعنی هسته ۱، سیستمی ۱، بقیه ۴.</p>

<?php if (!empty($result)): ?>
<div class="flash">
  آپدیت نصب شد: از <code><?= e($result['from']) ?></code> به <code><?= e($result['to']) ?></code><br>
  فایل‌های نوشته‌شده — هسته: <?= (int) $result['written']['core'] ?>، سیستمی: <?= (int) $result['written']['system'] ?>، بقیه: <?= (int) $result['written']['other'] ?>
  — حذف‌شده: <?= (int) $result['deleted'] ?><br>
  بکاپ این نصب: <code><?= e($result['backup']) ?></code>
  <?php foreach ($result['log'] as $line): ?><br><small><?= e($line) ?></small><?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($runError)): ?>
<div class="error">آپدیت انجام نشد: <?= e($runError) ?></div>
<?php endif; ?>

<div class="card">
  <table>
    <tr><th>نسخه فعلی</th><td><code style="font-size:1.2em"><?= e(\Modules\Update\Updater::format($check['local'])) ?></code></td></tr>
    <tr><th>نسخه روی گیت‌هاب</th><td>
      <?php if ($check['remote'] !== null): ?>
        <code style="font-size:1.2em"><?= e(\Modules\Update\Updater::format($check['remote'])) ?></code>
      <?php else: ?>
        —
      <?php endif; ?>
    </td></tr>
    <tr><th>وضعیت</th><td>
      <?php if ($check['error'] !== null): ?>
        خطا در بررسی: <?= e($check['error']) ?>
      <?php elseif ($check['newer']): ?>
        <strong>نسخه تازه هست</strong> —
        بخش‌های تغییریافته:
        <?= $check['changed']['core'] ? 'هسته' : '' ?>
        <?= $check['changed']['system'] ? ' ماژول‌های سیستمی' : '' ?>
        <?= $check['changed']['other'] ? ' بقیه' : '' ?>
      <?php else: ?>
        سیستم به‌روز است.
      <?php endif; ?>
    </td></tr>
  </table>

  <?php $ready = array_reduce($preflight, static fn (bool $ok, array $item): bool => $ok && $item['ok'], true); ?>
  <?php if ($check['newer'] && $ready): ?>
  <form method="post" action="/admin/update/run" onsubmit="return confirm('نسخه تازه نصب شود؟ قبل از نصب بکاپ گرفته می‌شود و storage دست نمی‌خورد.')">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <button class="btn green">نصب آپدیت <?= e(\Modules\Update\Updater::format($check['remote'])) ?></button>
  </form>
  <?php endif; ?>
</div>

<div class="card">
  <h3>پیش‌نیازهای هاست</h3>
  <table>
    <?php foreach ($preflight as $item): ?>
    <tr><td><?= e($item['label']) ?></td><td><?= $item['ok'] ? '✓ برقرار' : '✗ نیست' ?></td></tr>
    <?php endforeach; ?>
  </table>
</div>

<?php if (!empty($check['commits'])): ?>
<div class="card">
  <h3>آخرین کامیت‌های گیت‌هاب</h3>
  <table>
    <tr><th>کامیت</th><th>پیام</th><th>تاریخ</th></tr>
    <?php foreach ($check['commits'] as $commit): ?>
    <tr><td><code><?= e($commit['sha']) ?></code></td><td><?= e($commit['message']) ?></td><td><?= e(substr($commit['date'], 0, 10)) ?></td></tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<div class="card">
  <h3>قواعد آپدیت</h3>
  <p>منبع آپدیت: ریپوی عمومی <code>farsmd/vasleh</code> روی گیت‌هاب، برنچ <code>main</code>.</p>
  <p>پوشه <code>storage</code> (دیتابیس، کلید برنامه، وضعیت ماژول‌ها و بکاپ‌ها) در آپدیت <strong>هرگز</strong> دست نمی‌خورد.
  فایل‌های ساخته‌شده از پنل (مثل ماژول‌هایی که خودتان ساخته‌اید) هم حذف نمی‌شوند؛ حذف فقط برای فایل‌هایی است که قبلاً از گیت‌هاب نصب شده باشند.
  قبل از هر نصب، از فایل‌های تغییریافته در <code>storage/backups</code> بکاپ گرفته می‌شود.</p>
</div>
