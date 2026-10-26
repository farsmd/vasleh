<div class="space-y-6">
  <div class="glass-panel rounded-2xl p-6">
    <h1 class="text-xl font-black text-slate-900 dark:text-white">قالب ادمین</h1>
    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed">
      رنگ‌ها، گردی کارت‌ها، اندازه فونت و تم پیش‌فرض پنل را از همین‌جا عوض کنید. تغییر بلافاصله روی همه صفحه‌های ادمین اعمال می‌شود و از این نسخه، همین تنظیمات روی فرانت، فروشگاه و بخش شخصی هم می‌نشیند؛ دکمه تم در هدر همچنان انتخاب هر مرورگر را جداگانه نگه می‌دارد.
    </p>
  </div>

  <?php if (!empty($flash)): ?>
    <div class="flash"><?= e($flash) ?></div>
  <?php endif; ?>

  <form method="post" action="/admin/theme" class="space-y-6">
    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

    <div class="glass-panel rounded-2xl p-6">
      <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">رنگ‌ها</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
        <?php $colors = ['accent' => 'رنگ اصلی (دکمه‌ها و آیتم فعال)', 'panel_dark' => 'رنگ پنل‌ها در تم تیره', 'panel_light' => 'رنگ پنل‌ها در تم روشن', 'main_dark' => 'رنگ پس‌زمینه در تم تیره', 'main_light' => 'رنگ پس‌زمینه در تم روشن']; ?>
        <?php foreach ($colors as $key => $label): ?>
        <label class="block">
          <span class="text-slate-600 dark:text-slate-300 font-bold block mb-2"><?= e($label) ?></span>
          <span class="flex items-center gap-2">
            <input type="color" name="<?= e($key) ?>" value="<?= e($theme[$key]) ?>" class="w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-1 cursor-pointer">
            <span class="font-mono text-slate-500 dark:text-slate-400" dir="ltr"><?= e($theme[$key]) ?></span>
          </span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="glass-panel rounded-2xl p-6">
      <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">اندازه و تم</h3>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
        <label class="block">
          <span class="text-slate-600 dark:text-slate-300 font-bold block mb-2">گردی کارت‌ها</span>
          <select name="radius" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-800 dark:text-slate-200">
            <?php foreach ([8, 12, 16, 20, 24] as $r): ?>
            <option value="<?= $r ?>" <?= (int) $theme['radius'] === $r ? 'selected' : '' ?>><?= $r ?> پیکسل</option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="block">
          <span class="text-slate-600 dark:text-slate-300 font-bold block mb-2">اندازه پایه فونت</span>
          <select name="font_size" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-800 dark:text-slate-200">
            <?php foreach ([13, 14, 15, 16] as $f): ?>
            <option value="<?= $f ?>" <?= (int) $theme['font_size'] === $f ? 'selected' : '' ?>><?= $f ?> پیکسل</option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="block">
          <span class="text-slate-600 dark:text-slate-300 font-bold block mb-2">تم پیش‌فرض (مرورگر تازه)</span>
          <select name="default_theme" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2.5 text-slate-800 dark:text-slate-200">
            <option value="dark" <?= $theme['default_theme'] === 'dark' ? 'selected' : '' ?>>تیره</option>
            <option value="light" <?= $theme['default_theme'] === 'light' ? 'selected' : '' ?>>روشن</option>
          </select>
        </label>
      </div>
    </div>

    <div class="glass-panel rounded-2xl p-6">
      <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">پیش‌نمایش با تنظیمات ذخیره‌شده</h3>
      <div class="flex flex-wrap items-center gap-3 text-xs">
        <span class="px-4 py-2.5 rounded-xl text-white font-bold" style="background:var(--admin-accent)">دکمه اصلی</span>
        <span class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300" style="border-radius:var(--admin-radius)">نمونه کارت</span>
        <span class="text-slate-500 dark:text-slate-400">گردی و رنگ این نمونه‌ها از همان متغیرهای قالب می‌آیند.</span>
      </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
      <button type="submit" class="btn">ذخیره تنظیمات قالب</button>
      <button type="submit" name="action" value="reset" class="btn gray">بازنشانی به پیش‌فرض</button>
    </div>
  </form>
</div>
