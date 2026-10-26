<?php
declare(strict_types=1);

namespace Modules\Reports;

use Modules\Sqlite\Database;

/**
 * سرويس logger — ثبت و خواندن لاگ‌های وصله در جدول reports_log.
 * نوع‌ها: system (رویداد سیستم)، user (کار کاربر)، visit (بازدید روت/ویو).
 * IP و User-Agent هنگام ثبت از خود سرور خوانده می‌شود.
 */
final class Logger
{
    public function __construct(private readonly Database $db) {}

    public function migrate(): void
    {
        $pdo = $this->db->pdo();
        $pdo->exec('CREATE TABLE IF NOT EXISTS reports_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            kind TEXT NOT NULL,
            actor TEXT NOT NULL DEFAULT "",
            action TEXT NOT NULL DEFAULT "",
            target TEXT NOT NULL DEFAULT "",
            ip TEXT NOT NULL DEFAULT "",
            ua TEXT NOT NULL DEFAULT "",
            meta TEXT NOT NULL DEFAULT ""
        )');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reports_kind ON reports_log (kind, id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_reports_actor ON reports_log (actor, id)');
    }

    private function insert(string $kind, string $actor, string $action, string $target, array $meta = []): void
    {
        $stmt = $this->db->pdo()->prepare(
            'INSERT INTO reports_log (kind, actor, action, target, ip, ua, meta) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $kind,
            $actor,
            $action,
            $target,
            (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
            substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 300),
            $meta === [] ? '' : (string) json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    /** رویداد سیستمی (تغییرات و گزارشات سیستم). */
    public function system(string $action, string $target = '', string $actor = ''): void
    {
        try {
            $this->insert('system', $actor, $action, $target);
        } catch (\Throwable) {
        }
    }

    /** کاری که یک کاربر واردشده انجام داده است. */
    public function user(string $actor, string $action, string $target = ''): void
    {
        if ($actor === '') {
            return;
        }
        try {
            $this->insert('user', $actor, $action, $target);
        } catch (\Throwable) {
        }
    }

    /** بازدید یک روت/ویو — از هوک ماژول صدا زده می‌شود. */
    public function visit(string $method, string $path, string $actor): void
    {
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $this->insert('visit', $actor, $method, $path, $referer !== '' ? ['referer' => $referer] : []);
    }

    // ---------- خواندن برای صفحات گزارش ----------

    /** رویدادهای غیربازدید (سیستم + کاربران) با فیلتر نوع و صفحه‌بندی ساده. */
    public function events(?string $kind, int $limit, int $offset): array
    {
        $sql = "SELECT * FROM reports_log WHERE kind != 'visit'";
        $params = [];
        if ($kind !== null && in_array($kind, ['system', 'user'], true)) {
            $sql .= ' AND kind = ?';
            $params[] = $kind;
        }
        $sql .= ' ORDER BY id DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function eventsCount(?string $kind): int
    {
        $sql = "SELECT COUNT(*) FROM reports_log WHERE kind != 'visit'";
        $params = [];
        if ($kind !== null && in_array($kind, ['system', 'user'], true)) {
            $sql .= ' AND kind = ?';
            $params[] = $kind;
        }
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** خلاصه هر کاربر: تعداد رویداد، تعداد بازدید، آخرین فعالیت. */
    public function userSummary(): array
    {
        $pdo = $this->db->pdo();
        $events = $pdo->query(
            "SELECT actor, COUNT(*) AS cnt, MAX(created_at) AS last_at
             FROM reports_log WHERE kind = 'user' AND actor != '' GROUP BY actor"
        )->fetchAll();
        $visits = $pdo->query(
            "SELECT actor, COUNT(*) AS cnt FROM reports_log
             WHERE kind = 'visit' AND actor NOT IN ('', 'مهمان', 'api') GROUP BY actor"
        )->fetchAll();
        $visitMap = [];
        foreach ($visits as $row) {
            $visitMap[(string) $row['actor']] = (int) $row['cnt'];
        }
        foreach ($events as &$row) {
            $row['visits'] = $visitMap[(string) $row['actor']] ?? 0;
        }
        return $events;
    }

    public function userEvents(string $actor, int $limit): array
    {
        $stmt = $this->db->pdo()->prepare(
            "SELECT * FROM reports_log WHERE kind = 'user' AND actor = ? ORDER BY id DESC LIMIT ?"
        );
        $stmt->execute([$actor, $limit]);
        return $stmt->fetchAll();
    }

    public function userVisits(string $actor, int $limit): array
    {
        $stmt = $this->db->pdo()->prepare(
            "SELECT * FROM reports_log WHERE kind = 'visit' AND actor = ? ORDER BY id DESC LIMIT ?"
        );
        $stmt->execute([$actor, $limit]);
        return $stmt->fetchAll();
    }

    public function recentVisits(int $limit, int $offset): array
    {
        $stmt = $this->db->pdo()->prepare(
            "SELECT * FROM reports_log WHERE kind = 'visit' ORDER BY id DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function visitsCount(): int
    {
        return (int) $this->db->pdo()->query("SELECT COUNT(*) FROM reports_log WHERE kind = 'visit'")->fetchColumn();
    }

    /** شمارش روزانه بازدیدهای N روز گذشته (UTC)؛ روزهای بدون بازدید صفر. */
    public function visitsByDay(int $days): array
    {
        $pdo = $this->db->pdo();
        $since = gmdate('Y-m-d', time() - ($days - 1) * 86400) . ' 00:00:00';
        $stmt = $pdo->prepare("SELECT substr(created_at, 1, 10) AS d, COUNT(*) AS cnt FROM reports_log WHERE kind = 'visit' AND created_at >= ? GROUP BY d ORDER BY d");
        $stmt->execute([$since]);
        $counts = [];
        foreach ($stmt->fetchAll() as $row) {
            $counts[(string) $row['d']] = (int) $row['cnt'];
        }
        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = gmdate('Y-m-d', time() - $i * 86400);
            $out[] = ['date' => $d, 'count' => $counts[$d] ?? 0];
        }
        return $out;
    }

    /** سهم مرورگرها در بازه، از روی User-Agent (ترتیب CASE مهم است: Edge پیش از Chrome، Chrome پیش از Safari). */
    public function browserShares(?string $since): array
    {
        $sql = "SELECT CASE WHEN ua LIKE '%Edg%' THEN 'Edge' WHEN ua LIKE '%OPR%' OR ua LIKE '%Opera%' THEN 'Opera' WHEN ua LIKE '%Firefox%' THEN 'Firefox' WHEN ua LIKE '%Chrome%' THEN 'Chrome' WHEN ua LIKE '%Safari%' THEN 'Safari' WHEN ua LIKE '%curl%' OR ua LIKE '%bot%' OR ua LIKE '%crawler%' THEN 'ربات' ELSE 'سایر' END AS browser, COUNT(*) AS cnt FROM reports_log WHERE kind = 'visit'";
        $params = [];
        if ($since !== null) {
            $sql .= ' AND created_at >= ?';
            $params[] = $since;
        }
        $sql .= ' GROUP BY browser ORDER BY cnt DESC';
        $stmt = $this->db->pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** IPهای یکتای N دقیقه گذشته. */
    public function activeIps(int $minutes): int
    {
        $stmt = $this->db->pdo()->prepare("SELECT COUNT(DISTINCT ip) FROM reports_log WHERE kind = 'visit' AND ip != '' AND created_at >= ?");
        $stmt->execute([gmdate('Y-m-d H:i:s', time() - $minutes * 60)]);
        return (int) $stmt->fetchColumn();
    }

    /** شرط مشترک فیلتر بازدیدها: بازه، جستجوی IP، سیستم‌عامل. */
    private function visitFilter(?string $since, string $ip, string $os): array
    {
        $where = '';
        $params = [];
        if ($since !== null) {
            $where .= ' AND created_at >= ?';
            $params[] = $since;
        }
        if ($ip !== '') {
            $where .= ' AND ip LIKE ?';
            $params[] = '%' . $ip . '%';
        }
        $osLike = [
            'windows' => "ua LIKE '%Windows%'",
            'macos'   => "ua LIKE '%Mac OS%' AND ua NOT LIKE '%iPhone%' AND ua NOT LIKE '%iPad%'",
            'android' => "ua LIKE '%Android%'",
            'ios'     => "(ua LIKE '%iPhone%' OR ua LIKE '%iPad%')",
            'linux'   => "ua LIKE '%Linux%'",
        ];
        if (isset($osLike[$os])) {
            $where .= ' AND ' . $osLike[$os];
        }
        return [$where, $params];
    }

    /** فهرست بازدیدها با فیلتر بازه/IP/سیستم‌عامل. */
    public function filteredVisits(?string $since, string $ip, string $os, int $limit, int $offset): array
    {
        [$where, $params] = $this->visitFilter($since, $ip, $os);
        $stmt = $this->db->pdo()->prepare("SELECT * FROM reports_log WHERE kind = 'visit' $where ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->execute([...$params, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function filteredVisitsCount(?string $since, string $ip, string $os): int
    {
        [$where, $params] = $this->visitFilter($since, $ip, $os);
        $stmt = $this->db->pdo()->prepare("SELECT COUNT(*) FROM reports_log WHERE kind = 'visit' $where");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /** آمار بازدید: امروز، IP یکتا، روت‌ها و IPهای پرتکرار. */
    public function visitStats(): array
    {
        $pdo = $this->db->pdo();
        $today = gmdate('Y-m-d');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reports_log WHERE kind = 'visit' AND created_at >= ?");
        $stmt->execute([$today . ' 00:00:00']);
        $todayCount = (int) $stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT ip) FROM reports_log WHERE kind = 'visit' AND created_at >= ?");
        $stmt->execute([$today . ' 00:00:00']);
        $todayIps = (int) $stmt->fetchColumn();
        $topRoutes = $pdo->query(
            "SELECT target, COUNT(*) AS cnt FROM reports_log WHERE kind = 'visit'
             GROUP BY target ORDER BY cnt DESC LIMIT 10"
        )->fetchAll();
        $topIps = $pdo->query(
            "SELECT ip, COUNT(*) AS cnt FROM reports_log WHERE kind = 'visit' AND ip != ''
             GROUP BY ip ORDER BY cnt DESC LIMIT 10"
        )->fetchAll();
        return [
            'today'     => $todayCount,
            'today_ips' => $todayIps,
            'total'     => $this->visitsCount(),
            'routes'    => $topRoutes,
            'ips'       => $topIps,
        ];
    }

    /** پاک‌سازی بازدیدهای قدیمی‌تر از $days روز؛ خروجی: تعداد حذف‌شده. */
    public function pruneVisits(int $days): int
    {
        $days = max(1, min(3650, $days));
        $stmt = $this->db->pdo()->prepare(
            "DELETE FROM reports_log WHERE kind = 'visit' AND created_at < datetime('now', ?)"
        );
        $stmt->execute(['-' . $days . ' days']);
        return $stmt->rowCount();
    }

    /** خلاصه خوانا از User-Agent: مرورگر / سیستم‌عامل. */
    public static function uaSummary(string $ua): string
    {
        if ($ua === '') {
            return '—';
        }
        $browser = 'نامشخص';
        foreach ([
            'Edg' => 'Edge', 'OPR' => 'Opera', 'Opera' => 'Opera',
            'Firefox' => 'Firefox', 'Chrome' => 'Chrome', 'Safari' => 'Safari',
            'curl' => 'curl', 'bot' => 'ربات', 'Bot' => 'ربات', 'crawler' => 'ربات',
        ] as $needle => $name) {
            if (str_contains($ua, $needle)) {
                $browser = $name;
                break;
            }
        }
        $os = '';
        foreach ([
            'Windows' => 'Windows', 'Android' => 'Android', 'iPhone' => 'iOS',
            'iPad' => 'iOS', 'Mac OS' => 'macOS', 'Linux' => 'Linux',
        ] as $needle => $name) {
            if (str_contains($ua, $needle)) {
                $os = $name;
                break;
            }
        }
        return $browser . ($os !== '' ? ' / ' . $os : '');
    }
}
