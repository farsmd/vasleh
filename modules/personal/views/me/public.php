<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <p class="mb-2"><span class="text-[11px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">لینک عمومی</span> <span class="text-xs text-slate-400"><?= e($type_label) ?> از <?= e($owner) ?></span></p>
    <h2 class="font-bold text-lg text-slate-800 mb-2"><?= e($item['title']) ?></h2>
    <?php if ($type === 'project'): ?>
        <?php if (trim((string) $item['description']) !== ''): ?><p class="text-sm mb-2"><?= e($item['description']) ?></p><?php endif; ?>
        <p class="text-xs text-slate-400 mb-4">
            <?php if ($item['start_date'] !== ''): ?>شروع <?= \Modules\Personal\Personal::bothDates($item['start_date']) ?> · <?php endif; ?>
            <?php if ((int) $item['hours_needed'] > 0): ?><?= (int) $item['hours_needed'] ?> ساعت مورد نیاز<?php endif; ?>
        </p>
        <h3 class="font-bold text-sm mb-2">لیست نیازها</h3>
        <?php if (empty($needs)): ?>
            <p class="text-xs text-slate-400">نیازی ثبت نشده است.</p>
        <?php else: ?>
            <div class="space-y-2">
            <?php foreach ($needs as $n): ?>
                <p class="text-sm <?= (int) $n['done'] === 1 ? 'line-through text-slate-400' : '' ?>"><?= (int) $n['done'] === 1 ? '<i class="fa-solid fa-check text-emerald-500"></i> ' : '' ?><?= e($n['title']) ?></p>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-sm whitespace-pre-line"><?= e($item['body']) ?></p>
    <?php endif; ?>
</div>
