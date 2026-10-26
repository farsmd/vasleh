<?php
declare(strict_types=1);

namespace Modules\Admin;

use Core\Container;
use Core\Response;
use Core\View;
use Modules\Shop\Orders;

final class AdminController
{
    public function __construct(private readonly Container $container) {}

    /** چیدن صفحه‌ای از بخش مدیریت داخل لی‌اوت مشترک. */
    public function page(string $view, array $data, string $title): Response
    {
        $loader = $this->container->get('loader');
        $session = $this->container->has('session') ? $this->container->get('session') : null;
        $content = View::render('admin', 'admin', $view, $data);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => $title,
            'content' => $content,
            'menu'    => $loader->adminMenu(),
            'user'    => $session?->user(),
            'csrf'    => $session?->csrfToken() ?? '',
        ]));
    }

    public function index(): Response
    {
        $loader = $this->container->get('loader');
        $c = $this->container;
        $today = date('Y-m-d');
        $weekdays = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];

        $sales30 = ['count' => 0, 'sum' => 0];
        $salesPrev = ['count' => 0, 'sum' => 0];
        $recentOrders = [];
        if ($c->has('shop.orders')) {
            $o = $c->get('shop.orders');
            $now = date('Y-m-d H:i:s');
            $sales30 = $o->salesBetween(date('Y-m-d H:i:s', time() - 30 * 86400), $now);
            $salesPrev = $o->salesBetween(date('Y-m-d H:i:s', time() - 60 * 86400), date('Y-m-d H:i:s', time() - 30 * 86400));
            $recentOrders = array_slice($o->all(), 0, 5);
        }
        $prod = ['recipes' => 0, 'planned' => 0, 'running' => 0, 'done' => 0];
        if ($c->has('production.manager')) {
            $prod = $c->get('production.manager')->stats();
        }
        $inv = ['warehouses' => 0, 'items' => 0, 'low' => 0, 'moves' => 0];
        if ($c->has('inventory.stock')) {
            $inv = $c->get('inventory.stock')->stats();
        }
        $month = ['income' => 0, 'expense' => 0, 'net' => 0];
        $monthPrev = ['income' => 0, 'expense' => 0, 'net' => 0];
        $chart = [];
        if ($c->has('accounting.ledger')) {
            $ledger = $c->get('accounting.ledger');
            $month = $ledger->monthSummary(date('Y-m'));
            $monthPrev = $ledger->monthSummary(date('Y-m', strtotime('first day of last month')));
            $daily = $ledger->dailyTotals(date('Y-m-d', time() - 6 * 86400), $today);
            for ($i = 6; $i >= 0; $i--) {
                $t = time() - $i * 86400;
                $d = date('Y-m-d', $t);
                $chart[] = [
                    'date' => $d,
                    'day' => $weekdays[(int) date('w', $t)],
                    'income' => (int) ($daily[$d]['income'] ?? 0),
                    'expense' => (int) ($daily[$d]['expense'] ?? 0),
                ];
            }
        }
        $tasks = [];
        $username = '';
        $csrf = '';
        if ($c->has('session')) {
            $session = $c->get('session');
            $csrf = $session->csrfToken();
            $u = $session->user();
            if (is_array($u)) {
                $username = (string) ($u['username'] ?? '');
                if ($c->has('personal.store')) {
                    $tasks = array_slice($c->get('personal.store')->tasksForRange((int) $u['id'], $today, $today), 0, 5);
                }
            }
        }
        $version = '';
        $versionFile = $c->get('base_path') . '/version.json';
        if (is_file($versionFile)) {
            $parts = json_decode((string) file_get_contents($versionFile), true);
            if (is_array($parts)) {
                $version = (int) $parts['core'] . '.' . (int) $parts['system'] . '.' . (int) $parts['other'];
            }
        }
        $enabledCount = 0;
        foreach ($loader->all() as $slug => $m) {
            if ($loader->isEnabled($slug)) {
                $enabledCount++;
            }
        }
        return $this->page('dashboard', [
            'modules' => $loader->all(),
            'errors'  => $loader->errors,
            'loader'  => $loader,
            'enabledCount' => $enabledCount,
            'username' => $username,
            'version' => $version,
            'csrf' => $csrf,
            'sales30' => $sales30,
            'salesPrev' => $salesPrev,
            'prod' => $prod,
            'inv' => $inv,
            'month' => $month,
            'monthPrev' => $monthPrev,
            'chart' => $chart,
            'orders' => $recentOrders,
            'tasks' => $tasks,
            'orderStatuses' => Orders::STATUSES,
        ], 'داشبورد مدیریت');
    }
}
