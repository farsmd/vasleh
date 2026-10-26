<div class="card">
  <h1>درخواست‌های آزمایشی</h1>
  <?php if (!empty($flash)): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <p>درخواست‌هایی که از لندینگ صفحه اصلی ثبت شده‌اند. با متقاضی تماس بگیرید و نسخه آزمایشی را دستی فعال کنید.</p>
  <?php if (empty($leads)): ?>
    <p>هنوز درخواستی ثبت نشده است.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>نام</th><th>شماره تماس</th><th>ماژول‌های انتخابی</th><th>مبلغ ماهانه</th><th>تاریخ</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($leads as $l): ?>
          <?php $mods = json_decode((string) $l['modules'], true) ?: []; ?>
          <tr>
            <td><?= e($l['name']) ?></td>
            <td dir="ltr" style="text-align: right;"><?= e($l['phone']) ?></td>
            <td class="wrap"><?= empty($mods) ? 'فقط پایه' : e(implode('، ', array_map(static fn($s) => $modules[$s]['title'] ?? $s, $mods))) ?></td>
            <td><?= \Modules\Front\Landing::faNum((int) $l['total']) ?> تومان</td>
            <td><?= e($l['created_at']) ?></td>
            <td>
              <form method="post" action="/admin/leads/<?= (int) $l['id'] ?>/delete" onsubmit="return confirm('این درخواست حذف شود؟');">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="btn red">حذف</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
  <p><a class="btn gray" href="/admin/landing">تنظیمات قیمت‌های لندینگ</a></p>
</div>
