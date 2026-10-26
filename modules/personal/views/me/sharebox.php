<div class="border-t-2 border-dashed border-slate-200 pt-4 mt-2">
    <h3 class="font-bold text-sm text-slate-700 mb-4">اشتراک‌گذاری <?= e($type_label) ?></h3>
    <form method="post" action="/me/share/user" class="flex flex-col sm:flex-row gap-3 mb-4">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="item_id" value="<?= (int) $item_id ?>">
        <input type="text" name="username" required placeholder="نام کاربری در وصله (فقط خواندنی)" class="flex-1 bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">اشتراک با کاربر</button>
    </form>
    <?php if (!empty($shares)): ?>
        <div class="space-y-2 mb-4">
        <?php foreach ($shares as $s): ?>
            <p class="text-sm"><?= e($s['username']) ?> <span class="text-[11px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-bold">فقط خواندنی</span>
            <form method="post" action="/me/share/user/<?= (int) $s['id'] ?>/delete" class="inline">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="type" value="<?= e($type) ?>">
                <input type="hidden" name="item_id" value="<?= (int) $item_id ?>">
                <button class="px-3 py-1 text-xs font-bold rounded-lg bg-rose-600 text-white">برداشتن دسترسی</button>
            </form></p>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="post" action="/me/share/link" class="mb-3">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="type" value="<?= e($type) ?>">
        <input type="hidden" name="item_id" value="<?= (int) $item_id ?>">
        <button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200">ساخت لینک عمومی تازه</button>
        <span class="text-xs text-slate-400">لینک قبلی باطل می‌شود.</span>
    </form>
    <?php if (!empty($links)): ?>
        <?php foreach ($links as $l): ?>
            <p class="text-sm">لینک عمومی: <code class="bg-slate-100 rounded px-2 py-1 text-xs" dir="ltr">/me/share/<?= e($l['token']) ?></code>
            <form method="post" action="/me/share/link/<?= (int) $l['id'] ?>/delete" class="inline">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="type" value="<?= e($type) ?>">
                <input type="hidden" name="item_id" value="<?= (int) $item_id ?>">
                <button class="px-3 py-1 text-xs font-bold rounded-lg bg-rose-600 text-white">باطل کردن</button>
            </form></p>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
