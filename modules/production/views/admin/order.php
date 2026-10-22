<?php use Modules\Inventory\Stock; ?>
<div class="card">
  <h1>دستور تولید <code dir="ltr"><?= e($order['code']) ?></code></h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
  <table>
    <tr><th>فرمول</th><td><?= e($order['recipe_title']) ?></td><th>وضعیت</th><td><?= e($statuses[$order['status']] ?? $order['status']) ?></td></tr>
    <tr><th>خروجی</th><td><?= e($order['output_title']) ?> × <?= Stock::fmt((float) $order['output_qty']) ?> <?= e($order['output_unit']) ?></td><th>بچ</th><td><?= Stock::fmt((float) $order['batches']) ?></td></tr>
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
