<?php
declare(strict_types=1);

namespace Modules\Personal;

use Modules\Sqlite\Database;

/**
 * مدیریت شخصی: تودو، زمان‌بندی (رویداد با تاریخ و ساعت)، نوت و پروژه
 * (با لیست نیازها، تاریخ شروع و ساعت مورد نیاز).
 *
 * حریم خصوصی: همه ردیف‌ها user_id دارند و هر کوئری روی مالک فیلتر می‌شود.
 * اشتراک‌گذاری فقط برای پروژه و نوت است، به دو شکل: با کاربر دیگر وصله
 * (فقط خواندنی) و لینک عمومی فقط‌خواندنی. تودو و رویداد همیشه خصوصی‌اند.
 */
final class Personal
{
    /** نوع‌های قابل اشتراک‌گذاری. */
    public const SHAREABLE = ['project' => 'پروژه', 'note' => 'یادداشت'];

    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $pdo = $this->pdo();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_todos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                due_date TEXT NOT NULL DEFAULT \'\',
                done INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                date TEXT NOT NULL,
                start_time TEXT NOT NULL,
                end_time TEXT NOT NULL,
                note TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_notes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                body TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                description TEXT NOT NULL DEFAULT \'\',
                start_date TEXT NOT NULL DEFAULT \'\',
                hours_needed INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL,
                updated_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_needs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                done INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_shares (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item_type TEXT NOT NULL,
                item_id INTEGER NOT NULL,
                owner_id INTEGER NOT NULL,
                shared_user_id INTEGER NOT NULL,
                created_at TEXT NOT NULL,
                UNIQUE(item_type, item_id, shared_user_id)
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_links (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                item_type TEXT NOT NULL,
                item_id INTEGER NOT NULL,
                owner_id INTEGER NOT NULL,
                token TEXT NOT NULL UNIQUE,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_settings (
                user_id INTEGER PRIMARY KEY,
                settings TEXT NOT NULL DEFAULT \'{}\'
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_subtasks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                todo_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                done INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                name TEXT NOT NULL,
                UNIQUE(user_id, name)
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_todo_tags (
                todo_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                UNIQUE(todo_id, tag_id)
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_note_tags (
                note_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                UNIQUE(note_id, tag_id)
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_habits (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                target_per_week INTEGER NOT NULL DEFAULT 7,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_habit_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                habit_id INTEGER NOT NULL,
                date TEXT NOT NULL,
                UNIQUE(habit_id, date)
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_note_links (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                note_id INTEGER NOT NULL,
                item_type TEXT NOT NULL,
                item_id INTEGER NOT NULL,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_goals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                target_date TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_milestones (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                goal_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                done INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_journal (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                date TEXT NOT NULL,
                body TEXT NOT NULL DEFAULT \'\',
                mood TEXT NOT NULL DEFAULT \'\',
                UNIQUE(user_id, date)
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS pl_pomo_logs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                task_id INTEGER,
                started_at TEXT NOT NULL,
                minutes INTEGER NOT NULL DEFAULT 0
            )'
        );
        $this->migrateExtended();
    }

    // ---------- تنظیمات شخصی ----------

    public const DEFAULT_CATEGORIES = [
        ['key' => 'work', 'label' => 'کاری'],
        ['key' => 'personal', 'label' => 'شخصی'],
        ['key' => 'study', 'label' => 'یادگیری'],
        ['key' => 'health', 'label' => 'سلامت'],
    ];

    /** سبک‌های ظاهری /me (همان ۴ سبک ماکاپ مدیریت شخصی). */
    public const STYLES = [
        'darkPrism' => 'منشور تاریک',
        'zenCanvas' => 'بوم آرام',
        'swissPop' => 'نئون ساختاریافته',
        'monoGrid' => 'ماتریس مهندسی',
    ];

    /** تنظیمات کاربر؛ کلیدهای ناشناخته نادیده گرفته می‌شوند و مقادیر نرمال می‌شوند. */
    public function settings(int $userId): array
    {
        $stmt = $this->pdo()->prepare('SELECT settings FROM pl_settings WHERE user_id = :u');
        $stmt->execute(['u' => $userId]);
        $raw = $stmt->fetchColumn();
        $saved = $raw !== false ? (json_decode((string) $raw, true) ?: []) : [];
        $defaults = [
            'default_view' => 'dashboard',
            'categories' => self::DEFAULT_CATEGORIES,
            'pomo_focus' => 25,
            'pomo_break' => 5,
            'dark_default' => false,
            'style' => 'darkPrism',
            'saved_filters' => [],
            'day_capacity' => 480,
        ];
        $out = array_merge($defaults, array_intersect_key(is_array($saved) ? $saved : [], $defaults));
        $cats = [];
        if (is_array($out['categories'])) {
            foreach ($out['categories'] as $c) {
                if (is_array($c) && isset($c['key'], $c['label'])) {
                    $k = trim((string) $c['key']);
                    $l = trim((string) $c['label']);
                    if ($k !== '' && $l !== '' && !isset($cats[$k])) {
                        $cats[$k] = ['key' => $k, 'label' => $l];
                    }
                }
            }
        }
        $out['categories'] = array_values($cats) !== [] ? array_values($cats) : self::DEFAULT_CATEGORIES;
        $out['category_keys'] = array_column($out['categories'], 'key');
        $out['pomo_focus'] = max(1, min(180, (int) $out['pomo_focus']));
        $out['pomo_break'] = max(1, min(60, (int) $out['pomo_break']));
        $out['day_capacity'] = max(1, min(1440, (int) $out['day_capacity']));
        $filters = [];
        if (is_array($out['saved_filters'])) {
            foreach ($out['saved_filters'] as $f) {
                if (is_array($f)) {
                    $filters[] = [
                        'name' => (string) ($f['name'] ?? ''),
                        'tag' => (string) ($f['tag'] ?? ''),
                        'category' => (string) ($f['category'] ?? ''),
                        'priority' => (string) ($f['priority'] ?? ''),
                    ];
                }
            }
        }
        $out['saved_filters'] = $filters;
        $out['dark_default'] = !empty($out['dark_default']);
        if (!isset(self::STYLES[(string) ($out['style'] ?? '')])) {
            $out['style'] = 'darkPrism';
        }
        if (!in_array($out['default_view'], ['dashboard', 'hourly', 'daily', 'weekly', 'monthly'], true)) {
            $out['default_view'] = 'dashboard';
        }
        return $out;
    }

    public function settingsSave(int $userId, array $data): void
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_settings (user_id, settings) VALUES (:u, :s)
             ON CONFLICT(user_id) DO UPDATE SET settings = excluded.settings'
        );
        $stmt->execute(['u' => $userId, 's' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
    }

    /** هر خط «key|label»؛ کلیدهای نامعتبر نادیده گرفته می‌شوند. */
    public static function parseCategories(string $text): array
    {
        $out = [];
        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = explode('|', $line, 2);
            $key = trim($parts[0]);
            $label = trim($parts[1] ?? '');
            if (!preg_match('/^[a-z0-9_-]{1,20}$/', $key)) {
                continue;
            }
            if ($label === '') {
                $label = $key;
            }
            if (!isset($out[$key])) {
                $out[$key] = ['key' => $key, 'label' => $label];
            }
        }
        return array_values($out);
    }

    // ---------- اعتبارسنجی ----------

    public static function validDate(string $d): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
            return false;
        }
        [$y, $m, $day] = array_map('intval', explode('-', $d));
        return checkdate($m, $day, $y);
    }

    public static function validTime(string $t): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t);
    }

    /** دوشنبه هفته‌ای که تاریخ داده‌شده در آن است. */
    public static function weekMonday(string $date): string
    {
        $ts = strtotime($date);
        $n = (int) date('N', $ts); // 1=دوشنبه
        return date('Y-m-d', $ts - ($n - 1) * 86400);
    }

    public static function faWeekday(string $date): string
    {
        $names = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
        return $names[(int) date('w', strtotime($date))];
    }

    /** تبدیل میلادی به شمسی — الگوریتم استاندارد، بدون نیاز به افزونه. */
    public static function toJalali(int $gy, int $gm, int $gd): array
    {
        $gDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $jDays = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        $gy2 = ($gm > 2) ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + (int) (($gy2 + 3) / 4) - (int) (($gy2 + 99) / 100)
            + (int) (($gy2 + 399) / 400) + $gd;
        for ($i = 0; $i < $gm - 1; $i++) {
            $days += $gDays[$i];
        }
        $jy = -1595 + 33 * (int) ($days / 12053);
        $days %= 12053;
        $jy += 4 * (int) ($days / 1461);
        $days %= 1461;
        if ($days > 365) {
            $jy += (int) (($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $i = 0;
        for (; $i < 11 && $days >= $jDays[$i]; $i++) {
            $days -= $jDays[$i];
        }
        return [$jy, $i + 1, $days + 1];
    }

    /** تاریخ میلادی YYYY-MM-DD به شمسی با ارقام فارسی: ۱۴۰۵/۰۸/۰۴ */
    public static function faDate(string $date): string
    {
        if (!self::validDate($date)) {
            return $date;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $date));
        [$jy, $jm, $jd] = self::toJalali($y, $m, $d);
        $digits = ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹'];
        return strtr(sprintf('%d/%02d/%02d', $jy, $jm, $jd), $digits);
    }

    /** نمایش هر دو تقویم: ۱۴۰۵/۰۸/۰۴ · 2026-10-26 */
    public static function bothDates(string $date): string
    {
        if ($date === '' || !self::validDate($date)) {
            return $date;
        }
        return self::faDate($date) . ' · ' . $date;
    }

    public static function dayLabel(string $date): string
    {
        $today = date('Y-m-d');
        if ($date === $today) {
            return 'امروز';
        }
        if ($date === date('Y-m-d', strtotime($today . ' +1 day'))) {
            return 'فردا';
        }
        if ($date === date('Y-m-d', strtotime($today . ' -1 day'))) {
            return 'دیروز';
        }
        return self::faWeekday($date);
    }

    /** افزودن ستون‌های ناموجود (الگوی مهاجرت bare در cms). */
    private function ensureColumns(string $table, array $columns): void
    {
        $pdo = $this->pdo();
        $existing = [];
        foreach ($pdo->query("PRAGMA table_info({$table})")->fetchAll(\PDO::FETCH_ASSOC) as $col) {
            $existing[] = (string) $col['name'];
        }
        foreach ($columns as $name => $definition) {
            if (!in_array($name, $existing, true)) {
                $pdo->exec("ALTER TABLE {$table} ADD COLUMN {$name} {$definition}");
            }
        }
    }

    /** ستون‌های نمای تسک‌یار: دسته، اولویت، مدت و یادداشت + تیک رویداد. */
    public function migrateTaskyar(): void
    {
        $this->ensureColumns('pl_todos', [
            'category' => "TEXT NOT NULL DEFAULT 'personal'",
            'priority' => "TEXT NOT NULL DEFAULT 'medium'",
            'duration' => 'INTEGER NOT NULL DEFAULT 30',
            'note' => "TEXT NOT NULL DEFAULT ''",
        ]);
        $this->ensureColumns('pl_events', [
            'category' => "TEXT NOT NULL DEFAULT 'personal'",
            'priority' => "TEXT NOT NULL DEFAULT 'medium'",
            'duration' => 'INTEGER NOT NULL DEFAULT 30',
            'done' => 'INTEGER NOT NULL DEFAULT 0',
        ]);
        $this->migrateExtended();
    }

    /** ستون‌های توسعه‌یافته: تکرار، یادآوری، تاریخ شروع و لینک پروژه. */
    public function migrateExtended(): void
    {
        $this->ensureColumns('pl_todos', [
            'recur' => "TEXT NOT NULL DEFAULT 'none'",
            'recur_until' => "TEXT NOT NULL DEFAULT ''",
            'recur_mode' => "TEXT NOT NULL DEFAULT 'fixed'",
            'remind_at' => "TEXT NOT NULL DEFAULT ''",
            'start_date' => "TEXT NOT NULL DEFAULT ''",
            'project_id' => 'INTEGER NOT NULL DEFAULT 0',
        ]);
        $this->ensureColumns('pl_events', [
            'remind_at' => "TEXT NOT NULL DEFAULT ''",
        ]);
        $this->pdo()->exec(
            'CREATE TABLE IF NOT EXISTS pl_goal_tasks (
                goal_id INTEGER NOT NULL,
                todo_id INTEGER NOT NULL,
                UNIQUE(goal_id, todo_id)
            )'
        );
    }

    /** شنبه هفته‌ای که تاریخ داده‌شده در آن است (هفته ایرانی شنبه تا جمعه). */
    public static function weekSaturday(string $date): string
    {
        $uiDay = ((int) date('w', strtotime($date)) + 1) % 7; // 0=شنبه
        return date('Y-m-d', strtotime($date . " -{$uiDay} days"));
    }

    /** شاخص روز هفته در تقویم ایرانی: 0=شنبه … 6=جمعه. */
    public static function uiDay(string $date): int
    {
        return ((int) date('w', strtotime($date)) + 1) % 7;
    }

    /**
     * تسک‌های یکپارچه در بازه تاریخی: تودوها (تمام‌روز) + رویدادها (ساعت‌دار).
     * تودوهای بدون تاریخ هم همیشه برمی‌گردند (بخش «بدون تاریخ» در نما).
     *
     * @return list<array{kind:string,id:int,title:string,category:string,priority:string,date:string,day:int|null,hour:string,end:string,duration:int,note:string,done:int}>
     */
    public function tasksForRange(int $userId, string $from, string $to): array
    {
        $pdo = $this->pdo();
        $tasks = [];
        $stmt = $pdo->prepare(
            'SELECT * FROM pl_todos WHERE user_id = :u AND (due_date BETWEEN :a AND :b OR due_date = \'\')
             ORDER BY due_date, id'
        );
        $stmt->execute(['u' => $userId, 'a' => $from, 'b' => $to]);
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $t) {
            $date = (string) $t['due_date'];
            $todoId = (int) $t['id'];
            $tasks[] = [
                'kind' => 'todo', 'id' => $todoId, 'title' => (string) $t['title'],
                'category' => (string) ($t['category'] ?? 'personal'),
                'priority' => (string) ($t['priority'] ?? 'medium'),
                'date' => $date, 'day' => $date === '' ? null : self::uiDay($date),
                'hour' => 'all-day', 'end' => '',
                'duration' => (int) ($t['duration'] ?? 30), 'note' => (string) ($t['note'] ?? ''),
                'done' => (int) $t['done'],
                'subtasks' => $this->subtaskRows($todoId),
                'tags' => $this->todoTagNames($todoId),
                'start_date' => (string) ($t['start_date'] ?? ''),
                'remind_at' => (string) ($t['remind_at'] ?? ''),
                'project_id' => (int) ($t['project_id'] ?? 0),
            ];
        }
        $stmt = $pdo->prepare(
            'SELECT * FROM pl_events WHERE user_id = :u AND date BETWEEN :a AND :b ORDER BY date, start_time, id'
        );
        $stmt->execute(['u' => $userId, 'a' => $from, 'b' => $to]);
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $ev) {
            $date = (string) $ev['date'];
            $tasks[] = [
                'kind' => 'event', 'id' => (int) $ev['id'], 'title' => (string) $ev['title'],
                'category' => (string) ($ev['category'] ?? 'personal'),
                'priority' => (string) ($ev['priority'] ?? 'medium'),
                'date' => $date, 'day' => self::uiDay($date),
                'hour' => (string) $ev['start_time'], 'end' => (string) $ev['end_time'],
                'duration' => (int) ($ev['duration'] ?? 30), 'note' => (string) $ev['note'],
                'done' => (int) ($ev['done'] ?? 0),
                'remind_at' => (string) ($ev['remind_at'] ?? ''),
            ];
        }
        return $tasks;
    }

    public function taskByKind(int $userId, string $kind, int $id): ?array
    {
        $table = $kind === 'event' ? 'pl_events' : 'pl_todos';
        $stmt = $this->pdo()->prepare("SELECT * FROM {$table} WHERE id = :id AND user_id = :u");
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $date = (string) ($row['due_date'] ?? $row['date'] ?? '');
        return [
            'kind' => $kind, 'id' => (int) $row['id'], 'title' => (string) $row['title'],
            'category' => (string) ($row['category'] ?? 'personal'),
            'priority' => (string) ($row['priority'] ?? 'medium'),
            'date' => $date, 'day' => $date === '' ? null : self::uiDay($date),
            'hour' => $kind === 'event' ? (string) $row['start_time'] : 'all-day',
            'end' => $kind === 'event' ? (string) $row['end_time'] : '',
            'duration' => (int) ($row['duration'] ?? 30), 'note' => (string) ($row['note'] ?? ''),
            'done' => (int) $row['done'],
        ];
    }

    /**
     * ساخت تسک: ساعت all-day → تودو، وگرنه رویداد (پایان = شروع + مدت).
     *
     * @return array{kind:string,id:int}|null
     */
    public function taskCreate(int $userId, array $f): ?array
    {
        $title = trim((string) ($f['title'] ?? ''));
        $date = (string) ($f['date'] ?? '');
        $hour = (string) ($f['hour'] ?? 'all-day');
        if ($title === '' || !self::validDate($date)) {
            return null;
        }
        $allowed = $this->settings($userId)['category_keys'];
        $category = in_array($f['category'] ?? '', $allowed, true) ? (string) $f['category'] : ($allowed[0] ?? 'personal');
        $priority = in_array($f['priority'] ?? '', ['high', 'medium', 'low'], true) ? (string) $f['priority'] : 'medium';
        $duration = max(5, (int) ($f['duration'] ?? 30));
        $note = trim((string) ($f['note'] ?? ''));
        // فیلدهای افزودنی: فقط وقتی در ورودی آمده‌اند اعتبارسنجی می‌شوند؛
        // نبودنشان یعنی مقدار پیش‌فرض (برای سطر تازه) و در taskUpdate حفظ مقدار قبلی است.
        $extra = $this->todoExtraFields($userId, $f, null);
        if ($hour === 'all-day' || $hour === '' || !self::validTime($hour)) {
            $id = $this->todoCreate($userId, $title, $date);
            $this->pdo()->prepare(
                'UPDATE pl_todos SET category = :c, priority = :p, duration = :d, note = :n,
                 recur = :r, recur_until = :ru, recur_mode = :rm, remind_at = :ra,
                 start_date = :sd, project_id = :pj WHERE id = :id'
            )->execute([
                'c' => $category, 'p' => $priority, 'd' => $duration, 'n' => $note,
                'r' => $extra['recur'], 'ru' => $extra['recur_until'], 'rm' => $extra['recur_mode'],
                'ra' => $extra['remind_at'], 'sd' => $extra['start_date'], 'pj' => $extra['project_id'],
                'id' => $id,
            ]);
            return ['kind' => 'todo', 'id' => $id];
        }
        $end = date('H:i', min(strtotime($date . ' ' . $hour) + $duration * 60, strtotime($date . ' 23:59')));
        $id = $this->eventCreate($userId, $title, $date, $hour, $end, $note);
        $this->pdo()->prepare(
            'UPDATE pl_events SET category = :c, priority = :p, duration = :d, remind_at = :ra WHERE id = :id'
        )->execute(['c' => $category, 'p' => $priority, 'd' => $duration, 'ra' => $extra['remind_at'], 'id' => $id]);
        return ['kind' => 'event', 'id' => $id];
    }

    /**
     * ویرایش تسک؛ اگر نوع ساعت عوض شود (تمام‌روز ↔ ساعت‌دار) تسک تبدیل
     * می‌شود و هویت تازه برمی‌گردد.
     *
     * @return array{kind:string,id:int}|null
     */
    public function taskUpdate(int $userId, string $kind, int $id, array $f): ?array
    {
        $existing = $this->taskByKind($userId, $kind, $id);
        if ($existing === null) {
            return null;
        }
        $title = trim((string) ($f['title'] ?? ''));
        $date = (string) ($f['date'] ?? '');
        $hour = (string) ($f['hour'] ?? 'all-day');
        if ($title === '' || !self::validDate($date)) {
            return null;
        }
        $newKind = ($hour === 'all-day' || $hour === '' || !self::validTime($hour)) ? 'todo' : 'event';
        if ($newKind !== $kind) {
            $this->taskDelete($userId, $kind, $id);
            $created = $this->taskCreate($userId, $f);
            if ($created !== null && (int) $existing['done'] === 1) {
                $this->taskToggle($userId, $created['kind'], $created['id']);
            }
            return $created;
        }
        $allowed = $this->settings($userId)['category_keys'];
        $category = in_array($f['category'] ?? '', $allowed, true) ? (string) $f['category'] : ($allowed[0] ?? 'personal');
        $priority = in_array($f['priority'] ?? '', ['high', 'medium', 'low'], true) ? (string) $f['priority'] : 'medium';
        $duration = max(5, (int) ($f['duration'] ?? 30));
        $note = trim((string) ($f['note'] ?? ''));
        $pdo = $this->pdo();
        $rawStmt = $pdo->prepare(
            'SELECT * FROM ' . ($kind === 'event' ? 'pl_events' : 'pl_todos') . ' WHERE id = :id AND user_id = :u'
        );
        $rawStmt->execute(['id' => $id, 'u' => $userId]);
        $raw = $rawStmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        $extra = $this->todoExtraFields($userId, $f, $raw);
        if ($kind === 'event') {
            $end = date('H:i', min(strtotime($date . ' ' . $hour) + $duration * 60, strtotime($date . ' 23:59')));
            $stmt = $pdo->prepare(
                'UPDATE pl_events SET title = :t, date = :d, start_time = :s, end_time = :e, note = :n,
                 category = :c, priority = :p, duration = :du, remind_at = :ra WHERE id = :id AND user_id = :u'
            );
            $stmt->execute([
                't' => $title, 'd' => $date, 's' => $hour, 'e' => $end, 'n' => $note,
                'c' => $category, 'p' => $priority, 'du' => $duration, 'ra' => $extra['remind_at'],
                'id' => $id, 'u' => $userId,
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE pl_todos SET title = :t, due_date = :d, category = :c, priority = :p,
                 duration = :du, note = :n, recur = :r, recur_until = :ru, recur_mode = :rm,
                 remind_at = :ra, start_date = :sd, project_id = :pj WHERE id = :id AND user_id = :u'
            );
            $stmt->execute([
                't' => $title, 'd' => $date, 'c' => $category, 'p' => $priority,
                'du' => $duration, 'n' => $note, 'r' => $extra['recur'], 'ru' => $extra['recur_until'],
                'rm' => $extra['recur_mode'], 'ra' => $extra['remind_at'], 'sd' => $extra['start_date'],
                'pj' => $extra['project_id'], 'id' => $id, 'u' => $userId,
            ]);
        }
        return $this->taskByKind($userId, $kind, $id);
    }

    public function taskToggle(int $userId, string $kind, int $id): ?int
    {
        $table = $kind === 'event' ? 'pl_events' : 'pl_todos';
        $stmt = $this->pdo()->prepare("UPDATE {$table} SET done = 1 - done WHERE id = :id AND user_id = :u");
        $stmt->execute(['id' => $id, 'u' => $userId]);
        if ($stmt->rowCount() === 0) {
            return null;
        }
        $task = $this->taskByKind($userId, $kind, $id);
        if ($task === null) {
            return null;
        }
        if ($kind === 'todo' && (int) $task['done'] === 1) {
            $this->spawnRecurrence($userId, $id);
        }
        return (int) $task['done'];
    }

    /** اعتبارسنجی فیلدهای افزودنی تودو؛ $current=null یعنی سطر تازه (مقادیر پیش‌فرض). */
    private function todoExtraFields(int $userId, array $f, ?array $current): array
    {
        $out = [
            'recur' => (string) ($current['recur'] ?? 'none'),
            'recur_until' => (string) ($current['recur_until'] ?? ''),
            'recur_mode' => (string) ($current['recur_mode'] ?? 'fixed'),
            'remind_at' => (string) ($current['remind_at'] ?? ''),
            'start_date' => (string) ($current['start_date'] ?? ''),
            'project_id' => (int) ($current['project_id'] ?? 0),
        ];
        if (array_key_exists('recur', $f)) {
            $out['recur'] = in_array($f['recur'], ['daily', 'weekly', 'monthly'], true) ? (string) $f['recur'] : 'none';
        }
        if (array_key_exists('recur_until', $f)) {
            $out['recur_until'] = self::validDate((string) $f['recur_until']) ? (string) $f['recur_until'] : '';
        }
        if (array_key_exists('recur_mode', $f)) {
            $out['recur_mode'] = in_array($f['recur_mode'], ['fixed', 'after'], true) ? (string) $f['recur_mode'] : 'fixed';
        }
        if (array_key_exists('remind_at', $f)) {
            $out['remind_at'] = self::validTime((string) $f['remind_at']) ? (string) $f['remind_at'] : '';
        }
        if (array_key_exists('start_date', $f)) {
            $out['start_date'] = self::validDate((string) $f['start_date']) ? (string) $f['start_date'] : '';
        }
        if (array_key_exists('project_id', $f)) {
            $pid = (int) $f['project_id'];
            $out['project_id'] = ($pid > 0 && $this->project($userId, $pid) !== null) ? $pid : 0;
        }
        return $out;
    }

    /** تاریخ وقوع بعدی تکرار: fixed از تاریخ زمان‌بندی‌شده، after از روز تکمیل. */
    private static function nextRecurDate(string $scheduled, string $recur, string $mode, string $completedDay): ?string
    {
        $base = $mode === 'after' ? $completedDay : $scheduled;
        if (!self::validDate($base)) {
            return null;
        }
        if ($recur === 'daily') {
            return date('Y-m-d', strtotime($base . ' +1 day'));
        }
        if ($recur === 'weekly') {
            return date('Y-m-d', strtotime($base . ' +7 days'));
        }
        if ($recur === 'monthly') {
            $ts = strtotime($base);
            $y = (int) date('Y', $ts);
            $m = (int) date('n', $ts) + 1;
            if ($m > 12) {
                $m = 1;
                $y++;
            }
            $day = min((int) date('j', $ts), (int) date('t', mktime(0, 0, 0, $m, 1, $y)));
            return sprintf('%04d-%02d-%02d', $y, $m, $day);
        }
        return null;
    }

    /** ساخت وقوع بعدی تودوی تکرارشونده؛ زیرکارها کپی نمی‌شوند. */
    private function spawnRecurrenceFromRow(array $row, string $completedDay): void
    {
        $recur = (string) ($row['recur'] ?? 'none');
        if (!in_array($recur, ['daily', 'weekly', 'monthly'], true)) {
            return;
        }
        $mode = in_array($row['recur_mode'] ?? '', ['fixed', 'after'], true) ? (string) $row['recur_mode'] : 'fixed';
        $next = self::nextRecurDate((string) ($row['due_date'] ?? ''), $recur, $mode, $completedDay);
        if ($next === null) {
            return;
        }
        $until = (string) ($row['recur_until'] ?? '');
        if ($until !== '' && self::validDate($until) && $next > $until) {
            return;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_todos (user_id, title, due_date, category, priority, duration, note,
             recur, recur_until, recur_mode, remind_at, project_id, created_at)
             VALUES (:u, :t, :d, :c, :p, :du, :n, :r, :ru, :rm, :ra, :pj, :now)'
        );
        $stmt->execute([
            'u' => (int) $row['user_id'], 't' => (string) $row['title'], 'd' => $next,
            'c' => (string) ($row['category'] ?? 'personal'), 'p' => (string) ($row['priority'] ?? 'medium'),
            'du' => (int) ($row['duration'] ?? 30), 'n' => (string) ($row['note'] ?? ''),
            'r' => $recur, 'ru' => $until, 'rm' => $mode,
            'ra' => (string) ($row['remind_at'] ?? ''),
            'pj' => (int) ($row['project_id'] ?? 0), 'now' => date('Y-m-d H:i:s'),
        ]);
    }

    private function spawnRecurrence(int $userId, int $todoId): void
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_todos WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $todoId, 'u' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row !== false) {
            $this->spawnRecurrenceFromRow($row, date('Y-m-d'));
        }
    }

    public function taskDelete(int $userId, string $kind, int $id): void
    {
        if ($kind === 'event') {
            $this->eventDelete($userId, $id);
        } else {
            $this->todoDelete($userId, $id);
        }
    }

    // ---------- تودو ----------

    public function todos(int $userId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM pl_todos WHERE user_id = :u ORDER BY done ASC, (due_date = \'\') ASC, due_date ASC, id DESC'
        );
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function todoCreate(int $userId, string $title, string $dueDate): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_todos (user_id, title, due_date, created_at) VALUES (:u, :t, :d, :now)'
        );
        $stmt->execute(['u' => $userId, 't' => $title, 'd' => $dueDate, 'now' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function todoToggle(int $userId, int $id): bool
    {
        $stmt = $this->pdo()->prepare('UPDATE pl_todos SET done = 1 - done WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        if ($stmt->rowCount() === 0) {
            return false;
        }
        $rowStmt = $this->pdo()->prepare('SELECT * FROM pl_todos WHERE id = :id AND user_id = :u');
        $rowStmt->execute(['id' => $id, 'u' => $userId]);
        $row = $rowStmt->fetch(\PDO::FETCH_ASSOC);
        if ($row !== false && (int) $row['done'] === 1) {
            $this->spawnRecurrenceFromRow($row, date('Y-m-d'));
        }
        return true;
    }

    public function todoDelete(int $userId, int $id): void
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare('DELETE FROM pl_todos WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        if ($stmt->rowCount() === 0) {
            return;
        }
        // پاک‌سازی ردیف‌های وابسته تا زیرکار/برچسب/لینک هدفِ یتیم نماند.
        $pdo->prepare('DELETE FROM pl_subtasks WHERE todo_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM pl_todo_tags WHERE todo_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM pl_goal_tasks WHERE todo_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM pl_note_links WHERE item_type = \'todo\' AND item_id = :id')->execute(['id' => $id]);
    }

    // ---------- رویدادها ----------

    public function eventsForDay(int $userId, string $date): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM pl_events WHERE user_id = :u AND date = :d ORDER BY start_time, id'
        );
        $stmt->execute(['u' => $userId, 'd' => $date]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** رویدادهای ۷ روز هفته از دوشنبه داده‌شده، گروه‌بندی‌شده بر حسب تاریخ. */
    public function eventsForWeek(int $userId, string $monday): array
    {
        $sunday = date('Y-m-d', strtotime($monday . ' +6 days'));
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM pl_events WHERE user_id = :u AND date BETWEEN :a AND :b ORDER BY date, start_time, id'
        );
        $stmt->execute(['u' => $userId, 'a' => $monday, 'b' => $sunday]);
        $grouped = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $grouped[(string) $row['date']][] = $row;
        }
        return $grouped;
    }

    public function eventCreate(int $userId, string $title, string $date, string $start, string $end, string $note): int
    {
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_events (user_id, title, date, start_time, end_time, note, created_at)
             VALUES (:u, :t, :d, :s, :e, :n, :now)'
        );
        $stmt->execute([
            'u' => $userId, 't' => $title, 'd' => $date, 's' => $start, 'e' => $end,
            'n' => $note, 'now' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function eventDelete(int $userId, int $id): void
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare('DELETE FROM pl_events WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        if ($stmt->rowCount() > 0) {
            $pdo->prepare('DELETE FROM pl_note_links WHERE item_type = \'event\' AND item_id = :id')->execute(['id' => $id]);
        }
    }

    // ---------- نوت ----------

    public function notes(int $userId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM pl_notes WHERE user_id = :u ORDER BY updated_at DESC, id DESC'
        );
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function note(int $userId, int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_notes WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function noteCreate(int $userId, string $title): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_notes (user_id, title, created_at, updated_at) VALUES (:u, :t, :now, :now)'
        );
        $stmt->execute(['u' => $userId, 't' => $title, 'now' => $now]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function noteSave(int $userId, int $id, string $title, string $body): bool
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE pl_notes SET title = :t, body = :b, updated_at = :now WHERE id = :id AND user_id = :u'
        );
        $stmt->execute(['t' => $title, 'b' => $body, 'now' => date('Y-m-d H:i:s'), 'id' => $id, 'u' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function noteDelete(int $userId, int $id): void
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare('DELETE FROM pl_notes WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        if ($stmt->rowCount() === 0) {
            return;
        }
        $pdo->prepare('DELETE FROM pl_note_tags WHERE note_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM pl_note_links WHERE note_id = :id')->execute(['id' => $id]);
        $this->deleteItemShares('note', $id);
    }

    // ---------- پروژه ----------

    public function projects(int $userId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM pl_projects WHERE user_id = :u ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function project(int $userId, int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_projects WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function projectCreate(int $userId, string $title, string $description, string $startDate, int $hours): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_projects (user_id, title, description, start_date, hours_needed, created_at, updated_at)
             VALUES (:u, :t, :d, :s, :h, :now, :now)'
        );
        $stmt->execute(['u' => $userId, 't' => $title, 'd' => $description, 's' => $startDate, 'h' => $hours, 'now' => $now]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function projectSave(int $userId, int $id, string $title, string $description, string $startDate, int $hours): bool
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE pl_projects SET title = :t, description = :d, start_date = :s, hours_needed = :h, updated_at = :now
             WHERE id = :id AND user_id = :u'
        );
        $stmt->execute([
            't' => $title, 'd' => $description, 's' => $startDate, 'h' => $hours,
            'now' => date('Y-m-d H:i:s'), 'id' => $id, 'u' => $userId,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function projectDelete(int $userId, int $id): void
    {
        $pdo = $this->pdo();
        $stmt = $pdo->prepare('DELETE FROM pl_projects WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        if ($stmt->rowCount() === 0) {
            return;
        }
        $pdo->prepare('DELETE FROM pl_needs WHERE project_id = :id')->execute(['id' => $id]);
        $pdo->prepare('UPDATE pl_todos SET project_id = 0 WHERE user_id = :u AND project_id = :id')
            ->execute(['u' => $userId, 'id' => $id]);
        $pdo->prepare('DELETE FROM pl_note_links WHERE item_type = \'project\' AND item_id = :id')->execute(['id' => $id]);
        $this->deleteItemShares('project', $id);
    }

    public function needs(int $projectId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_needs WHERE project_id = :p ORDER BY id');
        $stmt->execute(['p' => $projectId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function needAdd(int $userId, int $projectId, string $title): bool
    {
        if ($this->project($userId, $projectId) === null) {
            return false;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_needs (project_id, title, created_at) VALUES (:p, :t, :now)'
        );
        $stmt->execute(['p' => $projectId, 't' => $title, 'now' => date('Y-m-d H:i:s')]);
        return true;
    }

    public function needToggle(int $userId, int $needId): bool
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE pl_needs SET done = 1 - done WHERE id = :id AND project_id IN
             (SELECT id FROM pl_projects WHERE user_id = :u)'
        );
        $stmt->execute(['id' => $needId, 'u' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function needDelete(int $userId, int $needId): void
    {
        $this->pdo()->prepare(
            'DELETE FROM pl_needs WHERE id = :id AND project_id IN
             (SELECT id FROM pl_projects WHERE user_id = :u)'
        )->execute(['id' => $needId, 'u' => $userId]);
    }

    // ---------- توسعه‌یافته: زیرکار، برچسب، جست‌وجو، عادت، هدف، ژورنال ----------

    private function subtaskRows(int $todoId): array
    {
        $stmt = $this->pdo()->prepare('SELECT id, title, done FROM pl_subtasks WHERE todo_id = :t ORDER BY id');
        $stmt->execute(['t' => $todoId]);
        $out = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $r) {
            $out[] = ['id' => (int) $r['id'], 'title' => (string) $r['title'], 'done' => (int) $r['done']];
        }
        return $out;
    }

    /** زیرکارهای یک تودو (بدون فیلتر مالک در امضا؛ فراخواننده مالکیت تودو را چک می‌کند). */
    public function subtasks(int $todoId): array
    {
        return $this->subtaskRows($todoId);
    }

    public function subtaskAdd(int $userId, int $todoId, string $title): ?int
    {
        $title = trim($title);
        if ($title === '') {
            return null;
        }
        $stmt = $this->pdo()->prepare('SELECT id FROM pl_todos WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $todoId, 'u' => $userId]);
        if ($stmt->fetchColumn() === false) {
            return null;
        }
        $ins = $this->pdo()->prepare(
            'INSERT INTO pl_subtasks (todo_id, title, created_at) VALUES (:t, :title, :now)'
        );
        $ins->execute(['t' => $todoId, 'title' => $title, 'now' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function subtaskToggle(int $userId, int $id): bool
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE pl_subtasks SET done = 1 - done WHERE id = :id AND todo_id IN
             (SELECT id FROM pl_todos WHERE user_id = :u)'
        );
        $stmt->execute(['id' => $id, 'u' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function subtaskDelete(int $userId, int $id): bool
    {
        $stmt = $this->pdo()->prepare(
            'DELETE FROM pl_subtasks WHERE id = :id AND todo_id IN
             (SELECT id FROM pl_todos WHERE user_id = :u)'
        );
        $stmt->execute(['id' => $id, 'u' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // ---------- برچسب‌ها ----------

    public function tags(int $userId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_tags WHERE user_id = :u ORDER BY name, id');
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function ensureTag(int $userId, string $name): int
    {
        $this->pdo()->prepare('INSERT OR IGNORE INTO pl_tags (user_id, name) VALUES (:u, :n)')
            ->execute(['u' => $userId, 'n' => $name]);
        $stmt = $this->pdo()->prepare('SELECT id FROM pl_tags WHERE user_id = :u AND name = :n');
        $stmt->execute(['u' => $userId, 'n' => $name]);
        return (int) $stmt->fetchColumn();
    }

    private function todoTagNames(int $todoId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT t.name FROM pl_tags t JOIN pl_todo_tags tt ON tt.tag_id = t.id WHERE tt.todo_id = :t ORDER BY t.name'
        );
        $stmt->execute(['t' => $todoId]);
        return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    private function noteTagNames(int $noteId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT t.name FROM pl_tags t JOIN pl_note_tags nt ON nt.tag_id = t.id WHERE nt.note_id = :n ORDER BY t.name'
        );
        $stmt->execute(['n' => $noteId]);
        return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    /** برچسب‌های تودو را با فهرست نام‌ها جایگزین می‌کند؛ تگ از جدول پاک نمی‌شود. */
    public function setTodoTags(int $userId, int $todoId, array $names): void
    {
        $stmt = $this->pdo()->prepare('SELECT id FROM pl_todos WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $todoId, 'u' => $userId]);
        if ($stmt->fetchColumn() === false) {
            return;
        }
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM pl_todo_tags WHERE todo_id = :t')->execute(['t' => $todoId]);
        $seen = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '' || isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;
            $pdo->prepare('INSERT OR IGNORE INTO pl_todo_tags (todo_id, tag_id) VALUES (:t, :g)')
                ->execute(['t' => $todoId, 'g' => $this->ensureTag($userId, $name)]);
        }
    }

    public function setNoteTags(int $userId, int $noteId, array $names): void
    {
        if ($this->note($userId, $noteId) === null) {
            return;
        }
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM pl_note_tags WHERE note_id = :n')->execute(['n' => $noteId]);
        $seen = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '' || isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;
            $pdo->prepare('INSERT OR IGNORE INTO pl_note_tags (note_id, tag_id) VALUES (:n, :g)')
                ->execute(['n' => $noteId, 'g' => $this->ensureTag($userId, $name)]);
        }
    }

    // ---------- تودوهای پروژه + چرت یادآوری ----------

    /** تودوهای یک پروژهٔ متعلق به کاربر؛ پروژهٔ کاربر دیگر فهرست خالی می‌دهد. */
    public function tasksForProject(int $userId, int $projectId): array
    {
        if ($this->project($userId, $projectId) === null) {
            return [];
        }
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM pl_todos WHERE user_id = :u AND project_id = :p
             ORDER BY done ASC, (due_date = \'\') ASC, due_date ASC, id DESC'
        );
        $stmt->execute(['u' => $userId, 'p' => $projectId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * چرت یادآوری: remind_at می‌شود «اکنون + minutes» (HH:MM).
     * توجه: ستون فقط ساعت دارد و تاریخ ندارد؛ اگر مجموع از نیمه‌شب بگذرد،
     * ساعت به بامداد روز بعد می‌چرخد (مثلاً ۲۳:۵۰ + ۳۰ دقیقه = ۰۰:۲۰) و
     * همان مقدار ذخیره می‌شود — یعنی در روزِ تنظیم، زمانی گذشته به نظر
     * می‌رسد و عملاً یادآوریِ صبح روز بعد است.
     */
    public function taskSnooze(int $userId, string $kind, int $id, int $minutes): bool
    {
        if ($minutes < 0) {
            return false;
        }
        $table = match ($kind) {
            'todo' => 'pl_todos',
            'event' => 'pl_events',
            default => null,
        };
        if ($table === null) {
            return false;
        }
        $nowMin = (int) date('H') * 60 + (int) date('i') + $minutes;
        $wrapped = $nowMin % 1440;
        $at = sprintf('%02d:%02d', intdiv($wrapped, 60), $wrapped % 60);
        $stmt = $this->pdo()->prepare("UPDATE {$table} SET remind_at = :ra WHERE id = :id AND user_id = :u");
        $stmt->execute(['ra' => $at, 'id' => $id, 'u' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // ---------- جست‌وجو، بار روز، انتقال ----------

    /** جست‌وجوی سراسری خصوصی (بدون اشتراک‌ها)؛ حداکثر ۵۰ نتیجه در مجموع. */
    public function search(int $userId, string $q): array
    {
        $out = ['todos' => [], 'events' => [], 'notes' => [], 'projects' => []];
        $q = trim($q);
        if ($q === '') {
            return $out;
        }
        $pdo = $this->pdo();
        $queries = [
            'todos' => 'SELECT * FROM pl_todos WHERE user_id = :u AND (instr(title, :q) > 0 OR instr(note, :q) > 0) ORDER BY id DESC LIMIT 50',
            'events' => 'SELECT * FROM pl_events WHERE user_id = :u AND (instr(title, :q) > 0 OR instr(note, :q) > 0) ORDER BY id DESC LIMIT 50',
            'notes' => 'SELECT * FROM pl_notes WHERE user_id = :u AND (instr(title, :q) > 0 OR instr(body, :q) > 0) ORDER BY id DESC LIMIT 50',
            'projects' => 'SELECT * FROM pl_projects WHERE user_id = :u AND (instr(title, :q) > 0 OR instr(description, :q) > 0) ORDER BY id DESC LIMIT 50',
        ];
        $total = 0;
        foreach ($queries as $key => $sql) {
            if ($total >= 50) {
                break;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['u' => $userId, 'q' => $q]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $out[$key] = array_slice($rows, 0, 50 - $total);
            $total += count($out[$key]);
        }
        return $out;
    }

    /** مجموع دقیقهٔ تودوها + رویدادهای یک روز (صرف‌نظر از انجام‌شده بودن). */
    public function workloadForDay(int $userId, string $date): int
    {
        $pdo = $this->pdo();
        $sum = 0;
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(duration), 0) FROM pl_todos WHERE user_id = :u AND due_date = :d');
        $stmt->execute(['u' => $userId, 'd' => $date]);
        $sum += (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(duration), 0) FROM pl_events WHERE user_id = :u AND date = :d');
        $stmt->execute(['u' => $userId, 'd' => $date]);
        $sum += (int) $stmt->fetchColumn();
        return $sum;
    }

    /** فقط تودوهای تاریخ‌دارِ انجام‌نشده با تاریخ ≤ fromDate به toDate می‌روند؛ رویداد هرگز. */
    public function rollover(int $userId, string $fromDate, string $toDate): int
    {
        if (!self::validDate($fromDate) || !self::validDate($toDate)) {
            return 0;
        }
        $stmt = $this->pdo()->prepare(
            'UPDATE pl_todos SET due_date = :to WHERE user_id = :u AND done = 0 AND due_date <> \'\' AND due_date <= :from'
        );
        $stmt->execute(['to' => $toDate, 'u' => $userId, 'from' => $fromDate]);
        return $stmt->rowCount();
    }

    // ---------- عادت‌ها ----------

    public function habits(int $userId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_habits WHERE user_id = :u ORDER BY id');
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function habitOwned(int $userId, int $habitId): bool
    {
        $stmt = $this->pdo()->prepare('SELECT id FROM pl_habits WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $habitId, 'u' => $userId]);
        return $stmt->fetchColumn() !== false;
    }

    private function habitLogDates(int $habitId): array
    {
        $stmt = $this->pdo()->prepare('SELECT date FROM pl_habit_logs WHERE habit_id = :h ORDER BY date');
        $stmt->execute(['h' => $habitId]);
        return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    public function habitCreate(int $userId, string $title, int $target): int
    {
        $title = trim($title);
        if ($title === '') {
            return 0;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_habits (user_id, title, target_per_week, created_at) VALUES (:u, :t, :g, :now)'
        );
        $stmt->execute(['u' => $userId, 't' => $title, 'g' => max(1, min(7, $target)), 'now' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function habitToggle(int $userId, int $id, string $date): bool
    {
        if (!$this->habitOwned($userId, $id) || !self::validDate($date)) {
            return false;
        }
        $pdo = $this->pdo();
        $stmt = $pdo->prepare('SELECT id FROM pl_habit_logs WHERE habit_id = :h AND date = :d');
        $stmt->execute(['h' => $id, 'd' => $date]);
        if ($stmt->fetchColumn() !== false) {
            $pdo->prepare('DELETE FROM pl_habit_logs WHERE habit_id = :h AND date = :d')->execute(['h' => $id, 'd' => $date]);
        } else {
            $pdo->prepare('INSERT INTO pl_habit_logs (habit_id, date) VALUES (:h, :d)')->execute(['h' => $id, 'd' => $date]);
        }
        return true;
    }

    public function habitDelete(int $userId, int $id): bool
    {
        if (!$this->habitOwned($userId, $id)) {
            return false;
        }
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM pl_habit_logs WHERE habit_id = :h')->execute(['h' => $id]);
        $pdo->prepare('DELETE FROM pl_habits WHERE id = :id')->execute(['id' => $id]);
        return true;
    }

    /**
     * استریک بخشنده: رو به عقب از امروز می‌رویم و روزهای تیک‌خورده شمرده
     * می‌شوند؛ یک روزِ تکیِ ازدست‌رفته پل می‌شود، ولی دو روز پیاپیِ
     * ازدست‌رفته زنجیره را می‌شکند (استریک صفر نمی‌شود مگر همین اتفاق
     * در انتهای بازه بیفتد). امروزِ هنوز تیک‌نخورده هم یک پل حساب می‌شود،
     * نه شکست؛ ولی امروز + دیروزِ هر دو خالی یعنی صفر.
     */
    public function habitStreak(int $userId, int $habitId): int
    {
        if (!$this->habitOwned($userId, $habitId)) {
            return 0;
        }
        $set = array_flip($this->habitLogDates($habitId));
        $cursor = date('Y-m-d');
        $misses = 0;
        $count = 0;
        for ($i = 0; $i < 3650; $i++) {
            if (isset($set[$cursor])) {
                $count++;
                $misses = 0;
            } else {
                $misses++;
                if ($misses >= 2) {
                    break;
                }
            }
            $cursor = date('Y-m-d', strtotime($cursor . ' -1 day'));
        }
        return $count;
    }

    /** رکورد: بیشترین تعداد روزهای تیک‌خورده در یک زنجیره (با همان پلِ تک‌روزه). */
    public function habitRecordStreak(int $userId, int $habitId): int
    {
        if (!$this->habitOwned($userId, $habitId)) {
            return 0;
        }
        $today = date('Y-m-d');
        $best = 0;
        $current = 0;
        $prev = null;
        foreach ($this->habitLogDates($habitId) as $d) {
            if ($d > $today) {
                continue;
            }
            if ($prev === null) {
                $current = 1;
            } else {
                $gap = (int) ((strtotime($d) - strtotime($prev)) / 86400);
                if ($gap <= 2) {
                    $current++;
                } else {
                    $best = max($best, $current);
                    $current = 1;
                }
            }
            $prev = $d;
        }
        return max($best, $current);
    }

    /** تاریخ‌های تیک‌خوردهٔ ۱۲ هفتهٔ اخیر (امروز و ۸۳ روز قبل از آن). */
    public function habitHeatmap(int $userId, int $habitId): array
    {
        if (!$this->habitOwned($userId, $habitId)) {
            return [];
        }
        $from = date('Y-m-d', strtotime('-83 days'));
        $stmt = $this->pdo()->prepare(
            'SELECT date FROM pl_habit_logs WHERE habit_id = :h AND date BETWEEN :a AND :b ORDER BY date'
        );
        $stmt->execute(['h' => $habitId, 'a' => $from, 'b' => date('Y-m-d')]);
        return array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
    }

    // ---------- لینک‌های نوت ----------

    public function noteLinks(int $userId, int $noteId): array
    {
        if ($this->note($userId, $noteId) === null) {
            return [];
        }
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_note_links WHERE note_id = :n ORDER BY id');
        $stmt->execute(['n' => $noteId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $table = ['todo' => 'pl_todos', 'event' => 'pl_events', 'project' => 'pl_projects'];
        foreach ($rows as &$row) {
            $title = '';
            $t = $table[(string) $row['item_type']] ?? null;
            if ($t !== null) {
                $s = $this->pdo()->prepare("SELECT title FROM {$t} WHERE id = :id AND user_id = :u");
                $s->execute(['id' => (int) $row['item_id'], 'u' => $userId]);
                $title = (string) ($s->fetchColumn() ?: '');
            }
            $row['item_title'] = $title;
        }
        unset($row);
        return $rows;
    }

    /** لینک نوت به تودو/رویداد/پروژهٔ متعلق به کاربر؛ لینک تکراری بی‌اثر و موفق است. */
    public function noteLinkAdd(int $userId, int $noteId, string $type, int $itemId): bool
    {
        $table = match ($type) {
            'todo' => 'pl_todos',
            'event' => 'pl_events',
            'project' => 'pl_projects',
            default => null,
        };
        if ($table === null || $this->note($userId, $noteId) === null) {
            return false;
        }
        $stmt = $this->pdo()->prepare("SELECT id FROM {$table} WHERE id = :id AND user_id = :u");
        $stmt->execute(['id' => $itemId, 'u' => $userId]);
        if ($stmt->fetchColumn() === false) {
            return false;
        }
        $pdo = $this->pdo();
        $exists = $pdo->prepare(
            'SELECT id FROM pl_note_links WHERE note_id = :n AND item_type = :t AND item_id = :i'
        );
        $exists->execute(['n' => $noteId, 't' => $type, 'i' => $itemId]);
        if ($exists->fetchColumn() !== false) {
            return true;
        }
        $pdo->prepare(
            'INSERT INTO pl_note_links (note_id, item_type, item_id, created_at) VALUES (:n, :t, :i, :now)'
        )->execute(['n' => $noteId, 't' => $type, 'i' => $itemId, 'now' => date('Y-m-d H:i:s')]);
        return true;
    }

    public function noteLinkDelete(int $userId, int $linkId): bool
    {
        $stmt = $this->pdo()->prepare(
            'DELETE FROM pl_note_links WHERE id = :id AND note_id IN (SELECT id FROM pl_notes WHERE user_id = :u)'
        );
        $stmt->execute(['id' => $linkId, 'u' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // ---------- مرور هفتگی + پومودورو ----------

    /**
     * آمار هفتهٔ شنبه‌محور: تودوهای تاریخ‌دارِ آن هفته، رویدادها، پروژه‌های
     * در حال پیشرفت (نیاز ناتمام دارند)، استریک عادت‌ها، پومودوروهای هفته،
     * و تودوهای ناتمامِ مانده از قبلِ هفته (overdue).
     */
    public function reviewStats(int $userId, string $weekSat): array
    {
        if (!self::validDate($weekSat)) {
            $weekSat = self::weekSaturday(date('Y-m-d'));
        }
        $weekEnd = date('Y-m-d', strtotime($weekSat . ' +6 days'));
        $pdo = $this->pdo();
        $count = static function (string $sql, array $params) use ($pdo): int {
            $s = $pdo->prepare($sql);
            $s->execute($params);
            return (int) $s->fetchColumn();
        };
        $todosTotal = $count(
            'SELECT COUNT(*) FROM pl_todos WHERE user_id = :u AND due_date BETWEEN :a AND :b',
            ['u' => $userId, 'a' => $weekSat, 'b' => $weekEnd]
        );
        $todosDone = $count(
            'SELECT COUNT(*) FROM pl_todos WHERE user_id = :u AND done = 1 AND due_date BETWEEN :a AND :b',
            ['u' => $userId, 'a' => $weekSat, 'b' => $weekEnd]
        );
        $eventsTotal = $count(
            'SELECT COUNT(*) FROM pl_events WHERE user_id = :u AND date BETWEEN :a AND :b',
            ['u' => $userId, 'a' => $weekSat, 'b' => $weekEnd]
        );
        $eventsDone = $count(
            'SELECT COUNT(*) FROM pl_events WHERE user_id = :u AND done = 1 AND date BETWEEN :a AND :b',
            ['u' => $userId, 'a' => $weekSat, 'b' => $weekEnd]
        );
        $inProgress = 0;
        foreach ($this->projects($userId) as $p) {
            $needs = $this->needs((int) $p['id']);
            $done = 0;
            foreach ($needs as $n) {
                $done += (int) $n['done'];
            }
            if ($needs !== [] && $done < count($needs)) {
                $inProgress++;
            }
        }
        $streaks = [];
        foreach ($this->habits($userId) as $h) {
            $streaks[] = ['id' => (int) $h['id'], 'title' => (string) $h['title'], 'streak' => $this->habitStreak($userId, (int) $h['id'])];
        }
        $pomo = $pdo->prepare(
            'SELECT COUNT(*), COALESCE(SUM(minutes), 0) FROM pl_pomo_logs
             WHERE user_id = :u AND started_at BETWEEN :a AND :b'
        );
        $pomo->execute(['u' => $userId, 'a' => $weekSat . ' 00:00:00', 'b' => $weekEnd . ' 23:59:59']);
        [$pomoSessions, $pomoMinutes] = $pomo->fetch(\PDO::FETCH_NUM);
        $overdue = $count(
            'SELECT COUNT(*) FROM pl_todos WHERE user_id = :u AND done = 0 AND due_date <> \'\' AND due_date < :a',
            ['u' => $userId, 'a' => $weekSat]
        );
        return [
            'week_start' => $weekSat, 'week_end' => $weekEnd,
            'todos_total' => $todosTotal, 'todos_done' => $todosDone,
            'todos_percent' => $todosTotal > 0 ? (int) round($todosDone * 100 / $todosTotal) : 0,
            'events_total' => $eventsTotal, 'events_done' => $eventsDone,
            'projects_in_progress' => $inProgress,
            'habit_streaks' => $streaks,
            'pomo_sessions' => (int) $pomoSessions, 'pomo_minutes' => (int) $pomoMinutes,
            'incomplete_todos' => $todosTotal - $todosDone, 'overdue' => $overdue,
        ];
    }

    /** ثبت نشست پومودورو؛ task_id فقط وقتی ذخیره می‌شود که تودوی همان کاربر باشد. */
    public function pomoLog(int $userId, int $minutes, ?int $taskId): void
    {
        if ($minutes < 1) {
            return;
        }
        $task = null;
        if ($taskId !== null && $taskId > 0) {
            $stmt = $this->pdo()->prepare('SELECT id FROM pl_todos WHERE id = :id AND user_id = :u');
            $stmt->execute(['id' => $taskId, 'u' => $userId]);
            $task = $stmt->fetchColumn() !== false ? $taskId : null;
        }
        $this->pdo()->prepare(
            'INSERT INTO pl_pomo_logs (user_id, task_id, started_at, minutes) VALUES (:u, :t, :now, :m)'
        )->execute(['u' => $userId, 't' => $task, 'now' => date('Y-m-d H:i:s'), 'm' => $minutes]);
    }

    /** مجموع دقیقه‌های پومودورو در بازهٔ تاریخی (شامل روزها). */
    public function pomoMinutesForRange(int $userId, string $from, string $to): int
    {
        $stmt = $this->pdo()->prepare(
            'SELECT COALESCE(SUM(minutes), 0) FROM pl_pomo_logs
             WHERE user_id = :u AND substr(started_at, 1, 10) BETWEEN :a AND :b'
        );
        $stmt->execute(['u' => $userId, 'a' => $from, 'b' => $to]);
        return (int) $stmt->fetchColumn();
    }

    /** تسک‌های لینک‌شده به یک هدف (فقط هدفِ مالک). */
    public function goalTasks(int $userId, int $goalId): array
    {
        if ($this->goal($userId, $goalId) === null) {
            return [];
        }
        $stmt = $this->pdo()->prepare(
            'SELECT t.* FROM pl_goal_tasks gt JOIN pl_todos t ON t.id = gt.todo_id
             WHERE gt.goal_id = :g AND t.user_id = :u ORDER BY t.due_date, t.id'
        );
        $stmt->execute(['g' => $goalId, 'u' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ---------- اهداف + مایل‌استون + تسک‌های لینک‌شده ----------

    public function goals(int $userId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM pl_goals WHERE user_id = :u ORDER BY created_at DESC, id DESC'
        );
        $stmt->execute(['u' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function goal(int $userId, int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_goals WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function goalCreate(int $userId, string $title, string $targetDate): int
    {
        $title = trim($title);
        if ($title === '') {
            return 0;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_goals (user_id, title, target_date, created_at) VALUES (:u, :t, :d, :now)'
        );
        $stmt->execute([
            'u' => $userId, 't' => $title,
            'd' => self::validDate($targetDate) ? $targetDate : '',
            'now' => date('Y-m-d H:i:s'),
        ]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function goalDelete(int $userId, int $id): bool
    {
        if ($this->goal($userId, $id) === null) {
            return false;
        }
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM pl_milestones WHERE goal_id = :g')->execute(['g' => $id]);
        $pdo->prepare('DELETE FROM pl_goal_tasks WHERE goal_id = :g')->execute(['g' => $id]);
        $pdo->prepare('DELETE FROM pl_goals WHERE id = :id AND user_id = :u')->execute(['id' => $id, 'u' => $userId]);
        return true;
    }

    /** مایل‌استون‌های یک هدف (بدون فیلتر مالک در امضا، مثل needs()). */
    public function milestones(int $goalId): array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_milestones WHERE goal_id = :g ORDER BY id');
        $stmt->execute(['g' => $goalId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function milestoneAdd(int $userId, int $goalId, string $title): ?int
    {
        $title = trim($title);
        if ($title === '' || $this->goal($userId, $goalId) === null) {
            return null;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_milestones (goal_id, title, created_at) VALUES (:g, :t, :now)'
        );
        $stmt->execute(['g' => $goalId, 't' => $title, 'now' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function milestoneToggle(int $userId, int $id): bool
    {
        $stmt = $this->pdo()->prepare(
            'UPDATE pl_milestones SET done = 1 - done WHERE id = :id AND goal_id IN
             (SELECT id FROM pl_goals WHERE user_id = :u)'
        );
        $stmt->execute(['id' => $id, 'u' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function milestoneDelete(int $userId, int $id): bool
    {
        $stmt = $this->pdo()->prepare(
            'DELETE FROM pl_milestones WHERE id = :id AND goal_id IN
             (SELECT id FROM pl_goals WHERE user_id = :u)'
        );
        $stmt->execute(['id' => $id, 'u' => $userId]);
        return $stmt->rowCount() > 0;
    }

    public function goalLinkTask(int $userId, int $goalId, int $todoId): bool
    {
        if ($this->goal($userId, $goalId) === null) {
            return false;
        }
        $stmt = $this->pdo()->prepare('SELECT id FROM pl_todos WHERE id = :id AND user_id = :u');
        $stmt->execute(['id' => $todoId, 'u' => $userId]);
        if ($stmt->fetchColumn() === false) {
            return false;
        }
        $this->pdo()->prepare('INSERT OR IGNORE INTO pl_goal_tasks (goal_id, todo_id) VALUES (:g, :t)')
            ->execute(['g' => $goalId, 't' => $todoId]);
        return true;
    }

    /** حذف لینک؛ اگر هدف متعلق به کاربر باشد همیشه موفق است (حذفِ لینکِ ناموجود هم). */
    public function goalUnlinkTask(int $userId, int $goalId, int $todoId): bool
    {
        if ($this->goal($userId, $goalId) === null) {
            return false;
        }
        $this->pdo()->prepare('DELETE FROM pl_goal_tasks WHERE goal_id = :g AND todo_id = :t')
            ->execute(['g' => $goalId, 't' => $todoId]);
        return true;
    }

    /** درصد پیشرفت: تسک‌های لینک‌شده اگر هست، وگرنه مایل‌استون‌ها، وگرنه ۰. */
    public function goalProgress(int $userId, int $goalId): int
    {
        if ($this->goal($userId, $goalId) === null) {
            return 0;
        }
        $stmt = $this->pdo()->prepare(
            'SELECT t.done FROM pl_goal_tasks gt JOIN pl_todos t ON t.id = gt.todo_id
             WHERE gt.goal_id = :g AND t.user_id = :u'
        );
        $stmt->execute(['g' => $goalId, 'u' => $userId]);
        $tasks = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        if ($tasks !== []) {
            $done = 0;
            foreach ($tasks as $d) {
                $done += (int) $d;
            }
            return (int) round($done * 100 / count($tasks));
        }
        $miles = $this->milestones($goalId);
        if ($miles !== []) {
            $done = 0;
            foreach ($miles as $m) {
                $done += (int) $m['done'];
            }
            return (int) round($done * 100 / count($miles));
        }
        return 0;
    }

    // ---------- ژورنال ----------

    public function journal(int $userId, string $date): ?array
    {
        if (!self::validDate($date)) {
            return null;
        }
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_journal WHERE user_id = :u AND date = :d');
        $stmt->execute(['u' => $userId, 'd' => $date]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function journalSave(int $userId, string $date, string $body, string $mood): bool
    {
        if (!self::validDate($date) || !in_array($mood, ['great', 'good', 'ok', 'bad', 'awful'], true)) {
            return false;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_journal (user_id, date, body, mood) VALUES (:u, :d, :b, :m)
             ON CONFLICT(user_id, date) DO UPDATE SET body = excluded.body, mood = excluded.mood'
        );
        $stmt->execute(['u' => $userId, 'd' => $date, 'b' => $body, 'm' => $mood]);
        return true;
    }

    // ---------- کپی پروژه + خروجی ----------

    /** کپی پروژه + نیازها (با همان وضعیت انجام)؛ عنوان با پیشوند «کپی ». اشتراک کپی نمی‌شود. */
    public function duplicateProject(int $userId, int $id): ?int
    {
        $src = $this->project($userId, $id);
        if ($src === null) {
            return null;
        }
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo()->prepare(
            'INSERT INTO pl_projects (user_id, title, description, start_date, hours_needed, created_at, updated_at)
             VALUES (:u, :t, :d, :s, :h, :now, :now)'
        );
        $stmt->execute([
            'u' => $userId, 't' => 'کپی ' . (string) $src['title'], 'd' => (string) $src['description'],
            's' => (string) $src['start_date'], 'h' => (int) $src['hours_needed'], 'now' => $now,
        ]);
        $newId = (int) $this->pdo()->lastInsertId();
        foreach ($this->needs($id) as $n) {
            $this->pdo()->prepare(
                'INSERT INTO pl_needs (project_id, title, done, created_at) VALUES (:p, :t, :d, :now)'
            )->execute(['p' => $newId, 't' => (string) $n['title'], 'd' => (int) $n['done'], 'now' => $now]);
        }
        return $newId;
    }

    /** خروجی کامل دادهٔ کاربر؛ هرگز شامل اشتراک‌ها (pl_shares/pl_links) نیست. */
    public function exportData(int $userId): array
    {
        $todos = $this->todos($userId);
        foreach ($todos as &$t) {
            $t['subtasks'] = $this->subtaskRows((int) $t['id']);
            $t['tags'] = $this->todoTagNames((int) $t['id']);
        }
        unset($t);
        $notes = $this->notes($userId);
        foreach ($notes as &$n) {
            $n['tags'] = $this->noteTagNames((int) $n['id']);
        }
        unset($n);
        $projects = $this->projects($userId);
        foreach ($projects as &$p) {
            $p['needs'] = $this->needs((int) $p['id']);
        }
        unset($p);
        $habits = $this->habits($userId);
        foreach ($habits as &$h) {
            $h['logs'] = $this->habitLogDates((int) $h['id']);
        }
        unset($h);
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_events WHERE user_id = :u ORDER BY date, start_time, id');
        $stmt->execute(['u' => $userId]);
        $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_journal WHERE user_id = :u ORDER BY date');
        $stmt->execute(['u' => $userId]);
        $journal = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return [
            'todos' => $todos, 'events' => $events, 'notes' => $notes,
            'projects' => $projects, 'habits' => $habits, 'journal' => $journal,
        ];
    }

    // ---------- اشتراک‌گذاری ----------

    private function itemTable(string $type): ?string
    {
        return match ($type) {
            'project' => 'pl_projects',
            'note' => 'pl_notes',
            default => null,
        };
    }

    /** آیتم بدون فیلتر مالک — فقط بعد از احراز اشتراک/لینک استفاده می‌شود. */
    public function itemAny(string $type, int $id): ?array
    {
        $table = $this->itemTable($type);
        if ($table === null) {
            return null;
        }
        $stmt = $this->pdo()->prepare("SELECT * FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function itemOwned(int $userId, string $type, int $id): ?array
    {
        $table = $this->itemTable($type);
        if ($table === null) {
            return null;
        }
        $stmt = $this->pdo()->prepare("SELECT * FROM {$table} WHERE id = :id AND user_id = :u");
        $stmt->execute(['id' => $id, 'u' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /** آیا کاربر به آیتم دسترسی دیدن دارد (مالک یا اشتراک‌گیرنده)؟ */
    public function canView(int $userId, string $type, int $id): bool
    {
        if ($this->itemOwned($userId, $type, $id) !== null) {
            return true;
        }
        $stmt = $this->pdo()->prepare(
            'SELECT COUNT(*) FROM pl_shares WHERE item_type = :t AND item_id = :id AND shared_user_id = :u'
        );
        $stmt->execute(['t' => $type, 'id' => $id, 'u' => $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function userByUsername(string $username): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT id, username, role FROM users WHERE username = :u');
        $stmt->execute(['u' => $username]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /** اشتراک با کاربر: ok | not_found | self | exists | not_owner */
    public function shareWithUser(int $ownerId, string $type, int $itemId, string $username): string
    {
        if ($this->itemOwned($ownerId, $type, $itemId) === null) {
            return 'not_owner';
        }
        $target = $this->userByUsername($username);
        if ($target === null) {
            return 'not_found';
        }
        if ((int) $target['id'] === $ownerId) {
            return 'self';
        }
        try {
            $stmt = $this->pdo()->prepare(
                'INSERT INTO pl_shares (item_type, item_id, owner_id, shared_user_id, created_at)
                 VALUES (:t, :id, :o, :s, :now)'
            );
            $stmt->execute(['t' => $type, 'id' => $itemId, 'o' => $ownerId, 's' => (int) $target['id'], 'now' => date('Y-m-d H:i:s')]);
        } catch (\PDOException) {
            return 'exists';
        }
        return 'ok';
    }

    /** ساخت لینک عمومی تازه؛ لینک قبلی همان آیتم باطل می‌شود. */
    public function createLink(int $ownerId, string $type, int $itemId): ?string
    {
        if ($this->itemOwned($ownerId, $type, $itemId) === null) {
            return null;
        }
        $token = bin2hex(random_bytes(16));
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM pl_links WHERE item_type = :t AND item_id = :id')->execute(['t' => $type, 'id' => $itemId]);
        $pdo->prepare(
            'INSERT INTO pl_links (item_type, item_id, owner_id, token, created_at) VALUES (:t, :id, :o, :tok, :now)'
        )->execute(['t' => $type, 'id' => $itemId, 'o' => $ownerId, 'tok' => $token, 'now' => date('Y-m-d H:i:s')]);
        return $token;
    }

    public function sharesFor(int $ownerId, string $type, int $itemId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT s.*, u.username FROM pl_shares s JOIN users u ON u.id = s.shared_user_id
             WHERE s.item_type = :t AND s.item_id = :id AND s.owner_id = :o ORDER BY s.id'
        );
        $stmt->execute(['t' => $type, 'id' => $itemId, 'o' => $ownerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function linksFor(int $ownerId, string $type, int $itemId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT * FROM pl_links WHERE item_type = :t AND item_id = :id AND owner_id = :o ORDER BY id'
        );
        $stmt->execute(['t' => $type, 'id' => $itemId, 'o' => $ownerId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function revokeShare(int $ownerId, int $shareId): void
    {
        $this->pdo()->prepare('DELETE FROM pl_shares WHERE id = :id AND owner_id = :o')
            ->execute(['id' => $shareId, 'o' => $ownerId]);
    }

    public function revokeLink(int $ownerId, int $linkId): void
    {
        $this->pdo()->prepare('DELETE FROM pl_links WHERE id = :id AND owner_id = :o')
            ->execute(['id' => $linkId, 'o' => $ownerId]);
    }

    /** آیتم‌هایی که با این کاربر به اشتراک گذاشته شده‌اند (فقط خواندنی). */
    public function sharedWithMe(int $userId): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT s.*, u.username AS owner_name FROM pl_shares s JOIN users u ON u.id = s.owner_id
             WHERE s.shared_user_id = :u ORDER BY s.id DESC'
        );
        $stmt->execute(['u' => $userId]);
        $out = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $item = $this->itemAny((string) $row['item_type'], (int) $row['item_id']);
            if ($item === null) {
                continue;
            }
            $row['title'] = (string) $item['title'];
            $out[] = $row;
        }
        return $out;
    }

    /** حل لینک عمومی: آیتم + نام مالک، یا null. */
    public function resolveLink(string $token): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM pl_links WHERE token = :t');
        $stmt->execute(['t' => $token]);
        $link = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($link === false) {
            return null;
        }
        $item = $this->itemAny((string) $link['item_type'], (int) $link['item_id']);
        if ($item === null) {
            return null;
        }
        $owner = $this->pdo()->prepare('SELECT username FROM users WHERE id = :id');
        $owner->execute(['id' => (int) $link['owner_id']]);
        return ['type' => (string) $link['item_type'], 'item' => $item, 'owner' => (string) ($owner->fetchColumn() ?: '')];
    }

    private function deleteItemShares(string $type, int $itemId): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('DELETE FROM pl_shares WHERE item_type = :t AND item_id = :id')->execute(['t' => $type, 'id' => $itemId]);
        $pdo->prepare('DELETE FROM pl_links WHERE item_type = :t AND item_id = :id')->execute(['t' => $type, 'id' => $itemId]);
    }
}
