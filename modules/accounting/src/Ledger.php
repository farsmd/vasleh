<?php
declare(strict_types=1);

namespace Modules\Accounting;

use Modules\Sqlite\Database;

/**
 * حسابداری ساده کسب‌وکار: حساب‌های پولی (صندوق/بانک)، دسته‌های درآمد/هزینه،
 * اسناد دریافت/پرداخت، انتقال بین حساب‌ها و گزارش ماهانه.
 * مانده هر حساب = موجودی اول دوره + درآمدها − هزینه‌ها ± انتقال‌ها.
 */
final class Ledger
{
    public const ACCOUNT_TYPES = [
        'cash'  => 'صندوق/نقد',
        'bank'  => 'بانک',
        'other' => 'سایر',
    ];

    public function __construct(private readonly Database $db) {}

    private function pdo(): \PDO
    {
        return $this->db->pdo();
    }

    public function migrate(): void
    {
        $pdo = $this->pdo();
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS acc_accounts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                type TEXT NOT NULL DEFAULT \'bank\',
                opening_balance INTEGER NOT NULL DEFAULT 0,
                active INTEGER NOT NULL DEFAULT 1,
                note TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS acc_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                kind TEXT NOT NULL DEFAULT \'expense\',
                sort INTEGER NOT NULL DEFAULT 0
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS acc_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                account_id INTEGER NOT NULL,
                category_id INTEGER NOT NULL DEFAULT 0,
                kind TEXT NOT NULL,
                amount INTEGER NOT NULL DEFAULT 0,
                date TEXT NOT NULL,
                ref TEXT NOT NULL DEFAULT \'\',
                party TEXT NOT NULL DEFAULT \'\',
                description TEXT NOT NULL DEFAULT \'\',
                actor TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS acc_transfers (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                from_account INTEGER NOT NULL,
                to_account INTEGER NOT NULL,
                amount INTEGER NOT NULL DEFAULT 0,
                date TEXT NOT NULL,
                note TEXT NOT NULL DEFAULT \'\',
                actor TEXT NOT NULL DEFAULT \'\',
                created_at TEXT NOT NULL
            )'
        );
    }

    public function seedIfEmpty(): void
    {
        $pdo = $this->pdo();
        if ((int) $pdo->query('SELECT COUNT(*) FROM acc_accounts')->fetchColumn() === 0) {
            $pdo->prepare('INSERT INTO acc_accounts (title, type, opening_balance, active, note, created_at) VALUES (\'صندوق اصلی\', \'cash\', 0, 1, \'\', :n)')
                ->execute(['n' => date('Y-m-d H:i:s')]);
        }
        if ((int) $pdo->query('SELECT COUNT(*) FROM acc_categories')->fetchColumn() === 0) {
            $stmt = $pdo->prepare('INSERT INTO acc_categories (title, kind, sort) VALUES (:t, :k, :s)');
            foreach ([['فروش', 'income', 0], ['سایر درآمد', 'income', 1]] as [$t, $k, $s]) {
                $stmt->execute(['t' => $t, 'k' => $k, 's' => $s]);
            }
            foreach ([['خرید مواد', 'expense', 0], ['اجاره', 'expense', 1], ['حقوق', 'expense', 2], ['حمل‌ونقل', 'expense', 3], ['سایر هزینه‌ها', 'expense', 4]] as [$t, $k, $s]) {
                $stmt->execute(['t' => $t, 'k' => $k, 's' => $s]);
            }
        }
    }

    // ---------- حساب‌ها ----------

    public function accounts(bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM acc_accounts' . ($activeOnly ? ' WHERE active = 1' : '') . ' ORDER BY id';
        $rows = $this->pdo()->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['balance'] = $this->balance((int) $row['id']);
        }
        unset($row);
        return $rows;
    }

    public function account(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM acc_accounts WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function accountSave(array $data, int $id = 0): int
    {
        if ($id > 0) {
            $stmt = $this->pdo()->prepare('UPDATE acc_accounts SET title = :t, type = :ty, opening_balance = :o, active = :a, note = :n WHERE id = :id');
            $stmt->execute(['t' => $data['title'], 'ty' => $data['type'], 'o' => $data['opening_balance'], 'a' => $data['active'], 'n' => $data['note'], 'id' => $id]);
            return $id;
        }
        $stmt = $this->pdo()->prepare('INSERT INTO acc_accounts (title, type, opening_balance, active, note, created_at) VALUES (:t, :ty, :o, :a, :n, :now)');
        $stmt->execute(['t' => $data['title'], 'ty' => $data['type'], 'o' => $data['opening_balance'], 'a' => $data['active'], 'n' => $data['note'], 'now' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function accountDelete(int $id): bool
    {
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM acc_transactions WHERE account_id = :id');
        $stmt->execute(['id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->pdo()->prepare('SELECT COUNT(*) FROM acc_transfers WHERE from_account = :id OR to_account = :id');
        $stmt->execute(['id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }
        $this->pdo()->prepare('DELETE FROM acc_accounts WHERE id = :id')->execute(['id' => $id]);
        return true;
    }

    public function balance(int $accountId): int
    {
        $account = $this->account($accountId);
        if ($account === null) {
            return 0;
        }
        $pdo = $this->pdo();
        $sum = static function (string $sql) use ($pdo, $accountId): int {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $accountId]);
            return (int) $stmt->fetchColumn();
        };
        $income = $sum("SELECT COALESCE(SUM(amount), 0) FROM acc_transactions WHERE account_id = :id AND kind = 'income'");
        $expense = $sum("SELECT COALESCE(SUM(amount), 0) FROM acc_transactions WHERE account_id = :id AND kind = 'expense'");
        $in = $sum('SELECT COALESCE(SUM(amount), 0) FROM acc_transfers WHERE to_account = :id');
        $out = $sum('SELECT COALESCE(SUM(amount), 0) FROM acc_transfers WHERE from_account = :id');
        return (int) $account['opening_balance'] + $income - $expense + $in - $out;
    }

    public function totalBalance(): int
    {
        $total = 0;
        foreach ($this->accounts() as $account) {
            $total += (int) $account['balance'];
        }
        return $total;
    }

    // ---------- دسته‌ها ----------

    public function categories(?string $kind = null): array
    {
        if ($kind !== null) {
            $stmt = $this->pdo()->prepare('SELECT * FROM acc_categories WHERE kind = :k ORDER BY sort, id');
            $stmt->execute(['k' => $kind]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        return $this->pdo()->query('SELECT * FROM acc_categories ORDER BY kind, sort, id')->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function category(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM acc_categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function categorySave(array $data, int $id = 0): int
    {
        if ($id > 0) {
            $stmt = $this->pdo()->prepare('UPDATE acc_categories SET title = :t, kind = :k, sort = :s WHERE id = :id');
            $stmt->execute(['t' => $data['title'], 'k' => $data['kind'], 's' => $data['sort'], 'id' => $id]);
            return $id;
        }
        $stmt = $this->pdo()->prepare('INSERT INTO acc_categories (title, kind, sort) VALUES (:t, :k, :s)');
        $stmt->execute(['t' => $data['title'], 'k' => $data['kind'], 's' => $data['sort']]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function categoryDelete(int $id): void
    {
        $pdo = $this->pdo();
        $pdo->prepare('UPDATE acc_transactions SET category_id = 0 WHERE category_id = :id')->execute(['id' => $id]);
        $pdo->prepare('DELETE FROM acc_categories WHERE id = :id')->execute(['id' => $id]);
    }

    // ---------- اسناد ----------

    /** فهرست اسناد با فیلترهای اختیاری: kind, account_id, category_id, month (YYYY-MM), q (جست‌وجو در طرف/مرجع/توضیح). */
    public function transactions(array $filters = [], int $limit = 300): array
    {
        $sql = 'SELECT t.*, a.title AS account_title, c.title AS category_title
                FROM acc_transactions t
                LEFT JOIN acc_accounts a ON a.id = t.account_id
                LEFT JOIN acc_categories c ON c.id = t.category_id';
        $where = [];
        $args = [];
        if (!empty($filters['kind'])) {
            $where[] = 't.kind = :kind';
            $args['kind'] = $filters['kind'];
        }
        if (!empty($filters['account_id'])) {
            $where[] = 't.account_id = :acc';
            $args['acc'] = (int) $filters['account_id'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 't.category_id = :cat';
            $args['cat'] = (int) $filters['category_id'];
        }
        if (!empty($filters['month'])) {
            $where[] = 'substr(t.date, 1, 7) = :month';
            $args['month'] = $filters['month'];
        }
        if (!empty($filters['q'])) {
            $where[] = '(t.party LIKE :q OR t.ref LIKE :q OR t.description LIKE :q)';
            $args['q'] = '%' . $filters['q'] . '%';
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY t.date DESC, t.id DESC LIMIT ' . max(1, $limit);
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function transaction(int $id): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM acc_transactions WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    public function transactionSave(array $data, int $id = 0): int
    {
        if ($id > 0) {
            $stmt = $this->pdo()->prepare(
                'UPDATE acc_transactions SET account_id = :a, category_id = :c, kind = :k, amount = :am, date = :d, ref = :r, party = :p, description = :de WHERE id = :id'
            );
            $stmt->execute(['a' => $data['account_id'], 'c' => $data['category_id'], 'k' => $data['kind'], 'am' => $data['amount'], 'd' => $data['date'], 'r' => $data['ref'], 'p' => $data['party'], 'de' => $data['description'], 'id' => $id]);
            return $id;
        }
        $stmt = $this->pdo()->prepare(
            'INSERT INTO acc_transactions (account_id, category_id, kind, amount, date, ref, party, description, actor, created_at)
             VALUES (:a, :c, :k, :am, :d, :r, :p, :de, :actor, :now)'
        );
        $stmt->execute(['a' => $data['account_id'], 'c' => $data['category_id'], 'k' => $data['kind'], 'am' => $data['amount'], 'd' => $data['date'], 'r' => $data['ref'], 'p' => $data['party'], 'de' => $data['description'], 'actor' => $data['actor'] ?? '', 'now' => date('Y-m-d H:i:s')]);
        return (int) $this->pdo()->lastInsertId();
    }

    public function transactionDelete(int $id): void
    {
        $this->pdo()->prepare('DELETE FROM acc_transactions WHERE id = :id')->execute(['id' => $id]);
    }

    public function transactionByRef(string $ref): ?array
    {
        $stmt = $this->pdo()->prepare('SELECT * FROM acc_transactions WHERE ref = :r ORDER BY id LIMIT 1');
        $stmt->execute(['r' => $ref]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    // ---------- انتقال بین حساب‌ها ----------

    public function transfers(int $limit = 100): array
    {
        $stmt = $this->pdo()->prepare(
            'SELECT tr.*, f.title AS from_title, tt.title AS to_title
             FROM acc_transfers tr
             LEFT JOIN acc_accounts f ON f.id = tr.from_account
             LEFT JOIN acc_accounts tt ON tt.id = tr.to_account
             ORDER BY tr.date DESC, tr.id DESC LIMIT ' . max(1, $limit)
        );
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /** @return array{ok: bool, error: ?string} */
    public function transfer(int $from, int $to, int $amount, string $date, string $note, string $actor): array
    {
        if ($from === $to) {
            return ['ok' => false, 'error' => 'حساب مبدأ و مقصد یکی است.'];
        }
        if ($amount <= 0) {
            return ['ok' => false, 'error' => 'مبلغ باید بزرگ‌تر از صفر باشد.'];
        }
        if ($this->account($from) === null || $this->account($to) === null) {
            return ['ok' => false, 'error' => 'حساب یافت نشد.'];
        }
        if ($this->balance($from) < $amount) {
            return ['ok' => false, 'error' => 'مانده حساب مبدأ کافی نیست.'];
        }
        $this->pdo()->prepare('INSERT INTO acc_transfers (from_account, to_account, amount, date, note, actor, created_at) VALUES (:f, :t, :a, :d, :n, :actor, :now)')
            ->execute(['f' => $from, 't' => $to, 'a' => $amount, 'd' => $date, 'n' => $note, 'actor' => $actor, 'now' => date('Y-m-d H:i:s')]);
        return ['ok' => true, 'error' => null];
    }

    public function transferDelete(int $id): void
    {
        $this->pdo()->prepare('DELETE FROM acc_transfers WHERE id = :id')->execute(['id' => $id]);
    }

    // ---------- گزارش‌ها ----------

    /** جمع یک ماه: ['income'=>…, 'expense'=>…, 'net'=>…] */
    public function monthSummary(string $month): array
    {
        $stmt = $this->pdo()->prepare(
            "SELECT kind, COALESCE(SUM(amount), 0) AS total FROM acc_transactions WHERE substr(date, 1, 7) = :m GROUP BY kind"
        );
        $stmt->execute(['m' => $month]);
        $out = ['income' => 0, 'expense' => 0];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $out[(string) $row['kind']] = (int) $row['total'];
        }
        $out['net'] = $out['income'] - $out['expense'];
        return $out;
    }

    /** ردیف‌های ماهانه از قدیم به جدید. */
    public function monthlyRows(): array
    {
        $rows = $this->pdo()->query(
            "SELECT substr(date, 1, 7) AS month, kind, COALESCE(SUM(amount), 0) AS total
             FROM acc_transactions GROUP BY month, kind ORDER BY month"
        )->fetchAll(\PDO::FETCH_ASSOC);
        $byMonth = [];
        foreach ($rows as $row) {
            $byMonth[$row['month']][$row['kind']] = (int) $row['total'];
        }
        $out = [];
        foreach ($byMonth as $month => $kinds) {
            $income = $kinds['income'] ?? 0;
            $expense = $kinds['expense'] ?? 0;
            $out[] = ['month' => $month, 'income' => $income, 'expense' => $expense, 'net' => $income - $expense];
        }
        return $out;
    }

    /** جمع به تفکیک دسته در یک بازه تاریخی (from/to به قالب Y-m-d، خالی = بدون مرز). */
    public function categoryTotals(string $from = '', string $to = ''): array
    {
        $sql = 'SELECT c.title AS category_title, t.kind, COALESCE(SUM(t.amount), 0) AS total, COUNT(*) AS docs
                FROM acc_transactions t LEFT JOIN acc_categories c ON c.id = t.category_id';
        $where = [];
        $args = [];
        if ($from !== '') {
            $where[] = 't.date >= :from';
            $args['from'] = $from;
        }
        if ($to !== '') {
            $where[] = 't.date <= :to';
            $args['to'] = $to;
        }
        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' GROUP BY t.kind, t.category_id ORDER BY t.kind, total DESC';
        $stmt = $this->pdo()->prepare($sql);
        $stmt->execute($args);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
