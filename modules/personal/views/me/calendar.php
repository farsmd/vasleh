<?php
// تقویم: گرید ماهانه شنبه‌اول + نوار هفته شنبه تا جمعه + تایم‌لاین ساعتی
// با بلاک‌های زمانی + خلاصه ظرفیت تمرکز روز.
// داده: $tasks (شکل tasksForRange) + $month یا $monthFirst (Y-m/Y-m-d) +
// $today + $date (روز فعال) + $workload و day_capacity (اختیاری).
// اختصاص ساعت: درگ‌اند‌دراپ بومی HTML5 + کلیک‌برای‌اختصاص، هر دو با همان
// POST موجود /me/task (ذخیره تسک) و CSRF؛ پس از موفقیت صفحه تازه می‌شود.
$__tasks = (isset($tasks) && is_array($tasks)) ? $tasks : [];
$__today = (string) ($today ?? date('Y-m-d'));
$__date = (string) ($date ?? $__today);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $__date)) { $__date = $__today; }
// ماه شمسیِ روز فعال: روز اول ماه (میلادی)، تعداد روزها، برچسب «آبان ۱۴۰۵».
$__P = \Modules\Personal\Personal::class;
[$__jy, $__jm] = $__P::toJalali((int) substr($__date, 0, 4), (int) substr($__date, 5, 2), (int) substr($__date, 8, 2));
[$__gy, $__gm, $__gd] = $__P::fromJalali($__jy, $__jm, 1);
$__monthFirst = sprintf('%04d-%02d-%02d', $__gy, $__gm, $__gd);
$__monthDays = $__P::jalaliMonthDays($__jy, $__jm);
$__monthLast = date('Y-m-d', strtotime($__monthFirst . ' +' . ($__monthDays - 1) . ' days'));
$__monthLabel = $__P::faMonthLabel($__jy, $__jm);
$__faDigits = ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'];
$__faNum = static fn ($n) => strtr((string) $n, $__faDigits);
$__dateLabel = $__P::faWeekday($__date) . '، ' . $__P::faDate($__date);
$__sat = \Modules\Personal\Personal::weekSaturday($__date);
$__daysFa = ['شنبه', 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'];
$__byDay = [];
foreach ($__tasks as $__t) {
    if (!is_array($__t) || empty($__t['date'])) { continue; }
    $__byDay[(string) $__t['date']][] = $__t;
}
$__dayTasks = $__byDay[$__date] ?? [];
$__unassigned = array_values(array_filter($__tasks, static fn ($t) => is_array($t) && (($t['hour'] ?? '') === 'all-day' || ($t['date'] ?? '') === '')));
$__hours = ['07:00','08:00','09:00','10:00','11:00','12:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00','21:00'];
$__workload = $workload ?? null;
if ($__workload === null) {
    $__workload = 0;
    foreach ($__dayTasks as $__t) { $__workload += (int) ($__t['duration'] ?? 0); }
}
$__cap = null;
if (isset($dayCapacity)) { $__cap = (int) $dayCapacity; }
elseif (isset($settings['day_capacity'])) { $__cap = (int) $settings['day_capacity']; }
// ماه قبل/بعد شمسی: روز اول ماه مجاور (میلادی) برای لینک ?date=.
[$__py, $__pm] = $__jm === 1 ? [$__jy - 1, 12] : [$__jy, $__jm - 1];
[$__ny, $__nm] = $__jm === 12 ? [$__jy + 1, 1] : [$__jy, $__jm + 1];
[$__pgy, $__pgm, $__pgd] = $__P::fromJalali($__py, $__pm, 1);
[$__ngy, $__ngm, $__ngd] = $__P::fromJalali($__ny, $__nm, 1);
$__prevMonth = sprintf('%04d-%02d-%02d', $__pgy, $__pgm, $__pgd);
$__nextMonth = sprintf('%04d-%02d-%02d', $__ngy, $__ngm, $__ngd);
?>
<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-regular fa-calendar-days"></i> تقویم <?= e($__monthLabel) ?></h3>
        <span class="me-nav-row">
            <a href="/me/calendar?date=<?= e($__prevMonth) ?>" class="me-nav-btn">ماه قبل</a>
            <a href="/me/calendar?date=<?= e($__nextMonth) ?>" class="me-nav-btn">ماه بعد</a>
        </span>
    </div>
    <div class="me-cal-grid-head">
        <?php foreach ($__daysFa as $__d): ?><span><?= $__d ?></span><?php endforeach; ?>
    </div>
    <div class="me-cal-grid">
        <?php
        $__offset = ((int) date('w', strtotime($__monthFirst)) + 1) % 7;
        for ($__i = 0; $__i < $__offset; $__i++): ?><span class="me-cal-cell empty"></span><?php endfor;
        for ($__j = 1; $__j <= $__monthDays; $__j++):
            $__d = date('Y-m-d', strtotime($__monthFirst . ' +' . ($__j - 1) . ' days'));
            $__dt = $__byDay[$__d] ?? [];
            $__hasEvent = false; $__hasTodo = false;
            foreach ($__dt as $__t) { if (($__t['kind'] ?? '') === 'event') { $__hasEvent = true; } else { $__hasTodo = true; } }
        ?>
        <a href="/me/calendar?date=<?= e($__d) ?>" class="me-cal-cell<?= $__d === $__date ? ' active' : '' ?><?= $__d === $__today ? ' today' : '' ?>">
            <span class="me-cal-num"><?= $__faNum($__j) ?></span>
            <span class="me-cal-dots"><?php if ($__hasEvent): ?><span class="me-dot event"></span><?php endif; ?><?php if ($__hasTodo): ?><span class="me-dot todo"></span><?php endif; ?></span>
        </a>
        <?php endfor; ?>
    </div>
    <div class="me-cal-strip">
        <?php for ($__i = 0; $__i < 7; $__i++): $__d = date('Y-m-d', strtotime($__sat . ' +' . $__i . ' days')); $__jd = $__P::toJalali((int) substr($__d, 0, 4), (int) substr($__d, 5, 2), (int) substr($__d, 8, 2))[2]; ?>
        <a href="/me/calendar?date=<?= e($__d) ?>" class="me-cal-day<?= $__d === $__date ? ' selected' : '' ?>"><span class="me-cal-day-name"><?= $__daysFa[$__i] ?></span><span class="me-cal-day-num"><?= $__faNum($__jd) ?></span></a>
        <?php endfor; ?>
    </div>
</div>

<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-solid fa-bolt"></i> ظرفیت تمرکز <?= e($__dateLabel) ?></h3>
        <span class="me-section-action"><?= (int) $__workload ?> دقیقه برنامه‌ریزی‌شده</span>
    </div>
    <?php if ($__cap !== null && $__cap > 0): ?>
        <?php $__pct = (int) round(100 * $__workload / $__cap); ?>
        <div class="me-progress-bar-bg"><div class="me-progress-bar-fill" style="width:<?= max(0, min(100, $__pct)) ?>%"></div></div>
        <p class="me-goal-date"><?= (int) $__workload ?> از <?= $__cap ?> دقیقه ظرفیت روز (<?= $__pct ?>٪)<?php if ($__workload > $__cap): ?> — روز شلوغ است.<?php endif; ?></p>
    <?php else: ?>
        <p class="me-goal-date">ظرفیت روز (day_capacity) تنظیم نشده است؛ مجموع مدت کارهای این روز <?= (int) $__workload ?> دقیقه است.</p>
    <?php endif; ?>
</div>

<?php if ($__unassigned !== []): ?>
<div class="me-card">
    <h3 class="me-section-title"><i class="fa-solid fa-hand-pointer"></i> کارهای بدون ساعت — بکشید روی ساعت، یا روی کار و بعد روی ساعت کلیک کنید</h3>
    <div class="me-assign-list">
    <?php foreach ($__unassigned as $__t): ?>
        <button type="button" class="me-assign-card" draggable="true"
            data-kind="<?= e($__t['kind'] ?? 'todo') ?>" data-id="<?= (int) $__t['id'] ?>"
            data-title="<?= e($__t['title'] ?? '') ?>" data-category="<?= e($__t['category'] ?? '') ?>"
            data-priority="<?= e($__t['priority'] ?? '') ?>" data-duration="<?= (int) ($__t['duration'] ?? 30) ?>"
            data-note="<?= e($__t['note'] ?? '') ?>" onclick="meCalSelect(this)" ondragstart="meCalDrag(event, this)">
            <?= e($__t['title'] ?? '') ?><?php if (!empty($__t['date'])): ?> <span class="me-search-date"><?= e($__P::faDate((string) $__t['date'])) ?></span><?php endif; ?>
        </button>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="me-card">
    <h3 class="me-section-title"><i class="fa-regular fa-clock"></i> تایم‌لاین <?= e($__dateLabel) ?></h3>
    <div class="me-timeline">
    <?php foreach ($__hours as $__h): ?>
        <?php $__slot = array_values(array_filter($__dayTasks, static fn ($t) => ($t['hour'] ?? '') === $__h)); ?>
        <div class="me-slot" data-hour="<?= e($__h) ?>" ondragover="event.preventDefault();this.classList.add('drag-over')" ondragleave="this.classList.remove('drag-over')" ondrop="meCalDrop(event, this)" onclick="meCalSlotClick(this)">
            <span class="me-slot-hour"><?= e($__h) ?></span>
            <div class="me-slot-body">
            <?php if ($__slot === []): ?>
                <span class="me-slot-empty">ساعت آزاد — برای اختصاص ساعت کلیک کنید</span>
            <?php else: ?>
                <?php foreach ($__slot as $__t): ?>
                <?php $__cls = ($__t['kind'] ?? '') === 'event' ? 'meeting' : (((string) ($__t['priority'] ?? '')) === 'high' ? 'deep' : 'plain'); ?>
                <div class="me-block me-block-<?= $__cls ?>">
                    <span class="me-block-title"><?= e($__t['title'] ?? '') ?></span>
                    <?php if (!empty($__t['end'])): ?><span class="me-block-time"><?= e($__h) ?> تا <?= e($__t['end']) ?></span><?php endif; ?>
                    <?php if (!empty($__t['duration'])): ?><span class="me-block-time"><?= (int) $__t['duration'] ?> دقیقه</span><?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<script>
var meCalSelected = null;
var meCalDate = <?= json_encode($__date) ?>;
var meCalCsrf = <?= json_encode($csrf ?? '') ?>;
function meCalSelect(el) {
  document.querySelectorAll('.me-assign-card').forEach(function (c) { c.classList.remove('selected'); });
  el.classList.add('selected');
  meCalSelected = el;
}
function meCalDrag(ev, el) {
  meCalSelect(el);
  ev.dataTransfer.setData('text/plain', 'task');
}
function meCalDrop(ev, slot) {
  ev.preventDefault();
  slot.classList.remove('drag-over');
  if (meCalSelected) { meCalAssign(slot.getAttribute('data-hour')); }
}
function meCalSlotClick(slot) {
  if (meCalSelected) { meCalAssign(slot.getAttribute('data-hour')); }
}
function meCalAssign(hour) {
  if (!meCalSelected) { return; }
  var fd = new FormData();
  fd.append('_csrf', meCalCsrf);
  fd.append('kind', meCalSelected.getAttribute('data-kind'));
  fd.append('id', meCalSelected.getAttribute('data-id'));
  fd.append('title', meCalSelected.getAttribute('data-title'));
  fd.append('category', meCalSelected.getAttribute('data-category'));
  fd.append('priority', meCalSelected.getAttribute('data-priority'));
  fd.append('date', meCalDate);
  fd.append('hour', hour);
  fd.append('duration', meCalSelected.getAttribute('data-duration'));
  fd.append('note', meCalSelected.getAttribute('data-note'));
  fetch('/me/task', { method: 'POST', body: fd }).then(function () { window.location.reload(); }).catch(function () { window.location.reload(); });
}
</script>
