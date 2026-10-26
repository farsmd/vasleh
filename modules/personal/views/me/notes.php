<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <h2 class="font-bold text-base text-slate-800 mb-4">یادداشت تازه</h2>
    <form method="post" action="/me/notes" class="flex flex-col sm:flex-row gap-3">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="text" name="title" required placeholder="مثلاً ایده محصول" class="flex-1 bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/30">ساختن و نوشتن</button>
    </form>
</div>
<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <h2 class="font-bold text-base text-slate-800 mb-4">یادداشت‌ها</h2>
    <?php if (empty($notes)): ?>
        <p class="text-xs text-slate-400">هنوز یادداشتی نیست.</p>
    <?php else: ?>
        <div class="space-y-2">
        <?php foreach ($notes as $n): ?>
            <p class="text-sm"><a href="/me/notes/<?= (int) $n['id'] ?>" class="text-indigo-600 hover:underline font-medium"><?= e($n['title']) ?></a> <span class="text-xs text-slate-400"><?= e($n['updated_at']) ?></span></p>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
