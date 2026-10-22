<?php use Modules\Inventory\Stock; $wTitles = []; foreach ($warehouses as $w) { $wTitles[(int) $w['id']] = $w['title']; } ?>
<div class="card">
  <h1>دستورهای تولید</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    <span class="btn gray">برنامه‌ریزی‌شده: <?= (int) $stats['planned'] ?></span>
    <span class="btn gray">در حال تولید: <?= (int) $stats['running'] ?></span>
    <span class="btn gray">تمام‌شده: <?= (int) $stats['done'] ?></span>
    <a class="btn green" href="/admin/production/orders/create">+ دستور تازه</a>
  </p>
  <p>
    <a class="btn <?= $activeStatus === '' ? 'green' : 'gray' ?>" href="/admin/production/orders">همه</a>
    <?php foreach ($statuses as $k => $label): ?>
      <a class="btn <?= $activeStatus === $k ? 'green' : 'gray' ?>" href="/admin/production/orders?status=<?= $k ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
  </p>
  <?php if (empty($orders)): ?>
    <p>دستوری نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>کد</th><th>فرمول</th><th>خروجی</th><th>بچ</th><th>انبار</th><th>وضعیت</th><th>ساخته‌شده</th><th>عملیات</th></tr>
    <?php foreach ($orders as $o): ?>
    <tr>
      <td><code dir="ltr"><?= e($o['code']) ?></code></td>
      <td class="wrap"><?= e($o['recipe_title']) ?></td>
      <td><?= e($o['output_title']) ?> × <?= Stock::fmt((float) $o['output_qty']) ?> <?= e($o['output_unit']) ?></td>
      <td><?= Stock::fmt((float) $o['batches']) ?></td>
      <td><?= e($wTitles[(int) $o['warehouse_id']] ?? '؟') ?></td>
      <td><?= e($statuses[$o['status']] ?? $o['status']) ?></td>
      <td><?= e($o['created_at']) ?></td>
      <td><a class="btn yellow" href="/admin/production/orders/<?= (int) $o['id'] ?>">جزئیات</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
