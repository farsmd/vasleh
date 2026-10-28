<?php
// ماتریس آیزنهاور: محاسباتی از $tasks؛ فوری = سررسید در ۲ روز آینده یا گذشته.
// فقط کارهای انجام‌نشده می‌آیند؛ تسک بدون تاریخ «بعداً» حساب می‌شود.
$__tasks = (isset($tasks) && is_array($tasks)) ? $tasks : [];
$__today = (string) ($today ?? date('Y-m-d'));
$__soon = date('Y-m-d', strtotime($__today . ' +2 days'));
$__q = ['do' => [], 'schedule' => [], 'delegate' => [], 'drop' => []];
foreach ($__tasks as $__t) {
    if (!is_array($__t) || !empty($__t['done'])) { continue; }
    $__high = ((string) ($__t['priority'] ?? '')) === 'high';
    $__date = (string) ($__t['date'] ?? '');
    $__urgent = $__date !== '' && $__date <= $__soon;
    if ($__high && $__urgent) { $__q['do'][] = $__t; }
    elseif ($__high) { $__q['schedule'][] = $__t; }
    elseif ($__urgent) { $__q['delegate'][] = $__t; }
    else { $__q['drop'][] = $__t; }
}
$__titles = [
    'do' => ['فوری و مهم', 'همین امروز انجام بده', 'fa-fire'],
    'schedule' => ['مهم، غیرفوری', 'برای روزهای آینده برنامه‌ریزی کن', 'fa-calendar-check'],
    'delegate' => ['فوری، کم‌اهمیت', 'سریع ردش کن یا واگذارش کن', 'fa-bolt'],
    'drop' => ['نه فوری، نه مهم', 'بعداً، اگر وقت شد', 'fa-hourglass-half'],
];
?>
<div class="me-matrix-grid">
<?php foreach (['do', 'schedule', 'delegate', 'drop'] as $__k): ?>
    <section class="me-card me-matrix-cell me-matrix-<?= $__k ?>">
        <div class="me-section-head">
            <h3 class="me-section-title"><i class="fa-solid <?= $__titles[$__k][2] ?>"></i> <?= $__titles[$__k][0] ?></h3>
            <span class="me-section-action"><?= count($__q[$__k]) ?> کار</span>
        </div>
        <p class="me-matrix-hint"><?= $__titles[$__k][1] ?></p>
        <?php if ($__q[$__k] === []): ?>
            <p class="me-empty">کاری نیست.</p>
        <?php else: ?>
            <div class="me-board-cards">
            <?php foreach ($__q[$__k] as $__t): ?>
                <div class="me-board-card">
                    <span class="me-board-title"><?= e($__t['title'] ?? '') ?></span>
                    <span class="me-board-meta">
                        <?php if (!empty($__t['date'])): ?><span><?= e($__t['date']) ?></span><?php else: ?><span>بدون تاریخ</span><?php endif; ?>
                        <?php if (!empty($__t['duration'])): ?><span><?= (int) $__t['duration'] ?> دقیقه</span><?php endif; ?>
                    </span>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
<?php endforeach; ?>
</div>
