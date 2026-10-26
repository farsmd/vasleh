<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <h2 class="font-bold text-base text-slate-800 mb-2">اشتراک‌گذاشته‌شده با من</h2>
    <p class="text-xs text-slate-400 mb-4">این آیتم‌ها را فقط می‌توانید ببینید؛ ویرایششان دست مالک است.</p>
    <?php if (empty($shared)): ?>
        <p class="text-xs text-slate-400">هنوز چیزی با شما به اشتراک گذاشته نشده است.</p>
    <?php else: ?>
        <div class="space-y-2">
        <?php foreach ($shared as $s): ?>
            <p class="text-sm"><a href="/me/shared/<?= e($s['item_type']) ?>/<?= (int) $s['item_id'] ?>" class="text-indigo-600 hover:underline font-medium"><?= e($s['title']) ?></a>
            <span class="text-xs text-slate-400"> · از <?= e($s['owner_name']) ?> · <?= $s['item_type'] === 'project' ? 'پروژه' : 'یادداشت' ?></span></p>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
