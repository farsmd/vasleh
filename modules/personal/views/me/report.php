<?php
// گزارش یک‌صفحه‌ای وضعیت پروژه — تمیز و چاپی.
// داده: $project, $needs, $milestones, $projectTasks, $progress, $actualHours, $daysLeft, $statuses, $todayFa
$__p = (isset($project) && is_array($project)) ? $project : [];
$__pr = (isset($progress) && is_array($progress)) ? $progress : ['pct' => 0, 'done' => 0, 'total' => 0];
$__ms = (isset($milestones) && is_array($milestones)) ? $milestones : [];
$__tasks = (isset($projectTasks) && is_array($projectTasks)) ? $projectTasks : [];
$__needs = (isset($needs) && is_array($needs)) ? $needs : [];
$__statuses = (isset($statuses) && is_array($statuses)) ? $statuses : [];
$__st = (string) ($__p['status'] ?? 'active');
$__dl = $daysLeft ?? null;
$__fa = static fn ($n) => strtr((string) $n, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
$__open = array_values(array_filter($__tasks, static fn ($t) => empty($t['done'])));
$__doneTasks = array_values(array_filter($__tasks, static fn ($t) => !empty($t['done'])));
?>
<style>
@media print { .me-dock, .no-print { display: none !important; } body { background: #fff !important; } }
.rep-sheet { background: #fff; color: #1c1b18; border-radius: 16px; padding: 28px; max-width: 800px; margin: 0 auto; }
.rep-sheet h1 { font-size: 1.4rem; margin-bottom: 4px; }
.rep-meta { color: #605c56; font-size: 0.85rem; margin-bottom: 16px; }
.rep-bar { height: 12px; background: #eee; border-radius: 999px; overflow: hidden; margin: 8px 0 16px; }
.rep-bar > div { height: 100%; background: #2d5a27; }
.rep-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 20px; }
.rep-stat { background: #f6f4f0; border-radius: 10px; padding: 10px; text-align: center; }
.rep-stat b { display: block; font-size: 1.2rem; }
.rep-stat span { font-size: 0.75rem; color: #605c56; }
.rep-sec { margin-bottom: 18px; }
.rep-sec h3 { font-size: 1rem; margin-bottom: 8px; border-bottom: 2px solid #2d5a27; padding-bottom: 4px; }
.rep-sec ul { margin: 0; padding-right: 18px; font-size: 0.9rem; }
.rep-sec li { margin-bottom: 4px; }
.rep-done { text-decoration: line-through; color: #8c8f87; }
</style>
<div class="rep-sheet">
    <h1><?= e((string) ($__p['title'] ?? 'پروژه')) ?></h1>
    <div class="rep-meta">
        وضعیت: <?= e($__statuses[$__st] ?? $__st) ?>
        <?php if (!empty($__p['start_date'])): ?> · شروع: <?= e(\Modules\Personal\Personal::faDate((string) $__p['start_date'])) ?><?php endif; ?>
        <?php if (!empty($__p['end_date'])): ?> · پایان: <?= e(\Modules\Personal\Personal::faDate((string) $__p['end_date'])) ?><?php endif; ?>
        <?php if ($__dl !== null): ?> · <?= $__dl < 0 ? $__fa(-$__dl) . ' روز عقب‌افتاده' : ($__dl === 0 ? 'ددلاین امروز' : $__fa($__dl) . ' روز مانده') ?><?php endif; ?>
        · گزارش <?= e((string) ($todayFa ?? '')) ?>
    </div>
    <?php if (!empty($__p['description'])): ?><p style="margin-bottom:14px"><?= nl2br(e((string) $__p['description'])) ?></p><?php endif; ?>

    <div class="rep-grid">
        <div class="rep-stat"><b><?= $__fa((int) ($__pr['pct'] ?? 0)) ?>٪</b><span>پیشرفت کل</span></div>
        <div class="rep-stat"><b><?= $__fa(count($__open)) ?></b><span>کار باز</span></div>
        <div class="rep-stat"><b><?= $__fa((int) ($__p['hours_needed'] ?? 0)) ?>h</b><span>برآورد</span></div>
        <div class="rep-stat"><b><?= $__fa((string) ($actualHours ?? 0)) ?>h</b><span>واقعی</span></div>
    </div>
    <div class="rep-bar"><div style="width:<?= (int) ($__pr['pct'] ?? 0) ?>%"></div></div>

    <?php if ($__ms !== []): ?>
    <div class="rep-sec"><h3>مایل‌استون‌ها</h3><ul>
        <?php foreach ($__ms as $__m): ?><li class="<?= !empty($__m['done']) ? 'rep-done' : '' ?>"><?= e((string) ($__m['title'] ?? '')) ?><?php if (!empty($__m['date'])): ?> — <?= e(\Modules\Personal\Personal::faDate((string) $__m['date'])) ?><?php endif; ?></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>

    <?php if ($__open !== []): ?>
    <div class="rep-sec"><h3>کارهای باز (<?= $__fa(count($__open)) ?>)</h3><ul>
        <?php foreach ($__open as $__t): ?><li><?= e((string) ($__t['title'] ?? '')) ?><?php if (!empty($__t['due_date'])): ?> — <?= e(\Modules\Personal\Personal::faDate((string) $__t['due_date'])) ?><?php endif; ?></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>

    <?php if ($__doneTasks !== []): ?>
    <div class="rep-sec"><h3>کارهای انجام‌شده (<?= $__fa(count($__doneTasks)) ?>)</h3><ul>
        <?php foreach ($__doneTasks as $__t): ?><li class="rep-done"><?= e((string) ($__t['title'] ?? '')) ?></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>

    <?php if ($__needs !== []): ?>
    <div class="rep-sec"><h3>نیازها</h3><ul>
        <?php foreach ($__needs as $__n): ?><li class="<?= !empty($__n['done']) ? 'rep-done' : '' ?>"><?= e((string) ($__n['title'] ?? '')) ?></li><?php endforeach; ?>
    </ul></div>
    <?php endif; ?>

    <div class="no-print" style="margin-top:16px;display:flex;gap:8px">
        <button class="me-mini-btn" onclick="window.print()"><i class="fa-solid fa-print"></i> چاپ / PDF</button>
        <a class="me-mini-btn" href="/me/projects/<?= (int) ($__p['id'] ?? 0) ?>">بازگشت به پروژه</a>
    </div>
</div>
