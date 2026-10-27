<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <h2 class="font-bold text-base text-slate-800 mb-1">داشبورد شخصی</h2>
    <p class="text-xs text-slate-500 mb-4">امروز <?= \Modules\Personal\Personal::faWeekday($today) ?> <?= \Modules\Personal\Personal::faDate($today) ?> · هفته <?= \Modules\Personal\Personal::faDate($weekSat) ?> تا <?= \Modules\Personal\Personal::faDate($weekSun) ?></p>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
            <p class="text-xs text-slate-500">کارهای امروز</p>
            <p class="text-2xl font-extrabold text-slate-800"><?= (int) $todayDone ?> از <?= count($todayTasks) ?></p>
        </div>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
            <p class="text-xs text-slate-500">کارهای هفته</p>
            <p class="text-2xl font-extrabold text-slate-800"><?= (int) $weekDone ?> از <?= count($weekTasks) ?></p>
        </div>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
            <p class="text-xs text-slate-500">کارهای عقب‌افتاده</p>
            <p class="text-2xl font-extrabold text-rose-600"><?= count($overdue) ?></p>
        </div>
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
            <p class="text-xs text-slate-500">یادداشت‌ها</p>
            <p class="text-2xl font-extrabold text-slate-800"><?= (int) $notesCount ?></p>
        </div>
    </div>

    <?php $weekPct = count($weekTasks) > 0 ? (int) round(100 * $weekDone / count($weekTasks)) : 0; ?>
    <p class="text-xs font-semibold text-slate-600 mb-1">پیشرفت هفته: <?= $weekPct ?>٪</p>
    <div class="h-3 bg-slate-100 rounded-full overflow-hidden mb-6"><div class="h-full bg-indigo-600 rounded-full" style="width:<?= $weekPct ?>%"></div></div>

    <h3 class="font-bold text-sm text-slate-800 mb-2">رویدادهای امروز</h3>
    <?php if (empty($todayEvents)): ?>
        <p class="text-xs text-slate-400 mb-4">رویدادی برای امروز نیست.</p>
    <?php else: ?>
        <ul class="text-sm space-y-1 mb-4">
        <?php foreach ($todayEvents as $ev): ?>
            <li><?= e($ev['start_time']) ?> تا <?= e($ev['end_time']) ?> — <strong><?= e($ev['title']) ?></strong></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h3 class="font-bold text-sm text-slate-800 mb-2">کارهای امروز</h3>
    <?php if (empty($todayTasks)): ?>
        <p class="text-xs text-slate-400 mb-4">کاری برای امروز نیست.</p>
    <?php else: ?>
        <ul class="text-sm space-y-1 mb-4">
        <?php foreach ($todayTasks as $t): ?>
            <li><?= (int) ($t['done'] ?? 0) === 1 ? '✓' : '○' ?> <?= e($t['title']) ?> <span class="text-xs text-slate-400">(<?= e($t['category'] ?? '') ?>)</span></li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h3 class="font-bold text-sm text-slate-800 mb-2">پروژه‌ها</h3>
    <?php if (empty($projects)): ?>
        <p class="text-xs text-slate-400">پروژه‌ای نیست.</p>
    <?php else: ?>
        <div class="space-y-3">
        <?php foreach ($projects as $pr): ?>
            <?php $pct = $pr['needs_total'] > 0 ? (int) round(100 * $pr['needs_done'] / $pr['needs_total']) : 0; ?>
            <div class="border border-slate-100 rounded-xl p-3">
                <p class="text-sm font-semibold"><a href="/me/projects/<?= (int) $pr['id'] ?>" class="text-indigo-600 hover:underline"><?= e($pr['title']) ?></a> <span class="text-xs text-slate-400 font-normal"><?= (int) $pr['needs_done'] ?> از <?= (int) $pr['needs_total'] ?> نیاز · <?= $pct ?>٪</span></p>
                <?php if (!empty($pr['needs'])): ?>
                <ul class="text-xs text-slate-500 mt-1 space-y-0.5">
                    <?php foreach ($pr['needs'] as $n): ?>
                    <li><?= (int) ($n['done'] ?? 0) === 1 ? '✓' : '○' ?> <?= e($n['title']) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <p class="mt-4"><a href="/me" class="text-xs font-bold text-indigo-600 hover:underline">رفتن به تسک‌ها ←</a></p>
</div>
