<?php
// آیین روزانه: صبحگاهی (برنامه‌ریزی ۳ دقیقه‌ای) + شبانه (مرور روز).
// داده: $today, $todayFa, $overdue, $todayOpen, $completedToday, $focusToday,
//       $workload, $capacity, $morningDone, $eveningDone, $journal, $csrf
$__fa = static fn ($n) => strtr((string) $n, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
$__today = (string) ($today ?? date('Y-m-d'));
$__tomorrow = date('Y-m-d', strtotime($__today . ' +1 day'));
$__overdue = (isset($overdue) && is_array($overdue)) ? $overdue : [];
$__open = (isset($todayOpen) && is_array($todayOpen)) ? $todayOpen : [];
$__doneN = (int) ($completedToday ?? 0);
$__focus = (int) ($focusToday ?? 0);
$__load = (int) ($workload ?? 0);
$__cap = (int) ($capacity ?? 480);
$__total = $__doneN + count($__open);
$__pct = $__total > 0 ? (int) round($__doneN * 100 / $__total) : 0;
$__moods = ['great' => ['عالی', 'fa-face-laugh-beam'], 'good' => ['خوب', 'fa-face-smile'], 'ok' => ['معمولی', 'fa-face-meh'], 'bad' => ['بد', 'fa-face-frown'], 'awful' => ['خیلی بد', 'fa-face-dizzy']];
$__curMood = (string) (($journal['mood'] ?? '') ?? '');
?>
<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-solid fa-sun"></i> آیین روزانه</h3>
        <span class="me-section-action"><?= e((string) ($todayFa ?? '')) ?></span>
    </div>
    <p class="me-muted" style="font-size:0.85rem">سه دقیقه صبح برای برنامه‌ریزی، سه دقیقه شب برای مرور؛ همین آیین ساده است که نرم‌افزار را از قبرستان کارهای عقب‌افتاده نجات می‌دهد.</p>
</div>

<!-- صبحگاهی -->
<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-solid fa-mug-saucer"></i> آیین صبحگاهی</h3>
        <?php if (!empty($morningDone)): ?><span class="me-badge"><i class="fa-solid fa-check"></i> انجام شد</span><?php endif; ?>
    </div>
    <?php if (!empty($morningDone)): ?>
        <p class="me-muted">برنامهٔ امروزتان مشخص است؛ روز پرباری داشته باشید.</p>
    <?php else: ?>
        <?php if ($__overdue !== []): ?>
        <h4 class="me-future-head"><i class="fa-solid fa-triangle-exclamation"></i> کارهای عقب‌افتاده (<?= $__fa(count($__overdue)) ?>)</h4>
        <?php foreach ($__overdue as $__t): ?>
        <div class="me-task-row">
            <div class="me-task-main">
                <div class="me-task-title"><?= e((string) ($__t['title'] ?? '')) ?></div>
                <div class="me-task-meta"><span><?= e(\Modules\Personal\Personal::faDate((string) ($__t['date'] ?? ''))) ?></span><?php if (!empty($__t['duration'])): ?><span>(<?= $__fa((int) $__t['duration']) ?> د)</span><?php endif; ?></div>
            </div>
            <div class="me-task-actions" style="display:flex;gap:6px;flex-wrap:wrap">
                <form method="post" action="/me/ritual/move"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="kind" value="<?= e((string) ($__t['kind'] ?? 'todo')) ?>"><input type="hidden" name="id" value="<?= (int) ($__t['id'] ?? 0) ?>"><input type="hidden" name="date" value="<?= e($__today) ?>"><button class="me-mini-btn">امروز</button></form>
                <form method="post" action="/me/ritual/move"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="kind" value="<?= e((string) ($__t['kind'] ?? 'todo')) ?>"><input type="hidden" name="id" value="<?= (int) ($__t['id'] ?? 0) ?>"><input type="hidden" name="date" value="<?= e($__tomorrow) ?>"><button class="me-mini-btn">فردا</button></form>
                <form method="post" action="/me/ritual/delete" onsubmit="return confirm('این کار حذف شود؟')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="kind" value="<?= e((string) ($__t['kind'] ?? 'todo')) ?>"><input type="hidden" name="id" value="<?= (int) ($__t['id'] ?? 0) ?>"><button class="me-mini-btn">حذف</button></form>
            </div>
        </div>
        <?php endforeach; ?>
        <form method="post" action="/me/rollover" style="margin:10px 0">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="back" value="/me/ritual">
            <button class="me-mini-btn"><i class="fa-solid fa-forward"></i> همه را به امروز منتقل کن</button>
        </form>
        <?php else: ?>
        <p class="me-muted"><i class="fa-solid fa-check"></i> کار عقب‌افتاده‌ای نیست؛ تمیز شروع می‌کنید.</p>
        <?php endif; ?>

        <h4 class="me-future-head"><i class="fa-solid fa-list-check"></i> برنامهٔ امروز (<?= $__fa(count($__open)) ?> کار باز)</h4>
        <p class="me-tasks-line<?= ($__cap > 0 && $__load > $__cap) ? ' me-line-warn' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i> بار امروز <?= $__fa($__load) ?> دقیقه
            <?php if ($__cap > 0): ?> از ظرفیت <?= $__fa($__cap) ?> دقیقه‌ای<?php if ($__load > $__cap): ?> — بیشتر از ظرفیت! چیزی را به فردا بدهید.<?php endif; ?><?php endif; ?>
        </p>

        <form method="post" action="/me/ritual/complete">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="kind" value="morning">
            <label class="me-field-label" for="ritIntention">نیت امروز (یک جمله؛ در ژورنال ثبت می‌شود)</label>
            <input type="text" id="ritIntention" name="intention" maxlength="200" placeholder="مثلاً: امروز فقط روی سه کار مهم تمرکز می‌کنم" class="me-note-input" style="width:100%;margin-bottom:10px">
            <button class="me-mini-btn"><i class="fa-solid fa-sun"></i> ثبت آیین صبحگاهی و شروع روز</button>
        </form>
    <?php endif; ?>
</div>

<!-- شبانه -->
<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-solid fa-moon"></i> مرور شبانه</h3>
        <?php if (!empty($eveningDone)): ?><span class="me-badge"><i class="fa-solid fa-check"></i> انجام شد</span><?php endif; ?>
    </div>
    <?php if (!empty($eveningDone)): ?>
        <p class="me-muted">مرور امشب ثبت شد؛ فردا با ذهن سبک شروع می‌کنید.</p>
    <?php else: ?>
        <div class="me-stat-grid">
            <div class="me-card me-stat-card"><span class="me-stat-num"><?= $__fa($__doneN) ?></span><span class="me-stat-label">کار انجام‌شده امروز</span></div>
            <div class="me-card me-stat-card"><span class="me-stat-num"><?= $__fa($__focus) ?></span><span class="me-stat-label">دقیقه تمرکز (پومودورو)</span></div>
            <div class="me-card me-stat-card"><span class="me-stat-num"><?= $__fa($__pct) ?>٪</span><span class="me-stat-label">درصد تکمیل روز</span></div>
        </div>

        <?php if ($__open !== []): ?>
        <h4 class="me-future-head"><i class="fa-solid fa-hourglass-half"></i> ناتمام‌های امروز (<?= $__fa(count($__open)) ?>)</h4>
        <?php foreach ($__open as $__t): ?>
        <div class="me-task-row">
            <div class="me-task-main"><div class="me-task-title"><?= e((string) ($__t['title'] ?? '')) ?></div></div>
            <div class="me-task-actions" style="display:flex;gap:6px">
                <form method="post" action="/me/ritual/move"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="kind" value="<?= e((string) ($__t['kind'] ?? 'todo')) ?>"><input type="hidden" name="id" value="<?= (int) ($__t['id'] ?? 0) ?>"><input type="hidden" name="date" value="<?= e($__tomorrow) ?>"><button class="me-mini-btn">فردا</button></form>
                <form method="post" action="/me/ritual/delete" onsubmit="return confirm('این کار حذف شود؟')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input type="hidden" name="kind" value="<?= e((string) ($__t['kind'] ?? 'todo')) ?>"><input type="hidden" name="id" value="<?= (int) ($__t['id'] ?? 0) ?>"><button class="me-mini-btn">حذف</button></form>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <h4 class="me-future-head"><i class="fa-solid fa-face-smile"></i> حال امروز چطور بود؟</h4>
        <div class="me-chip-row">
            <?php foreach ($__moods as $__mk => $__mv): ?>
            <form method="post" action="/me/ritual/mood" style="display:inline">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="mood" value="<?= e($__mk) ?>">
                <button type="submit" class="me-chip<?= $__curMood === $__mk ? ' active' : '' ?>"><i class="fa-solid <?= e($__mv[1]) ?>"></i> <?= e($__mv[0]) ?></button>
            </form>
            <?php endforeach; ?>
        </div>

        <form method="post" action="/me/ritual/complete" style="margin-top:12px">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="kind" value="evening">
            <button class="me-mini-btn"><i class="fa-solid fa-moon"></i> ثبت مرور شبانه و پایان روز</button>
        </form>
    <?php endif; ?>
</div>
