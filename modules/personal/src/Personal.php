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
        return $stmt->rowCount() > 0;
    }

    public function todoDelete(int $userId, int $id): void
    {
        $this->pdo()->prepare('DELETE FROM pl_todos WHERE id = :id AND user_id = :u')
            ->execute(['id' => $id, 'u' => $userId]);
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
        $this->pdo()->prepare('DELETE FROM pl_events WHERE id = :id AND user_id = :u')
            ->execute(['id' => $id, 'u' => $userId]);
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
        $pdo->prepare('DELETE FROM pl_notes WHERE id = :id AND user_id = :u')->execute(['id' => $id, 'u' => $userId]);
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
        $pdo->prepare('DELETE FROM pl_projects WHERE id = :id AND user_id = :u')->execute(['id' => $id, 'u' => $userId]);
        $pdo->prepare('DELETE FROM pl_needs WHERE project_id = :id')->execute(['id' => $id]);
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
