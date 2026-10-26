<?php
declare(strict_types=1);

namespace Modules\Accounting;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل حسابداری: داشبورد، اسناد، حساب‌ها، دسته‌ها، انتقال‌ها و گزارش‌ها. */
final class AdminAccountingController
{
    public function __construct(private readonly Container $container) {}

    private function ledger(): Ledger
    {
        return $this->container->get('accounting.ledger');
    }

    private function currency(): string
    {
        if ($this->container->has('shop.settings')) {
            $unit = $this->container->get('shop.settings')->get('currency', '');
            if ($unit !== '') {
                return $unit;
            }
        }
        return 'تومان';
    }

    private function actor(): string
    {
        return (string) ($this->container->get('session')->user()['username'] ?? '');
    }

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('accounting', 'admin', $view, $data + [
            'csrf' => $session->csrfToken(),
            'currency' => $this->currency(),
        ]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => $title,
            'content' => $content,
            'menu'    => $this->container->get('loader')->adminMenu(),
            'user'    => $session->user(),
            'csrf'    => $session->csrfToken(),
        ]));
    }

    private function log(string $action, string $target): void
    {
        if (!$this->container->has('logger') || !$this->container->has('session')) {
            return;
        }
        $actor = $this->actor();
        if ($actor !== '') {
            $this->container->get('logger')->user($actor, $action, $target);
        }
    }

    private function back(string $path, string $msg = '', string $err = ''): Response
    {
        $q = [];
        if ($msg !== '') {
            $q['msg'] = $msg;
        }
        if ($err !== '') {
            $q['err'] = $err;
        }
        return Response::redirect($path . ($q === [] ? '' : '?' . http_build_query($q)));
    }

    // ---------- داشبورد ----------

    public function dashboard(Request $request): Response
    {
        $ledger = $this->ledger();
        $month = date('Y-m');
        $expenseByCat = array_values(array_filter(
            $ledger->categoryTotals(),
            static fn ($r) => $r['kind'] === 'expense' && ($r['category_title'] ?? '') !== ''
        ));
        return $this->page('dashboard', [
            'accounts' => $ledger->accounts(),
            'activeAccounts' => $ledger->accounts(true),
            'total' => $ledger->totalBalance(),
            'month' => $ledger->monthSummary($month),
            'monthLabel' => $month,
            'monthly' => $ledger->monthlyRows(),
            'expenseByCat' => $expenseByCat,
            'incomeCats' => $ledger->categories('income'),
            'expenseCats' => $ledger->categories('expense'),
            'hasShop' => $this->container->has('shop.orders'),
            'recent' => $ledger->transactions([], 10),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'حسابداری');
    }

    // ---------- اسناد ----------

    public function transactions(Request $request): Response
    {
        $filters = [
            'kind' => (string) $request->input('kind', ''),
            'account_id' => (int) $request->input('account', 0),
            'category_id' => (int) $request->input('category', 0),
            'month' => (string) $request->input('month', ''),
            'q' => trim((string) $request->input('q', '')),
        ];
        return $this->page('transactions', [
            'rows' => $this->ledger()->transactions($filters),
            'accounts' => $this->ledger()->accounts(),
            'categories' => $this->ledger()->categories(),
            'filters' => $filters,
            'hasShop' => $this->container->has('shop.orders'),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'اسناد حسابداری');
    }

    public function transactionCreateForm(Request $request): Response
    {
        return $this->transactionForm(null, '/admin/accounting/transactions/create', 'سند تازه');
    }

    public function transactionEditForm(Request $request, array $params): Response
    {
        $tx = $this->ledger()->transaction((int) ($params['id'] ?? 0));
        if ($tx === null) {
            return $this->back('/admin/accounting/transactions', '', 'سند یافت نشد.');
        }
        return $this->transactionForm($tx, '/admin/accounting/transactions/edit/' . $tx['id'], 'ویرایش سند');
    }

    private function transactionForm(?array $tx, string $action, string $title, ?string $error = null): Response
    {
        return $this->page('transaction_form', [
            'tx' => $tx ?? ['account_id' => 0, 'category_id' => 0, 'kind' => 'expense', 'amount' => 0, 'date' => date('Y-m-d'), 'ref' => '', 'party' => '', 'description' => ''],
            'action' => $action,
            'accounts' => $this->ledger()->accounts(true),
            'incomeCats' => $this->ledger()->categories('income'),
            'expenseCats' => $this->ledger()->categories('expense'),
            'error' => $error,
        ], $title);
    }

    /** @return array{0: array, 1: ?string} */
    private function readTransaction(Request $request): array
    {
        $kind = $request->input('kind') === 'income' ? 'income' : 'expense';
        $data = [
            'account_id' => (int) $request->input('account_id', 0),
            'category_id' => (int) $request->input('category_id', 0),
            'kind' => $kind,
            'amount' => (int) $request->input('amount', 0),
            'date' => trim((string) $request->input('date', date('Y-m-d'))),
            'ref' => trim((string) $request->input('ref', '')),
            'party' => trim((string) $request->input('party', '')),
            'description' => trim((string) $request->input('description', '')),
        ];
        $error = null;
        if ($this->ledger()->account($data['account_id']) === null) {
            $error = 'حساب را انتخاب کنید.';
        } elseif ($data['amount'] <= 0) {
            $error = 'مبلغ باید بزرگ‌تر از صفر باشد.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
            $error = 'تاریخ باید به قالب 2026-01-30 باشد.';
        } else {
            $cat = $data['category_id'] > 0 ? $this->ledger()->category($data['category_id']) : null;
            if ($cat !== null && $cat['kind'] !== $kind) {
                $error = 'دسته انتخاب‌شده با نوع سند (درآمد/هزینه) نمی‌خواند.';
            }
        }
        return [$data, $error];
    }

    public function transactionCreate(Request $request): Response
    {
        [$data, $error] = $this->readTransaction($request);
        if ($error !== null) {
            return $this->transactionForm($data, '/admin/accounting/transactions/create', 'سند تازه', $error);
        }
        $data['actor'] = $this->actor();
        $this->ledger()->transactionSave($data);
        $this->log('ثبت سند حسابداری', number_format($data['amount']));
        return $this->back('/admin/accounting/transactions', 'سند ثبت شد.');
    }

    public function transactionUpdate(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        if ($this->ledger()->transaction($id) === null) {
            return $this->back('/admin/accounting/transactions', '', 'سند یافت نشد.');
        }
        [$data, $error] = $this->readTransaction($request);
        if ($error !== null) {
            $data['id'] = $id;
            return $this->transactionForm($data, '/admin/accounting/transactions/edit/' . $id, 'ویرایش سند', $error);
        }
        $this->ledger()->transactionSave($data, $id);
        $this->log('ویرایش سند حسابداری', '#' . $id);
        return $this->back('/admin/accounting/transactions', 'سند به‌روز شد.');
    }

    public function transactionDelete(Request $request, array $params): Response
    {
        $this->ledger()->transactionDelete((int) ($params['id'] ?? 0));
        $this->log('حذف سند حسابداری', '');
        return $this->back('/admin/accounting/transactions', 'سند حذف شد.');
    }

    /** وارد کردن فروش‌های پرداخت‌شده فروشگاه به‌عنوان سند درآمد (بدون تکرار — مرجع: SHOP-<code>). */
    public function importShop(Request $request): Response
    {
        if (!$this->container->has('shop.orders')) {
            return $this->back('/admin/accounting/transactions', '', 'ماژول فروشگاه فعال نیست.');
        }
        $accountId = (int) $request->input('account_id', 0);
        if ($this->ledger()->account($accountId) === null) {
            return $this->back('/admin/accounting/transactions', '', 'حساب مقصد را انتخاب کنید.');
        }
        $salesCat = null;
        foreach ($this->ledger()->categories('income') as $cat) {
            if ($cat['title'] === 'فروش') {
                $salesCat = $cat;
                break;
            }
        }
        $orders = $this->container->get('shop.orders');
        $imported = 0;
        foreach ($orders->all() as $order) {
            if ($order['payment_status'] !== 'paid' || $order['status'] === 'cancelled') {
                continue;
            }
            $ref = 'SHOP-' . $order['code'];
            if ($this->ledger()->transactionByRef($ref) !== null) {
                continue;
            }
            $this->ledger()->transactionSave([
                'account_id' => $accountId,
                'category_id' => (int) ($salesCat['id'] ?? 0),
                'kind' => 'income',
                'amount' => (int) $order['total'],
                'date' => substr((string) $order['created_at'], 0, 10),
                'ref' => $ref,
                'party' => (string) $order['customer_name'],
                'description' => 'فروش فروشگاه — سفارش ' . $order['code'],
                'actor' => $this->actor(),
            ]);
            $imported++;
        }
        $this->log('وارد کردن فروش فروشگاه', $imported . ' سند');
        return $this->back('/admin/accounting/transactions', $imported > 0 ? $imported . ' فروش فروشگاه به‌عنوان درآمد ثبت شد.' : 'فروش تازه‌ای برای وارد کردن نبود.');
    }

    // ---------- حساب‌ها ----------

    public function accounts(Request $request): Response
    {
        $edit = null;
        $editId = (int) $request->input('edit', 0);
        if ($editId > 0) {
            $edit = $this->ledger()->account($editId);
        }
        return $this->page('accounts', [
            'accounts' => $this->ledger()->accounts(),
            'edit' => $edit,
            'types' => Ledger::ACCOUNT_TYPES,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'حساب‌های پولی');
    }

    public function accountSave(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/admin/accounting/accounts', '', 'عنوان حساب الزامی است.');
        }
        $type = (string) $request->input('type', 'bank');
        if (!isset(Ledger::ACCOUNT_TYPES[$type])) {
            $type = 'bank';
        }
        $this->ledger()->accountSave([
            'title' => $title,
            'type' => $type,
            'opening_balance' => (int) $request->input('opening_balance', 0),
            'active' => $request->input('active') ? 1 : 0,
            'note' => trim((string) $request->input('note', '')),
        ], $id);
        $this->log($id > 0 ? 'ویرایش حساب پولی' : 'ساخت حساب پولی', $title);
        return $this->back('/admin/accounting/accounts', 'حساب ذخیره شد.');
    }

    public function accountDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        if (!$this->ledger()->accountDelete($id)) {
            return $this->back('/admin/accounting/accounts', '', 'این حساب سند یا انتقال دارد و حذف نمی‌شود.');
        }
        $this->log('حذف حساب پولی', '');
        return $this->back('/admin/accounting/accounts', 'حساب حذف شد.');
    }

    // ---------- دسته‌ها ----------

    public function categories(Request $request): Response
    {
        $edit = null;
        $editId = (int) $request->input('edit', 0);
        if ($editId > 0) {
            $edit = $this->ledger()->category($editId);
        }
        return $this->page('categories', [
            'categories' => $this->ledger()->categories(),
            'edit' => $edit,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'دسته‌های درآمد و هزینه');
    }

    public function categorySave(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/admin/accounting/categories', '', 'عنوان دسته الزامی است.');
        }
        $this->ledger()->categorySave([
            'title' => $title,
            'kind' => $request->input('kind') === 'income' ? 'income' : 'expense',
            'sort' => (int) $request->input('sort', 0),
        ], $id);
        $this->log($id > 0 ? 'ویرایش دسته حسابداری' : 'ساخت دسته حسابداری', $title);
        return $this->back('/admin/accounting/categories', 'دسته ذخیره شد.');
    }

    public function categoryDelete(Request $request, array $params): Response
    {
        $this->ledger()->categoryDelete((int) ($params['id'] ?? 0));
        return $this->back('/admin/accounting/categories', 'دسته حذف شد (اسنادش بدون دسته شدند).');
    }

    // ---------- انتقال‌ها ----------

    public function transfers(Request $request): Response
    {
        return $this->page('transfers', [
            'transfers' => $this->ledger()->transfers(),
            'accounts' => $this->ledger()->accounts(true),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'انتقال بین حساب‌ها');
    }

    public function transferSave(Request $request): Response
    {
        $date = trim((string) $request->input('date', date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $this->back('/admin/accounting/transfers', '', 'تاریخ نامعتبر است.');
        }
        $result = $this->ledger()->transfer(
            (int) $request->input('from_account', 0),
            (int) $request->input('to_account', 0),
            (int) $request->input('amount', 0),
            $date,
            trim((string) $request->input('note', '')),
            $this->actor()
        );
        if (!$result['ok']) {
            return $this->back('/admin/accounting/transfers', '', (string) $result['error']);
        }
        $this->log('انتقال بین حساب‌ها', '');
        return $this->back('/admin/accounting/transfers', 'انتقال انجام شد.');
    }

    public function transferDelete(Request $request, array $params): Response
    {
        $this->ledger()->transferDelete((int) ($params['id'] ?? 0));
        return $this->back('/admin/accounting/transfers', 'انتقال حذف شد.');
    }

    // ---------- گزارش‌ها ----------

    public function reports(Request $request): Response
    {
        $from = trim((string) $request->input('from', ''));
        $to = trim((string) $request->input('to', ''));
        return $this->page('reports', [
            'monthly' => $this->ledger()->monthlyRows(),
            'categoryTotals' => $this->ledger()->categoryTotals($from, $to),
            'from' => $from,
            'to' => $to,
            'total' => $this->ledger()->totalBalance(),
        ], 'گزارش‌های حسابداری');
    }
}
