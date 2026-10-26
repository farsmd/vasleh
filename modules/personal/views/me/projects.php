<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <h2 class="font-bold text-base text-slate-800 mb-4">پروژه تازه</h2>
    <form method="post" action="/me/projects" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان پروژه</label>
        <input type="text" name="title" required placeholder="مثلاً راه‌اندازی فروشگاه" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <label class="block text-xs font-semibold text-slate-600 mb-1">توضیح (اختیاری)</label>
        <input type="text" name="description" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">شروع (اختیاری)</label>
                <input type="date" name="start_date" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">زمان مورد نیاز (ساعت)</label>
                <input type="number" name="hours_needed" min="0" value="0" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/30">ساختن پروژه</button>
    </form>
</div>
<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <h2 class="font-bold text-base text-slate-800 mb-4">پروژه‌ها</h2>
    <?php if (empty($projects)): ?>
        <p class="text-xs text-slate-400">هنوز پروژه‌ای نیست.</p>
    <?php else: ?>
        <div class="space-y-2">
        <?php foreach ($projects as $p): ?>
            <p class="text-sm"><a href="/me/projects/<?= (int) $p['id'] ?>" class="text-indigo-600 hover:underline font-medium"><?= e($p['title']) ?></a>
            <?php if ($p['start_date'] !== ''): ?><span class="text-xs text-slate-400"> · شروع <?= \Modules\Personal\Personal::bothDates($p['start_date']) ?></span><?php endif; ?>
            <?php if ((int) $p['hours_needed'] > 0): ?><span class="text-xs text-slate-400"> · <?= (int) $p['hours_needed'] ?> ساعت مورد نیاز</span><?php endif; ?></p>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
