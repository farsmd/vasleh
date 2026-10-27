<link rel="stylesheet" href="/assets/codemirror/codemirror.css">
<div class="card">
  <h1>قالب‌های فروشگاه</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>پوسته دور همه صفحات فروشگاه از همین‌جا مدیریت می‌شود؛ قالب پیش‌فرض روی همه صفحات فروشگاه رندر می‌شود. توکن‌های مجاز در محتوای قالب: <code dir="ltr">{{content}}</code> (الزامی — جای محتوای صفحه)، <code dir="ltr">{{title}}</code>، <code dir="ltr">{{shop_title}}</code>، <code dir="ltr">{{shop_accent}}</code>، <code dir="ltr">{{app_version}}</code>، <code dir="ltr">{{cart_count}}</code>، <code dir="ltr">{{wishlist_badge}}</code>، <code dir="ltr">{{account_link}}</code>، <code dir="ltr">{{categories_nav}}</code>، <code dir="ltr">{{cart_items}}</code>، <code dir="ltr">{{cart_total}}</code>، <code dir="ltr">{{csrf}}</code>. محتوا خام رندر می‌شود چون فقط مالک به این صفحه دسترسی دارد.</p>
  <p><a class="btn green" href="/admin/shop/templates/create">قالب تازه</a></p>
  <?php if (empty($templates)): ?>
    <p>هنوز قالبی نیست.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>عنوان</th><th>نام</th><th>پیش‌فرض</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($templates as $t): ?>
          <tr>
            <td><?= e($t['title']) ?></td>
            <td dir="ltr" style="text-align: right;"><?= e($t['name']) ?></td>
            <td><?= (int) $t['is_default'] === 1 ? '<span class="btn green">پیش‌فرض</span>' : '—' ?></td>
            <td>
              <a class="btn yellow" href="/admin/shop/templates/edit/<?= (int) $t['id'] ?>">ویرایش</a>
              <?php if ((int) $t['is_default'] !== 1): ?>
              <form method="post" action="/admin/shop/templates/<?= (int) $t['id'] ?>/default" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn green">پیش‌فرض شود</button>
              </form>
              <form method="post" action="/admin/shop/templates/<?= (int) $t['id'] ?>/delete" onsubmit="return confirm('این قالب حذف شود؟');" style="display:inline;">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn red">حذف</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>
