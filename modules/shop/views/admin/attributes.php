<div class="card">
  <h1>ویژگی‌های محصول</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p class="muted">ویژگی‌ها برای ساخت واریانت به کار می‌روند؛ مثلاً ویژگی «رنگ» با مقدارهای قرمز و آبی × ویژگی «سایز» با مقدارهای کوچک و بزرگ، چهار واریانت می‌سازد که هر کدام قیمت و موجودی جدا دارند.</p>
  <form method="post" action="/admin/shop/attributes/save">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="id" value="0">
    <div class="row">
      <label>عنوان ویژگی تازه
        <input type="text" name="title" required placeholder="مثلاً سایز">
      </label>
      <label>نامک (خالی = ساخت خودکار)
        <input type="text" name="slug" dir="ltr">
      </label>
      <label>ترتیب
        <input type="number" name="sort" value="0">
      </label>
    </div>
    <p><button class="btn green">ساخت ویژگی</button></p>
  </form>
</div>

<?php foreach ($attributes as $a): ?>
<div class="card">
  <h2 style="margin-top:0"><?= e($a['title']) ?> <small class="muted" dir="ltr">(<?= e($a['slug']) ?>)</small></h2>
  <div style="display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.8rem">
    <?php if (empty($a['values'])): ?><span class="muted">هنوز مقداری ندارد.</span><?php endif; ?>
    <?php foreach ($a['values'] as $v): ?>
      <span style="border:1px solid #ccc;border-radius:2rem;padding:.15rem .6rem;display:inline-flex;gap:.4rem;align-items:center">
        <?= e($v['value']) ?>
        <form method="post" action="/admin/shop/attributes/values/delete/<?= (int) $v['id'] ?>" style="display:inline" onsubmit="return confirm('این مقدار حذف شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red" style="padding:0 .4rem">×</button>
        </form>
      </span>
    <?php endforeach; ?>
  </div>
  <form method="post" action="/admin/shop/attributes/<?= (int) $a['id'] ?>/values" style="display:flex;gap:.5rem;max-width:26rem">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <input type="text" name="value" required placeholder="مقدار تازه، مثلاً سبز">
    <button class="btn green">افزودن</button>
  </form>
  <form method="post" action="/admin/shop/attributes/delete/<?= (int) $a['id'] ?>" onsubmit="return confirm('این ویژگی و همه مقدارهایش حذف شود؟')" style="margin-top:.8rem">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <button class="btn red">حذف ویژگی</button>
  </form>
</div>
<?php endforeach; ?>
