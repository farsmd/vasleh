<?php use Modules\Inventory\Stock; ?>
<div class="card">
  <h1>فرمول‌های ساخت (BOM)</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    <span class="btn gray">فرمول‌های فعال: <?= (int) $stats['recipes'] ?></span>
    <span class="btn gray">در حال تولید: <?= (int) $stats['running'] ?></span>
    <span class="btn gray">تمام‌شده: <?= (int) $stats['done'] ?></span>
    <a class="btn green" href="/admin/production/recipes/create">+ فرمول تازه</a>
    <a class="btn gray" href="/admin/production/orders">دستورهای تولید</a>
  </p>
  <?php if (empty($recipes)): ?>
    <p>هنوز فرمولی نیست. فرمول یعنی: برای ساخت چه کالایی، چه مواد اولیه‌ای چقدر لازم است.</p>
  <?php else: ?>
  <table>
    <tr><th>فرمول</th><th>خروجی هر بچ</th><th>مواد</th><th>هزینه مواد هر بچ</th><th>فعال</th><th>عملیات</th></tr>
    <?php foreach ($recipes as $r): ?>
    <tr>
      <td class="wrap"><?= e($r['title']) ?></td>
      <td><?= e($r['output_title']) ?> × <?= Stock::fmt((float) $r['output_qty']) ?> <?= e($r['output_unit']) ?></td>
      <td><?= (int) $r['lines_count'] ?> قلم</td>
      <td><?= number_format((int) round($r['cost'])) ?></td>
      <td><?= (int) $r['active'] === 1 ? 'بله' : 'خیر' ?></td>
      <td>
        <a class="btn yellow" href="/admin/production/recipes/edit/<?= (int) $r['id'] ?>">ویرایش</a>
        <a class="btn green" href="/admin/production/orders/create?recipe=<?= (int) $r['id'] ?>">ساخت دستور</a>
        <form method="post" action="/admin/production/recipes/delete/<?= (int) $r['id'] ?>" style="display:inline" onsubmit="return confirm('این فرمول حذف شود؟ (دستورهای قبلی می‌مانند)')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn red">حذف</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <p class="muted">هزینه مواد بر مبنای آخرین قیمت خرید انبار است؛ ۰ یعنی هنوز خرید قیمت‌داری برای آن ماده ثبت نشده.</p>
  <?php endif; ?>
</div>
