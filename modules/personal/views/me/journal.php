<?php
// ژورنال روزانه: ناوبری تاریخ + متن + حال‌وهوا با کلمه و آیکون FontAwesome (بدون ایموجی).
// داده: $date (Y-m-d) + $entry (body, mood) اگر برای آن روز ثبت شده باشد.
$__date = (string) ($date ?? date('Y-m-d'));
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $__date)) { $__date = date('Y-m-d'); }
$__prev = date('Y-m-d', strtotime($__date . ' -1 day'));
$__next = date('Y-m-d', strtotime($__date . ' +1 day'));
$__body = (string) ($entry['body'] ?? '');
$__mood = (string) ($entry['mood'] ?? '');
$__moods = [
    'great' => ['عالی', 'fa-face-laugh-beam'],
    'good' => ['خوب', 'fa-face-smile'],
    'ok' => ['معمولی', 'fa-face-meh'],
    'bad' => ['بد', 'fa-face-frown'],
    'awful' => ['افتضاح', 'fa-face-sad-tear'],
];
?>
<div class="me-card">
    <div class="me-section-head">
        <h3 class="me-section-title"><i class="fa-regular fa-book-open"></i> ژورنال <?= e($__date) ?></h3>
        <span class="me-section-action"><?= \Modules\Personal\Personal::faWeekday($__date) ?> <?= \Modules\Personal\Personal::faDate($__date) ?></span>
    </div>
    <div class="me-nav-row">
        <a href="/me/journal?date=<?= e($__prev) ?>" class="me-nav-btn">روز قبل</a>
        <a href="/me/journal?date=<?= e($__next) ?>" class="me-nav-btn">روز بعد</a>
    </div>
</div>
<div class="me-card">
    <form method="post" action="/me/journal" class="me-journal-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="date" value="<?= e($__date) ?>">
        <label class="me-field-label">حال امروز</label>
        <div class="me-mood-row">
            <?php foreach ($__moods as $__k => $__m): ?>
            <label class="me-mood-chip<?= $__mood === $__k ? ' selected' : '' ?>">
                <input type="radio" name="mood" value="<?= $__k ?>" <?= $__mood === $__k ? 'checked' : '' ?>>
                <i class="fa-regular <?= $__m[1] ?>"></i><span><?= $__m[0] ?></span>
            </label>
            <?php endforeach; ?>
        </div>
        <label class="me-field-label">نوشته روز</label>
        <textarea name="body" rows="10" placeholder="امروز چه گذشت..." class="me-textarea"><?= e($__body) ?></textarea>
        <button class="me-note-btn">ذخیره ژورنال</button>
    </form>
</div>
