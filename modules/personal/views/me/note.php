<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <form method="post" action="/me/notes/<?= (int) $note['id'] ?>" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label class="block text-xs font-semibold text-slate-600 mb-1">عنوان</label>
        <input type="text" name="title" required value="<?= e($note['title']) ?>" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <label class="block text-xs font-semibold text-slate-600 mb-1">متن</label>
        <textarea name="body" rows="14" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500"><?= e($note['body']) ?></textarea>
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/30">ذخیره</button>
    </form>
    <form method="post" action="/me/notes/<?= (int) $note['id'] ?>/delete" onsubmit="return confirm('این یادداشت حذف شود؟')" class="mt-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700">حذف یادداشت</button>
    </form>
    <?= $shareBox ?>
</div>
