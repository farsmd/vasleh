<?php use Modules\Inventory\Stock; ?>
<div class="card">
  <h1>دستور تولید <code dir="ltr"><?= e($order['code']) ?></code></h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <table>
    <tr><th>فرمول</th><td><?= e($order['recipe_title']) ?></td><th>وضعیت</th><td><?= e($statuses[$order['status']] ?? $order['status']) ?></td></tr>
    <tr><th>خروجی</th><td><?= e($order['output_title']) ?> × <?= Stock::fmt((float) $order['output_qty']) ?> <?= e($order['output_unit']) ?></td><th>بچ</th><td><?= Stock::fmt((float) $order['batches']) ?></td></tr>
    <tr><th>پیشرفت ساخت</th><td><?= Stock::fmt((float) ($order['done_qty'] ?? 0)) ?> از <?= Stock::fmt((float) $order['output_qty']) ?> <?= e($order['output_unit']) ?><?= $order['status'] === 'in_progress' ? ' (باقی‌مانده: ' . Stock::fmt((float) $order['output_qty'] - (float) ($order['done_qty'] ?? 0)) . ')' : '' ?></td><th>هزینه مواد</th><td><?= number_format((int) round($cost['total'])) ?> تومان (هر واحد: <?= number_format((int) round($cost['unit'])) ?>)</td></tr>
    <tr><th>انبار</th><td><?= e($warehouse['title'] ?? '؟') ?></td><th>سازنده</th><td><?= e($order['actor']) ?></td></tr>
    <tr><th>ساخته‌شده</th><td><?= e($order['created_at']) ?></td><th>شروع / پایان</th><td><?= e($order['started_at']) ?> <?= $order['finished_at'] !== '' ? ' / ' . e($order['finished_at']) : '' ?></td></tr>
    <?php if (($order['note'] ?? '') !== ''): ?><tr><th>یادداشت</th><td colspan="3" class="wrap"><?= e($order['note']) ?></td></tr><?php endif; ?>
  </table>

  <h2>مواد لازم</h2>
  <table>
    <tr><th>ماده</th><th>نیاز</th><th>موجودی انبار</th><th>کسری</th></tr>
    <?php foreach ($lines as $ln): ?>
    <tr>
      <td class="wrap"><?= e($ln['material_title']) ?></td>
      <td><?= Stock::fmt((float) $ln['qty']) ?> <?= e($ln['material_unit']) ?></td>
      <td><?= Stock::fmt((float) $ln['available']) ?></td>
      <td><?= $ln['shortage'] > 0 ? '<strong>' . Stock::fmt((float) $ln['shortage']) . '</strong>' : '—' ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <h2>هزینه مواد (بر مبنای آخرین قیمت خرید)</h2>
  <table>
    <tr><th>ماده</th><th>مقدار</th><th>آخرین قیمت خرید</th><th>هزینه</th></tr>
    <?php foreach ($cost['lines'] as $cl): ?>
    <tr>
      <td class="wrap"><?= e($cl['material_title']) ?></td>
      <td><?= Stock::fmt((float) $cl['qty']) ?> <?= e($cl['material_unit']) ?></td>
      <td><?= $cl['unit_price'] > 0 ? number_format((int) round($cl['unit_price'])) : '— (بدون خرید قیمت‌دار)' ?></td>
      <td><?= number_format((int) round($cl['line_cost'])) ?></td>
    </tr>
    <?php endforeach; ?>
    <tr><th colspan="3">جمع هزینه مواد</th><th><?= number_format((int) round($cost['total'])) ?></th></tr>
  </table>

  <?php if ($order['status'] === 'in_progress'): ?>
  <form method="post" action="/admin/production/orders/<?= (int) $order['id'] ?>/partial" style="border:1px dashed #bbb;border-radius:.5rem;padding:.8rem;margin:1rem 0">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <strong>اتمام جزئی:</strong>
    بخشی از خروجی را وارد انبار کنید؛ دستور تا تکمیل کامل در حال تولید می‌ماند.
    <label>مقدار
      <input type="number" name="qty" step="any" min="0" required>
    </label>
    <button class="btn green">ثبت اتمام جزئی</button>
  </form>
  <?php endif; ?>

  <?php if (in_array($order['status'], ['in_progress', 'done'], true)): ?>
  <form method="post" action="/admin/production/orders/<?= (int) $order['id'] ?>/waste" style="border:1px dashed #bbb;border-radius:.5rem;padding:.8rem;margin:1rem 0">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
    <strong>ثبت ضایعات محصول:</strong>
    واحدهای معیوبِ خروجی از انبار کسر می‌شوند.
    <label>مقدار
      <input type="number" name="qty" step="any" min="0" required>
    </label>
    <label>یادداشت
      <input type="text" name="note">
    </label>
    <button class="btn red">ثبت ضایعات</button>
  </form>
  <?php endif; ?>

  <?php if (!empty($wastes)): ?>
  <h2>ضایعات ثبت‌شده</h2>
  <table>
    <tr><th>مقدار</th><th>یادداشت</th><th>ثبت‌کننده</th><th>تاریخ</th></tr>
    <?php foreach ($wastes as $w): ?>
    <tr>
      <td><?= Stock::fmt((float) $w['qty']) ?> <?= e($order['output_unit']) ?></td>
      <td class="wrap"><?= e($w['note']) ?></td>
      <td><?= e($w['actor']) ?></td>
      <td><?= e($w['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>

  <p style="margin-top:1rem">
    <?php if ($order['status'] === 'planned'): ?>
      <?php if ($canStart): ?>
        <form method="post" action="/admin/production/orders/<?= (int) $order['id'] ?>/start" style="display:inline" onsubmit="return confirm('تولید شروع و مواد از انبار کسر شود؟')">
          <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
          <button class="btn green">شروع تولید (کسر مواد)</button>
        </form>
      <?php else: ?>
        <span class="error" style="display:inline-block">موجودی مواد کافی نیست؛ اول انبار را شارژ کنید.</span>
      <?php endif; ?>
    <?php endif; ?>
    <?php if ($order['status'] === 'in_progress'): ?>
      <form method="post" action="/admin/production/orders/<?= (int) $order['id'] ?>/complete" style="display:inline" onsubmit="return confirm('تولید تمام شد و خروجی وارد انبار شود؟')">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="btn green">اتمام تولید (ورود خروجی)</button>
      </form>
    <?php endif; ?>
    <?php if (in_array($order['status'], ['planned', 'in_progress'], true)): ?>
      <form method="post" action="/admin/production/orders/<?= (int) $order['id'] ?>/cancel" style="display:inline" onsubmit="return confirm('این دستور لغو شود؟<?= $order['status'] === 'in_progress' ? ' مواد مصرف‌شده به انبار برمی‌گردد.' : '' ?>')">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="btn red">لغو دستور</button>
      </form>
    <?php endif; ?>
    <a class="btn gray" href="/admin/production/orders">بازگشت به دستورها</a>
    <a class="btn gray" href="/admin/inventory/kardex">کاردکس انبار</a>
  </p>
</div>
