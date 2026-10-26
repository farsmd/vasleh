<?php
declare(strict_types=1);

namespace Modules\Reports;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;
use Modules\Session\SessionManager;

/** صفحات گزارشات — فقط مالک (IP و رفتار کاربران داده حساسی است). */
final class ReportsController
{
    private const PAGE_SIZE = 50;

    public function __construct(private readonly Container $container) {}

    private function session(): SessionManager
    {
        return $this->container->get('session');
    }

    private function logger(): Logger
    {
        return $this->container->get('logger');
    }

    private function ownerOnly(): ?Response
    {
        if (($this->session()->user()['role'] ?? '') !== 'owner') {
            return Response::forbidden('مشاهده گزارشات فقط برای مالک است');
        }
        return null;
    }

    private function page(string $view, string $title, array $data): Response
    {
        $session = $this->session();
        $content = View::render('reports', 'admin', $view, $data);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => $title,
            'content' => $content,
            'menu'    => $this->container->get('loader')->adminMenu(),
            'user'    => $session->user(),
            'csrf'    => $session->csrfToken(),
        ]));
    }

    public function log(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $kind = (string) $request->input('kind', '');
        $kind = in_array($kind, ['system', 'user'], true) ? $kind : null;
        $page = max(1, (int) $request->input('page', 1));
        $total = $this->logger()->eventsCount($kind);
        $rows = $this->logger()->events($kind, self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE);
        return $this->page('log', 'لاگ سیستم', [
            'rows'  => $rows,
            'kind'  => $kind,
            'page'  => $page,
            'pages' => max(1, (int) ceil($total / self::PAGE_SIZE)),
            'total' => $total,
        ]);
    }

    public function users(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $actor = trim((string) $request->input('user', ''));
        return $this->page('users', 'فعالیت کاربران', [
            'summary'     => $this->logger()->userSummary(),
            'actor'       => $actor,
            'events'      => $actor !== '' ? $this->logger()->userEvents($actor, 50) : [],
            'actorVisits' => $actor !== '' ? $this->logger()->userVisits($actor, 20) : [],
        ]);
    }

    public function visits(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $range = (string) $request->input('range', '7');
        $range = in_array($range, ['7', '30', '90'], true) ? (int) $range : 7;
        $since = gmdate('Y-m-d', time() - ($range - 1) * 86400) . ' 00:00:00';
        $q = trim((string) $request->input('q', ''));
        $os = (string) $request->input('os', '');
        $os = in_array($os, ['windows', 'macos', 'android', 'ios', 'linux'], true) ? $os : '';
        $page = max(1, (int) $request->input('page', 1));
        $total = $this->logger()->filteredVisitsCount($since, $q, $os);
        return $this->page('visits', 'گزارش بازدیدها', [
            'stats'    => $this->logger()->visitStats(),
            'active5'  => $this->logger()->activeIps(5),
            'daily'    => $this->logger()->visitsByDay($range),
            'browsers' => $this->logger()->browserShares($since),
            'rows'     => $this->logger()->filteredVisits($since, $q, $os, self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE),
            'page'     => $page,
            'pages'    => max(1, (int) ceil($total / self::PAGE_SIZE)),
            'range'    => $range,
            'q'        => $q,
            'os'       => $os,
            'flash'    => $request->input('msg'),
            'csrf'     => $this->session()->csrfToken(),
        ]);
    }

    public function prune(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $days = (int) $request->input('days', 90);
        $deleted = $this->logger()->pruneVisits($days);
        if ($this->container->has('logger')) {
            $this->logger()->user(
                (string) ($this->session()->user()['username'] ?? ''),
                'پاک‌سازی بازدیدهای قدیمی',
                $deleted . ' ردیف (بیش از ' . $days . ' روز)',
            );
        }
        return Response::redirect('/admin/reports/visits?msg=' . urlencode($deleted . ' ردیف بازدید قدیمی پاک شد'));
    }
}
