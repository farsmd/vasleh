<div class="card">
  <h1>داشبورد تولید</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <p>
    <span class="btn gray">فرمول‌های فعال: <?= (int) $stats['recipes'] ?></span>
    <span class="btn gray">برنامه‌ریزی‌شده: <?= (int) $stats['planned'] ?></span>
    <span class="btn gray">در حال تولید: <?= (int) $stats['running'] ?></span>
    <span class="btn gray">تمام‌شده: <?= (int) $stats['done'] ?></span>
    <span class="btn gray">هزینه مواد دستورهای در حال تولید: <?= number_format((int) round($inProgressCost)) ?> تومان</span>
  </p>
  <p>
    <a class="btn green" href="/admin/production/orders/create">+ دستور تازه</a>
    <a class="btn gray" href="/admin/production/orders">دستورها</a>
    <a class="btn gray" href="/admin/production/recipes">فرمول‌ها</a>
  </p>

  <h2>کسری مواد دستورهای برنامه‌ریزی‌شده</h2>
  <?php if (empty($shortages)): ?>
    <p>کسری‌ای نیست؛ همه دستورهای برنامه‌ریزی‌شده موادشان را دارند.</p>
  <?php else: ?>
  <table>
    <tr><th>دستور</th><th>ماده</th><th>نیاز</th><th>موجودی</th><th>کسری</th></tr>
    <?php foreach ($shortages as $s): ?>
    <tr>
      <td><a href="/admin/production/orders/<?= (int) $s['order_id'] ?>"><code dir="ltr"><?= e($s['order_code']) ?></code></a></td>
      <td class="wrap"><?= e($s['material_title']) ?></td>
      <td><?= \Modules\Inventory\Stock::fmt((float) $s['qty']) ?> <?= e($s['material_unit']) ?></td>
      <td><?= \Modules\Inventory\Stock::fmt((float) $s['available']) ?></td>
      <td><strong><?= \Modules\Inventory\Stock::fmt((float) $s['shortage']) ?></strong></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <h2>دستورهای اخیر</h2>
  <?php if (empty($recent)): ?>
    <p>دستوری نیست.</p>
  <?php else: ?>
  <table>
    <tr><th>کد</th><th>فرمول</th><th>خروجی</th><th>وضعیت</th><th>ساخته‌شده</th></tr>
    <?php foreach ($recent as $o): ?>
    <tr>
      <td><a href="/admin/production/orders/<?= (int) $o['id'] ?>"><code dir="ltr"><?= e($o['code']) ?></code></a></td>
      <td class="wrap"><?= e($o['recipe_title']) ?></td>
      <td><?= e($o['output_title']) ?> × <?= \Modules\Inventory\Stock::fmt((float) $o['output_qty']) ?></td>
      <td><?= e($statuses[$o['status']] ?? $o['status']) ?></td>
      <td><?= e($o['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
