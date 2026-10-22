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
        $page = max(1, (int) $request->input('page', 1));
        $total = $this->logger()->visitsCount();
        return $this->page('visits', 'گزارش بازدیدها', [
            'stats' => $this->logger()->visitStats(),
            'rows'  => $this->logger()->recentVisits(self::PAGE_SIZE, ($page - 1) * self::PAGE_SIZE),
            'page'  => $page,
            'pages' => max(1, (int) ceil($total / self::PAGE_SIZE)),
            'flash' => $request->input('msg'),
            'csrf'  => $this->session()->csrfToken(),
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
