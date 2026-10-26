<?php
declare(strict_types=1);

namespace Modules\Personal;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** فضای شخصی کاربر در /me: فقط داده‌های خودش، به‌جز آیتم‌های اشتراکی. */
final class MeController
{
    public function __construct(private readonly Container $container) {}

    private function store(): Personal
    {
        return $this->container->get('personal.store');
    }

    private function session(): \Modules\Session\SessionManager
    {
        return $this->container->get('session');
    }

    /** کاربر لاگین‌کرده یا ریدایرکت به ورود. */
    private function user(): ?array
    {
        return $this->session()->user();
    }

    private function requireUser(): ?Response
    {
        if ($this->user() === null) {
            return Response::redirect('/admin/login');
        }
        return null;
    }

    /** CSRF دستی برای POSTهای /me (هوک سشن فقط /admin را پوشش می‌دهد). */
    private function csrfOk(Request $request): bool
    {
        return $this->session()->validCsrf($request->input('_csrf'));
    }

    private function page(Request $request, string $view, array $data, string $title, string $tab): Response
    {
        $session = $this->session();
        $inner = View::render('personal', 'me', $view, $data + [
            'csrf' => $session->csrfToken(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ]);
        return Response::html(View::render('personal', 'me', 'layout', [
            'title' => $title,
            'content' => $inner,
            'user' => $session->user(),
            'tab' => $tab,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ]));
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

    /** باکس اشتراک‌گذاری برای صفحه پروژه/نوت (فرم کاربر + لینک + فهرست‌ها). */
    private function shareBox(string $type, int $itemId): string
    {
        $user = $this->user();
        if ($user === null) {
            return '';
        }
        return View::render('personal', 'me', 'sharebox', [
            'type' => $type,
            'item_id' => $itemId,
            'shares' => $this->store()->sharesFor((int) $user['id'], $type, $itemId),
            'links' => $this->store()->linksFor((int) $user['id'], $type, $itemId),
            'csrf' => $this->session()->csrfToken(),
            'type_label' => Personal::SHAREABLE[$type] ?? $type,
        ]);
    }

    // ---------- داشبورد ----------

    public function dashboard(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $user = $this->user();
        $uid = (int) $user['id'];
        $today = date('Y-m-d');
        $store = $this->store();
        return $this->page($request, 'dashboard', [
            'today' => $today,
            'today_label' => Personal::dayLabel($today) . '، ' . Personal::faWeekday($today) . ' ' . Personal::bothDates($today),
            'events' => $store->eventsForDay($uid, $today),
            'todos' => array_slice(array_filter(
                $store->todos($uid),
                static fn (array $t): bool => (int) $t['done'] === 0
            ), 0, 20),
            'open_todos' => count(array_filter(
                $store->todos($uid),
                static fn (array $t): bool => (int) $t['done'] === 0
            )),
            'projects' => $store->projects($uid),
            'notes' => $store->notes($uid),
            'shared' => $store->sharedWithMe($uid),
        ], 'مدیریت شخصی', 'home');
    }

    // ---------- روز و هفته ----------

    public function day(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        $uid = (int) $this->user()['id'];
        return $this->page($request, 'day', [
            'date' => $date,
            'label' => Personal::dayLabel($date) . '، ' . Personal::faWeekday($date) . ' ' . Personal::bothDates($date),
            'prev' => date('Y-m-d', strtotime($date . ' -1 day')),
            'next' => date('Y-m-d', strtotime($date . ' +1 day')),
            'events' => $this->store()->eventsForDay($uid, $date),
            'hours' => range(0, 23),
        ], 'روز', 'day');
    }

    public function week(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        $monday = Personal::weekMonday($date);
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $days[] = date('Y-m-d', strtotime($monday . " +{$i} days"));
        }
        return $this->page($request, 'week', [
            'monday' => $monday,
            'sunday' => $days[6],
            'prev' => date('Y-m-d', strtotime($monday . ' -7 days')),
            'next' => date('Y-m-d', strtotime($monday . ' +7 days')),
            'days' => $days,
            'grouped' => $this->store()->eventsForWeek((int) $this->user()['id'], $monday),
        ], 'هفته', 'week');
    }

    // ---------- تودو ----------

    public function todos(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        return $this->page($request, 'todos', [
            'todos' => $this->store()->todos((int) $this->user()['id']),
        ], 'کارها', 'todos');
    }

    public function todoCreate(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $title = trim((string) $request->input('title', ''));
        $due = trim((string) $request->input('due_date', ''));
        if ($title === '') {
            return $this->back('/me/todos', '', 'عنوان کار را بنویسید.');
        }
        if ($due !== '' && !Personal::validDate($due)) {
            return $this->back('/me/todos', '', 'تاریخ سررسید معتبر نیست.');
        }
        $this->store()->todoCreate((int) $this->user()['id'], $title, $due);
        $from = (string) $request->input('from', '/me/todos');
        return $this->back(str_starts_with($from, '/me') ? $from : '/me/todos', 'کار اضافه شد.');
    }

    public function todoToggle(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $this->store()->todoToggle((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/todos');
    }

    public function todoDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $this->store()->todoDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/todos', 'کار حذف شد.');
    }

    // ---------- رویداد ----------

    public function eventCreate(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $title = trim((string) $request->input('title', ''));
        $date = trim((string) $request->input('date', ''));
        $start = trim((string) $request->input('start_time', ''));
        $end = trim((string) $request->input('end_time', ''));
        $note = trim((string) $request->input('note', ''));
        if ($title === '' || !Personal::validDate($date) || !Personal::validTime($start) || !Personal::validTime($end)) {
            return $this->back('/me/day?date=' . $date, '', 'عنوان، تاریخ و ساعت شروع/پایان معتبر لازم است.');
        }
        if ($end < $start) {
            return $this->back('/me/day?date=' . $date, '', 'ساعت پایان باید بعد از شروع باشد.');
        }
        $this->store()->eventCreate((int) $this->user()['id'], $title, $date, $start, $end, $note);
        return $this->back('/me/day?date=' . $date, 'رویداد اضافه شد.');
    }

    public function eventDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $date = (string) $request->input('date', date('Y-m-d'));
        $this->store()->eventDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/day?date=' . $date, 'رویداد حذف شد.');
    }

    // ---------- نوت ----------

    public function notes(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        return $this->page($request, 'notes', [
            'notes' => $this->store()->notes((int) $this->user()['id']),
        ], 'یادداشت‌ها', 'notes');
    }

    public function noteCreate(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/me/notes', '', 'عنوان یادداشت را بنویسید.');
        }
        $id = $this->store()->noteCreate((int) $this->user()['id'], $title);
        return $this->back('/me/notes/' . $id, 'یادداشت ساخته شد.');
    }

    public function noteShow(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $note = $this->store()->note((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        if ($note === null) {
            return Response::notFound('یادداشت پیدا نشد');
        }
        return $this->page($request, 'note', [
            'note' => $note,
            'shareBox' => $this->shareBox('note', (int) $note['id']),
        ], 'یادداشت', 'notes');
    }

    public function noteSave(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $id = (int) ($params['id'] ?? 0);
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/me/notes/' . $id, '', 'عنوان یادداشت را بنویسید.');
        }
        if (!$this->store()->noteSave((int) $this->user()['id'], $id, $title, (string) $request->input('body', ''))) {
            return Response::notFound('یادداشت پیدا نشد');
        }
        return $this->back('/me/notes/' . $id, 'یادداشت ذخیره شد.');
    }

    public function noteDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $this->store()->noteDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/notes', 'یادداشت حذف شد.');
    }

    // ---------- پروژه ----------

    public function projects(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        return $this->page($request, 'projects', [
            'projects' => $this->store()->projects((int) $this->user()['id']),
        ], 'پروژه‌ها', 'projects');
    }

    public function projectCreate(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $title = trim((string) $request->input('title', ''));
        $start = trim((string) $request->input('start_date', ''));
        $hours = max(0, (int) $request->input('hours_needed', 0));
        if ($title === '') {
            return $this->back('/me/projects', '', 'عنوان پروژه را بنویسید.');
        }
        if ($start !== '' && !Personal::validDate($start)) {
            return $this->back('/me/projects', '', 'تاریخ شروع معتبر نیست.');
        }
        $id = $this->store()->projectCreate(
            (int) $this->user()['id'], $title,
            trim((string) $request->input('description', '')), $start, $hours
        );
        return $this->back('/me/projects/' . $id, 'پروژه ساخته شد.');
    }

    public function projectShow(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $project = $this->store()->project((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        if ($project === null) {
            return Response::notFound('پروژه پیدا نشد');
        }
        return $this->page($request, 'project', [
            'project' => $project,
            'needs' => $this->store()->needs((int) $project['id']),
            'shareBox' => $this->shareBox('project', (int) $project['id']),
        ], 'پروژه', 'projects');
    }

    public function projectSave(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $id = (int) ($params['id'] ?? 0);
        $title = trim((string) $request->input('title', ''));
        $start = trim((string) $request->input('start_date', ''));
        if ($title === '' || ($start !== '' && !Personal::validDate($start))) {
            return $this->back('/me/projects/' . $id, '', 'عنوان و تاریخ شروع معتبر لازم است.');
        }
        if (!$this->store()->projectSave(
            (int) $this->user()['id'], $id, $title,
            trim((string) $request->input('description', '')), $start,
            max(0, (int) $request->input('hours_needed', 0))
        )) {
            return Response::notFound('پروژه پیدا نشد');
        }
        return $this->back('/me/projects/' . $id, 'پروژه ذخیره شد.');
    }

    public function projectDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $this->store()->projectDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/projects', 'پروژه حذف شد.');
    }

    public function needAdd(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $projectId = (int) ($params['id'] ?? 0);
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/me/projects/' . $projectId, '', 'عنوان نیاز را بنویسید.');
        }
        if (!$this->store()->needAdd((int) $this->user()['id'], $projectId, $title)) {
            return Response::notFound('پروژه پیدا نشد');
        }
        return $this->back('/me/projects/' . $projectId, 'نیاز اضافه شد.');
    }

    public function needToggle(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $projectId = (int) $request->input('project_id', 0);
        $this->store()->needToggle((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/projects/' . $projectId);
    }

    public function needDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $projectId = (int) $request->input('project_id', 0);
        $this->store()->needDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/projects/' . $projectId, 'نیاز حذف شد.');
    }

    // ---------- اشتراک‌گذاری ----------

    public function shareUser(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $type = (string) $request->input('type', '');
        $itemId = (int) $request->input('item_id', 0);
        $username = trim((string) $request->input('username', ''));
        $back = $type === 'project' ? '/me/projects/' . $itemId : '/me/notes/' . $itemId;
        if (!isset(Personal::SHAREABLE[$type]) || $username === '') {
            return $this->back($back, '', 'نوع آیتم یا نام کاربری نامعتبر است.');
        }
        $status = $this->store()->shareWithUser((int) $this->user()['id'], $type, $itemId, $username);
        $msg = match ($status) {
            'ok' => 'با ' . $username . ' به اشتراک گذاشته شد.',
            'not_found' => 'کاربری با این نام پیدا نشد.',
            'self' => 'نمی‌توانید با خودتان به اشتراک بگذارید.',
            'exists' => 'این آیتم قبلاً با این کاربر به اشتراک گذاشته شده است.',
            default => 'اشتراک‌گذاری ممکن نشد.',
        };
        return $status === 'ok' ? $this->back($back, $msg) : $this->back($back, '', $msg);
    }

    public function shareLink(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $type = (string) $request->input('type', '');
        $itemId = (int) $request->input('item_id', 0);
        $back = $type === 'project' ? '/me/projects/' . $itemId : '/me/notes/' . $itemId;
        if (!isset(Personal::SHAREABLE[$type])) {
            return $this->back($back, '', 'نوع آیتم نامعتبر است.');
        }
        $token = $this->store()->createLink((int) $this->user()['id'], $type, $itemId);
        if ($token === null) {
            return $this->back($back, '', 'ساخت لینک ممکن نشد.');
        }
        return $this->back($back, 'لینک عمومی ساخته شد.');
    }

    public function revokeShare(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $type = (string) $request->input('type', '');
        $itemId = (int) $request->input('item_id', 0);
        $this->store()->revokeShare((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        $back = $type === 'project' ? '/me/projects/' . $itemId : '/me/notes/' . $itemId;
        return $this->back($back, 'دسترسی کاربر برداشته شد.');
    }

    public function revokeLink(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $type = (string) $request->input('type', '');
        $itemId = (int) $request->input('item_id', 0);
        $this->store()->revokeLink((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        $back = $type === 'project' ? '/me/projects/' . $itemId : '/me/notes/' . $itemId;
        return $this->back($back, 'لینک عمومی باطل شد.');
    }

    // ---------- دیده‌شده با من ----------

    public function shared(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        return $this->page($request, 'shared', [
            'shared' => $this->store()->sharedWithMe((int) $this->user()['id']),
        ], 'اشتراک‌گذاشته‌شده با من', 'shared');
    }

    public function sharedView(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $type = (string) ($params['type'] ?? '');
        $id = (int) ($params['id'] ?? 0);
        if (!isset(Personal::SHAREABLE[$type]) || !$this->store()->canView((int) $this->user()['id'], $type, $id)) {
            return Response::notFound('یافت نشد');
        }
        $item = $this->store()->itemAny($type, $id);
        if ($item === null) {
            return Response::notFound('یافت نشد');
        }
        return $this->page($request, 'shared_view', [
            'type' => $type,
            'type_label' => Personal::SHAREABLE[$type],
            'item' => $item,
            'needs' => $type === 'project' ? $this->store()->needs($id) : [],
            'readonly' => true,
        ], 'مشاهده اشتراکی', 'shared');
    }

    // ---------- لینک عمومی (بدون ورود) ----------

    public function publicShare(Request $request, array $params): Response
    {
        $resolved = $this->store()->resolveLink((string) ($params['token'] ?? ''));
        if ($resolved === null) {
            return Response::notFound('این لینک معتبر نیست یا باطل شده است.');
        }
        $item = $resolved['item'];
        $inner = View::render('personal', 'me', 'public', [
            'type' => $resolved['type'],
            'type_label' => Personal::SHAREABLE[$resolved['type']] ?? '',
            'item' => $item,
            'needs' => $resolved['type'] === 'project' ? $this->store()->needs((int) $item['id']) : [],
            'owner' => $resolved['owner'],
        ]);
        return Response::html(View::render('personal', 'me', 'layout_public', [
            'title' => (string) $item['title'],
            'content' => $inner,
        ]));
    }
}
