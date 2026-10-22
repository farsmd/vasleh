<div class="card">
  <h1>آیتم‌های منوی «<?= e($menu['name']) ?>» <small>(<code dir="ltr"><?= e($menu['slug']) ?></code>)</small></h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p><a class="btn gray" href="/admin/cms/menus">برگشت به منوها</a></p>
  <?php if (empty($items)): ?>
    <p>هنوز آیتمی نیست.</p>
  <?php endif; ?>
  <?php foreach ($items as $item): ?>
  <div style="border:1px solid #e5e7eb;border-radius:10px;padding:12px;margin-bottom:10px">
    <form method="post" action="/admin/cms/menus/<?= (int) $menu['id'] ?>/items/update" style="display:flex;flex-wrap:wrap;gap:8px;align-items:end">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
      <label style="flex:1;min-width:130px">برچسب
        <input name="label" value="<?= e($item['label']) ?>">
      </label>
      <label style="flex:1;min-width:130px">آدرس
        <input name="url" dir="ltr" value="<?= e($item['url']) ?>">
      </label>
      <label>والد
        <select name="parent_id">
          <option value="0">— سطح اصلی —</option>
          <?php foreach ($tops as $top): ?>
            <?php if ((int) $top['id'] !== (int) $item['id']): ?>
            <option value="<?= (int) $top['id'] ?>" <?= (int) $item['parent_id'] === (int) $top['id'] ? 'selected' : '' ?>><?= e($top['label']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>
      </label>
      <label>ترتیب
        <input name="sort" type="number" value="<?= (int) $item['sort'] ?>" style="width:80px">
      </label>
      <button class="btn yellow">ذخیره</button>
    </form>
    <form method="post" action="/admin/cms/menus/<?= (int) $menu['id'] ?>/items/delete" style="margin-top:8px" onsubmit="return confirm('این آیتم حذف شود؟')">
      <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
      <input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>">
      <button class="btn red">حذف آیتم</button>
    </form>
  </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <h3>آیتم تازه</h3>
  <form method="post" action="/admin/cms/menus/<?= (int) $menu['id'] ?>/items">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <label>برچسب</label>
    <input name="label" required placeholder="تماس با ما">
    <label>آدرس</label>
    <input name="url" dir="ltr" value="/page/about">
    <label>والد (برای زیرمنو)</label>
    <select name="parent_id">
      <option value="0">— سطح اصلی —</option>
      <?php foreach ($tops as $top): ?>
      <option value="<?= (int) $top['id'] ?>"><?= e($top['label']) ?></option>
      <?php endforeach; ?>
    </select>
    <label>ترتیب</label>
    <input name="sort" type="number" value="0">
    <button class="btn green">افزودن آیتم</button>
  </form>
</div>
