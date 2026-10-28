<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-solid fa-gear"></i> تنظیمات</h3>
    </div>
    <form method="post" action="/me/settings">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

        <h4 class="me-settings-sub">تنظیمات استایل</h4>
        <div class="me-style-list">
            <?php $styleMeta = [
                'darkPrism' => ['منشور تاریک', 'شیشه مایع، عمق فضایی و نئون ملایم', '#818CF8'],
                'zenCanvas' => ['بوم آرام', 'مینیمالیسم گرم، کاغذی و بدون استرس', '#2D5A27'],
                'swissPop' => ['نئون ساختاریافته', 'نئوبروتالیسم، سایه سخت و کنتراست بالا', '#FF5E36'],
                'monoGrid' => ['ماتریس مهندسی', 'گرید صنعتی، خطوط دقیق و داده‌محور', '#FF4F00'],
            ]; ?>
            <?php foreach (\Modules\Personal\Personal::STYLES as $k => $label): ?>
            <?php [$sName, $sDesc, $sDot] = $styleMeta[$k]; ?>
            <label class="me-style-row">
                <input type="radio" name="style" value="<?= $k ?>" <?= ($settings['style'] ?? 'darkPrism') === $k ? 'checked' : '' ?>>
                <span class="me-theme-dot" style="background:<?= $sDot ?>"></span>
                <span class="me-style-text"><span class="me-style-name"><?= $sName ?></span><span class="me-style-desc"><?= $sDesc ?></span></span>
                <svg class="me-style-check" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </label>
            <?php endforeach; ?>
        </div>

        <h4 class="me-settings-sub">تنظیمات کاربری</h4>
        <div class="me-settings-field">
            <label>نمای پیش‌فرض هنگام ورود به /me</label>
            <select name="default_view">
                <?php $viewLabels = ['dashboard' => 'داشبورد و آمار', 'hourly' => 'تایم‌لاین ساعتی', 'daily' => 'روزانه', 'weekly' => 'هفتگی', 'monthly' => 'ماهانه']; ?>
                <?php foreach ($viewLabels as $k => $label): ?>
                <option value="<?= $k ?>" <?= $settings['default_view'] === $k ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="me-settings-field">
            <label>دسته‌بندی‌ها (هر خط: کلید انگلیسی | برچسب فارسی)</label>
            <textarea name="categories_text" rows="6" dir="ltr" style="text-align:left"><?= e($categories_text) ?></textarea>
            <p class="me-settings-hint">کلید فقط حروف کوچک انگلیسی، عدد، خط تیره و زیرخط. تسک‌های قدیمی با دستهٔ حذف‌شده، برچسب «عمومی» می‌گیرند ولی داده‌شان نمی‌پرد.</p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="me-settings-field">
                <label>پومودورو: تمرکز (دقیقه)</label>
                <input type="number" name="pomo_focus" min="1" max="180" value="<?= (int) $settings['pomo_focus'] ?>">
            </div>
            <div class="me-settings-field">
                <label>پومودورو: استراحت کوتاه (دقیقه)</label>
                <input type="number" name="pomo_break" min="1" max="60" value="<?= (int) $settings['pomo_break'] ?>">
            </div>
        </div>
        <label class="me-settings-check">
            <input type="checkbox" name="dark_default" value="1" <?= !empty($settings['dark_default']) ? 'checked' : '' ?>>
            شروع با تم تیره (در غیر این صورت پیش‌فرض قالب ادمین)
        </label>
        <p><button class="me-save-btn">ذخیره تنظیمات</button></p>
    </form>

    <h4 class="me-settings-sub">حساب کاربری</h4>
    <div class="me-account-row">
        <span>حساب کاربری: <?= e($user['username'] ?? '') ?></span>
        <button type="button" class="me-logout-btn" onclick="document.getElementById('meLogoutConfirm').style.display='block';this.style.display='none'">خروج از حساب کاربری</button>
    </div>
    <div id="meLogoutConfirm" class="me-logout-confirm" style="display:none">
        <p>آیا مطمئن هستی که می‌خواهی خارج شوی؟</p>
        <div class="me-btn-row">
            <form method="post" action="/admin/logout">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button class="me-logout-btn solid">خروج از حساب</button>
            </form>
            <button type="button" class="me-save-btn" onclick="document.getElementById('meLogoutConfirm').style.display='none'">لغو</button>
        </div>
    </div>
</div>
