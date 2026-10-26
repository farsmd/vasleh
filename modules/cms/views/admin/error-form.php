<div class="card">
  <h1><?= $code > 0 ? 'ویرایش صفحه خطای ' . (int) $code : 'صفحه خطای تازه' ?></h1>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="/admin/cms/errors/edit/<?= (int) $code ?>">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <?php if ($code > 0): ?>
      <input type="hidden" name="code" value="<?= (int) $code ?>">
      <p>کد وضعیت: <strong><?= (int) $code ?></strong></p>
    <?php else: ?>
      <label>کد وضعیت (عدد سه‌رقمی، مثلاً ۴۱۸)
        <input type="number" name="code" min="100" max="599" required>
      </label>
    <?php endif; ?>
    <label>عنوان صفحه
      <input type="text" name="title" required value="<?= e($page['title'] ?? '') ?>">
    </label>
    <label>بدنه (HTML — مثل بدنه نوشته‌ها)
      <textarea name="body" rows="10" required><?= e($page['body'] ?? '') ?></textarea>
    </label>
    <label class="check" style="display:flex;gap:.4rem;align-items:center">
      <input type="checkbox" name="active" value="1" <?= (int) ($page['active'] ?? 1) === 1 ? 'checked' : '' ?>> فعال (غیرفعال = نمایش صفحه پیش‌فرض)
    </label>
    <p>
      <button class="btn green">ذخیره</button>
      <a class="btn gray" href="/admin/cms/errors">انصراف</a>
    </p>
  </form>
</div>
