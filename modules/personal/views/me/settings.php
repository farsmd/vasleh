<div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
    <h2 class="font-bold text-base text-slate-800 mb-4">تنظیمات شخصی</h2>
    <form method="post" action="/me/settings" class="space-y-4 max-w-xl">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">نمای پیش‌فرض هنگام ورود به /me</label>
            <select name="default_view" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <?php $viewLabels = ['dashboard' => 'داشبورد و آمار', 'hourly' => 'تایم‌لاین ساعتی', 'daily' => 'روزانه', 'weekly' => 'هفتگی', 'monthly' => 'ماهانه']; ?>
                <?php foreach ($viewLabels as $k => $label): ?>
                <option value="<?= $k ?>" <?= $settings['default_view'] === $k ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">دسته‌بندی‌ها (هر خط: کلید انگلیسی | برچسب فارسی)</label>
            <textarea name="categories_text" rows="6" dir="ltr" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-left"><?= e($categories_text) ?></textarea>
            <p class="text-xs text-slate-400 mt-1">کلید فقط حروف کوچک انگلیسی، عدد، خط تیره و زیرخط. تسک‌های قدیمی با دستهٔ حذف‌شده، برچسب «عمومی» می‌گیرند ولی داده‌شان نمی‌پرد.</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">پومودورو: تمرکز (دقیقه)</label>
                <input type="number" name="pomo_focus" min="1" max="180" value="<?= (int) $settings['pomo_focus'] ?>" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">پومودورو: استراحت کوتاه (دقیقه)</label>
                <input type="number" name="pomo_break" min="1" max="60" value="<?= (int) $settings['pomo_break'] ?>" class="w-full bg-slate-50 border border-slate-200 text-sm rounded-xl px-3.5 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="dark_default" value="1" <?= !empty($settings['dark_default']) ? 'checked' : '' ?>>
            شروع با تم تیره (در غیر این صورت پیش‌فرض قالب ادمین)
        </label>
        <p><button class="px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md shadow-indigo-600/30">ذخیره تنظیمات</button></p>
    </form>
</div>
