<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <form method="post" action="/me/projects/<?= (int) $project['id'] ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان پروژه</label>
        <input type="text" name="title" required value="<?= e($project['title']) ?>" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <label class="block text-xs font-semibold text-slate-600 mb-1">توضیح</label>
        <input type="text" name="description" value="<?= e($project['description']) ?>" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">شروع</label>
                <input type="date" name="start_date" value="<?= e($project['start_date']) ?>" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">زمان مورد نیاز (ساعت)</label>
                <input type="number" name="hours_needed" min="0" value="<?= (int) $project['hours_needed'] ?>" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/30">ذخیره</button>
    </form>
    <form method="post" action="/me/projects/<?= (int) $project['id'] ?>/delete" onsubmit="return confirm('این پروژه با همه نیازهایش حذف شود؟')" class="mt-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700">حذف پروژه</button>
    </form>
</div>

<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <h2 class="font-bold text-base text-slate-800 mb-2">لیست نیازها</h2>
    <?php $done = count(array_filter($needs, static fn ($n) => (int) $n['done'] === 1)); ?>
    <p class="text-xs text-slate-400 mb-4"><?= $done ?> از <?= count($needs) ?> نیاز انجام شده</p>
    <?php if (empty($needs)): ?>
        <p class="text-xs text-slate-400">هنوز نیازی ثبت نشده است.</p>
    <?php else: ?>
        <div class="space-y-2 mb-4">
        <?php foreach ($needs as $n): ?>
            <div class="flex items-center gap-3 py-2 border-b border-slate-100">
                <form method="post" action="/me/projects/needs/<?= (int) $n['id'] ?>/toggle">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                    <input type="checkbox" <?= (int) $n['done'] === 1 ? 'checked' : '' ?> onchange="this.form.submit()" class="w-5 h-5">
                </form>
                <span class="text-sm flex-1 <?= (int) $n['done'] === 1 ? 'line-through text-slate-400' : '' ?>"><?= e($n['title']) ?></span>
                <form method="post" action="/me/projects/needs/<?= (int) $n['id'] ?>/delete" onsubmit="return confirm('این نیاز حذف شود؟')">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                    <button class="px-3 py-1 text-xs font-bold rounded-lg bg-rose-600 text-white">حذف</button>
                </form>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/me/projects/<?= (int) $project['id'] ?>/needs" class="flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="text" name="title" required placeholder="مثلاً خرید دامنه" class="flex-1 bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/30">افزودن نیاز</button>
    </form>
</div>

<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <?= $shareBox ?>
</div>
