<div class="share">
  <h3>اشتراک‌گذاری <?= e($type_label) ?></h3>
  <form method="post" action="/me/share/user">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="type" value="<?= e($type) ?>">
    <input type="hidden" name="item_id" value="<?= (int) $item_id ?>">
    <label>اشتراک با کاربر وصله (نام کاربری، فقط خواندنی)</label>
    <div class="row">
      <div><input type="text" name="username" required placeholder="نام کاربری"></div>
      <div><button class="btn">اشتراک با کاربر</button></div>
    </div>
  </form>
  <?php if (!empty($shares)): ?>
    <?php foreach ($shares as $s): ?>
      <p><?= e($s['username']) ?> <span class="pill">فقط خواندنی</span>
      <form class="inline" method="post" action="/me/share/user/<?= (int) $s['id'] ?>/delete">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="item_id" value="<?= (int) $item_id ?>">
        <button class="btn red small">برداشتن دسترسی</button>
      </form></p>
    <?php endforeach; ?>
  <?php endif; ?>
  <form method="post" action="/me/share/link">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="type" value="<?= e($type) ?>">
    <input type="hidden" name="item_id" value="<?= (int) $item_id ?>">
    <p><button class="btn gray">ساخت لینک عمومی تازه</button> <span class="muted">لینک قبلی باطل می‌شود.</span></p>
  </form>
  <?php if (!empty($links)): ?>
    <?php foreach ($links as $l): ?>
      <p>لینک عمومی: <code>/me/share/<?= e($l['token']) ?></code>
      <form class="inline" method="post" action="/me/share/link/<?= (int) $l['id'] ?>/delete">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="item_id" value="<?= (int) $item_id ?>">
        <button class="btn red small">باطل کردن</button>
      </form></p>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
