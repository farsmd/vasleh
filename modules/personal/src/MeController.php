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

    /** آیا متد تازهٔ استور پیاده شده است؟ (تا زمان لندینگ موازی، فیتال نمی‌دهیم.) */
    private function storeHas(string $method): bool
    {
        return method_exists($this->store(), $method);
    }

    /** بازگشت به مسیر from (فقط داخل /me) یا مسیر پیش‌فرض. */
    private function backFrom(Request $request, string $default, string $msg = '', string $err = ''): Response
    {
        $from = (string) $request->input('from', '');
        return $this->back(str_starts_with($from, '/me') ? $from : $default, $msg, $err);
    }

    /** برش امن UTF-8 بدون mbstring (هاست mbstring ندارد). */
    private function trunc(string $s, int $n): string
    {
        if (preg_match('/^.{0,' . $n . '}/su', $s, $m) === 1) {
            return $m[0];
        }
        return $s;
    }

    /** «a, b, a» → فهرست یکتا و تمیز برچسب‌ها. */
    private function parseTags(string $raw): array
    {
        $out = [];
        foreach (explode(',', $raw) as $part) {
            $t = trim($part);
            if ($t !== '' && !in_array($t, $out, true)) {
                $out[] = $this->trunc($t, 50);
            }
        }
        return array_slice($out, 0, 20);
    }

    /** فقط فیلدهای رشته‌ای مجاز در نماهای ذخیره‌شده؛ بقیه حذف می‌شوند. */
    private function sanitizeSavedFilters(mixed $raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $f) {
            if (!is_array($f)) {
                continue;
            }
            $item = [
                'name' => $this->trunc(trim((string) ($f['name'] ?? '')), 100),
                'tag' => $this->trunc(trim((string) ($f['tag'] ?? '')), 50),
                'category' => $this->trunc(trim((string) ($f['category'] ?? '')), 50),
                'priority' => $this->trunc(trim((string) ($f['priority'] ?? '')), 20),
            ];
            if ($item['name'] !== '' || $item['tag'] !== '' || $item['category'] !== '' || $item['priority'] !== '') {
                $out[] = $item;
            }
        }
        return $out;
    }

    private function clampDayCapacity(mixed $v, int $fallback): int
    {
        if ($v === null || $v === '') {
            return $fallback;
        }
        return max(60, min(1440, (int) $v));
    }

    /** آرایهٔ کامل تنظیمات برای ذخیرهٔ مجدد (بدون کلید محاسباتی category_keys). */
    private function fullSettingsForSave(int $uid): array
    {
        $current = $this->store()->settings($uid);
        unset($current['category_keys']);
        return $current;
    }

    /** نوت‌های لینک‌شده به یک پروژه (جست‌وجوی معکوس روی noteLinks هر نوت). */
    private function linkedNotesForProject(int $uid, int $projectId): array
    {
        if (!$this->storeHas('noteLinks')) {
            return [];
        }
        $out = [];
        foreach ($this->store()->notes($uid) as $note) {
            foreach ((array) $this->store()->noteLinks($uid, (int) $note['id']) as $link) {
                if ((string) ($link['item_type'] ?? '') === 'project' && (int) ($link['item_id'] ?? 0) === $projectId) {
                    $out[] = $note + ['link_id' => (int) ($link['id'] ?? 0)];
                    break;
                }
            }
        }
        return $out;
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

    // ---------- تسک‌یار ----------

    /** صفحه اصلی مدیریت شخصی با پوسته تسک‌یار و داده یکپارچه تسک‌ها. */
    public function tasks(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        $weekSat = Personal::weekSaturday($date);
        $weekSun = date('Y-m-d', strtotime($weekSat . ' +6 days'));
        $monthFirst = substr($date, 0, 7) . '-01';
        $monthLast = date('Y-m-t', strtotime($date));
        $from = min($weekSat, $monthFirst);
        $to = max($weekSun, $monthLast);
        $settings = $this->store()->settings((int) $this->user()['id']);
        $view = (string) $request->input('view', $settings['default_view']);
        if (!in_array($view, ['dashboard', 'hourly', 'daily', 'weekly', 'monthly'], true)) {
            $view = $settings['default_view'];
        }
        // کارت‌های پروژه و یادداشت صفحه اصلی: همان دادهٔ صفحه‌های خودشان، بدون روت تازه.
        $uid = (int) $this->user()['id'];
        $bentoProjects = [];
        foreach ($this->store()->projects($uid) as $pr) {
            $needs = $this->store()->needs((int) $pr['id']);
            $done = count(array_filter($needs, static fn ($n) => (int) ($n['done'] ?? 0) === 1));
            $bentoProjects[] = $pr + ['needs_done' => $done, 'needs_total' => count($needs)];
        }
        // فیلترهای نمای اصلی (?tag=&category=&priority=) فقط روی خروجی همین نما.
        // توجه: فیلتر tag به فیلد tags آیتم‌ها وابسته است که استور باید به
        // tasksForRange() اضافه کند؛ تا آن زمان فیلتر tag نتیجه‌ای نمی‌دهد.
        $mainTasks = $this->store()->tasksForRange($uid, $from, $to);
        $filterTag = trim((string) $request->input('tag', ''));
        $filterCategory = trim((string) $request->input('category', ''));
        $filterPriority = trim((string) $request->input('priority', ''));
        if ($filterTag !== '' || $filterCategory !== '' || $filterPriority !== '') {
            $mainTasks = array_values(array_filter($mainTasks, static function ($t) use ($filterTag, $filterCategory, $filterPriority): bool {
                if ($filterTag !== '' && !in_array($filterTag, (array) ($t['tags'] ?? []), true)) {
                    return false;
                }
                if ($filterCategory !== '' && (string) ($t['category'] ?? '') !== $filterCategory) {
                    return false;
                }
                if ($filterPriority !== '' && (string) ($t['priority'] ?? '') !== $filterPriority) {
                    return false;
                }
                return true;
            }));
        }
        return Response::html(View::render('personal', 'me', 'taskyar', [
            'tasks' => $mainTasks,
            'savedFilters' => $this->sanitizeSavedFilters($settings['saved_filters'] ?? []),
            'workload' => $this->storeHas('workloadForDay')
                ? (int) $this->store()->workloadForDay($uid, $date) : 0,
            'projects' => $bentoProjects,
            'notes' => $this->store()->notes($uid),
            'today' => date('Y-m-d'),
            'date' => $date,
            'weekSat' => $weekSat,
            'weekSun' => $weekSun,
            'monthFirst' => $monthFirst,
            'monthLast' => $monthLast,
            'view' => $view,
            'settings' => $settings,
            'csrf' => $this->session()->csrfToken(),
            'user' => $this->user(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ]));
    }

    /** پوسته تسک‌یار برای صفحه‌های فرعی (نوت، پروژه، اشتراک). */
    private function page2(Request $request, string $view, array $data, string $title, string $tab): Response
    {
        $session = $this->session();
        $inner = View::render('personal', 'me', $view, $data + [
            'csrf' => $session->csrfToken(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ]);
        $meUser = $session->user();
        return Response::html(View::render('personal', 'me', 'taskyar_layout', [
            'title' => $title,
            'content' => $inner,
            'user' => $meUser,
            'tab' => $tab,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
            'public' => false,
            'me_style' => $meUser === null ? 'darkPrism'
                : ($this->store()->settings((int) $meUser['id'])['style'] ?? 'darkPrism'),
        ]));
    }

    // ---------- داشبورد شخصی ----------

    public function dashboard(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $store = $this->store();
        $today = date('Y-m-d');
        $weekSat = Personal::weekSaturday($today);
        $weekSun = date('Y-m-d', strtotime($weekSat . ' +6 days'));
        $weekTasks = $store->tasksForRange($uid, $weekSat, $weekSun);
        $todayTasks = array_values(array_filter($weekTasks, static fn ($t) => ($t['date'] ?? '') === $today));
        $todos = $store->todos($uid);
        $overdue = array_values(array_filter($todos, static fn ($t) => (int) ($t['done'] ?? 0) === 0 && ($t['due_date'] ?? '') !== '' && $t['due_date'] < $today));
        $projects = [];
        foreach ($store->projects($uid) as $pr) {
            $needs = $store->needs((int) $pr['id']);
            $done = count(array_filter($needs, static fn ($n) => (int) ($n['done'] ?? 0) === 1));
            $projects[] = $pr + ['needs' => $needs, 'needs_done' => $done, 'needs_total' => count($needs)];
        }
        $weekDone = count(array_filter($weekTasks, static fn ($t) => (int) ($t['done'] ?? 0) === 1));
        $todayDone = count(array_filter($todayTasks, static fn ($t) => (int) ($t['done'] ?? 0) === 1));
        return $this->page2($request, 'dashboard', [
            'today' => $today,
            'todayTasks' => $todayTasks,
            'todayDone' => $todayDone,
            'todayEvents' => $store->eventsForDay($uid, $today),
            'weekTasks' => $weekTasks,
            'weekDone' => $weekDone,
            'weekSat' => $weekSat,
            'weekSun' => $weekSun,
            'todos' => $todos,
            'overdue' => $overdue,
            'projects' => $projects,
            'notesCount' => count($store->notes($uid)),
        ], 'داشبورد شخصی', 'dashboard');
    }

    // ---------- تنظیمات شخصی ----------

    public function settings(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $settings = $this->store()->settings((int) $this->user()['id']);
        $lines = [];
        foreach ($settings['categories'] as $c) {
            $lines[] = $c['key'] . '|' . $c['label'];
        }
        return $this->page2($request, 'settings', [
            'settings' => $settings,
            'categories_text' => implode("\n", $lines),
            'user' => $this->user(),
        ], 'تنظیمات شخصی', 'settings');
    }

    public function settingsSave(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $uid = (int) $this->user()['id'];
        $current = $this->store()->settings($uid);
        $cats = Personal::parseCategories((string) $request->input('categories_text', ''));
        if ($cats === []) {
            $cats = Personal::DEFAULT_CATEGORIES;
        }
        $view = (string) $request->input('default_view', $current['default_view']);
        if (!in_array($view, ['dashboard', 'hourly', 'daily', 'weekly', 'monthly'], true)) {
            $view = $current['default_view'];
        }
        $new = $current;
        unset($new['category_keys']);
        $new['default_view'] = $view;
        $new['categories'] = $cats;
        $new['pomo_focus'] = max(1, min(180, (int) $request->input('pomo_focus', $current['pomo_focus'])));
        $new['pomo_break'] = max(1, min(60, (int) $request->input('pomo_break', $current['pomo_break'])));
        $new['dark_default'] = (bool) $request->input('dark_default');
        $style = (string) $request->input('style', $current['style'] ?? 'darkPrism');
        $new['style'] = isset(Personal::STYLES[$style]) ? $style : ($current['style'] ?? 'darkPrism');
        // گذر day_capacity و saved_filters: اگر Personal::settings() این دو کلید
        // را در defaults سفید نکرده باشد، خواندن آن‌ها را برنمی‌گرداند و ذخیرهٔ
        // تنظیمات، فیلترهای ذخیره‌شدهٔ قبلی را از JSON می‌اندازد (وابستگی گزارش‌شده).
        $new['day_capacity'] = $this->clampDayCapacity(
            $request->input('day_capacity', $current['day_capacity'] ?? 480),
            (int) ($current['day_capacity'] ?? 480)
        );
        if ($request->input('saved_filters', null) !== null) {
            $new['saved_filters'] = $this->sanitizeSavedFilters($request->input('saved_filters'));
        } elseif (isset($current['saved_filters'])) {
            $new['saved_filters'] = $this->sanitizeSavedFilters($current['saved_filters']);
        }
        $this->store()->settingsSave($uid, $new);
        return $this->back('/me/settings', 'تنظیمات ذخیره شد.');
    }

    // ---------- فایل‌های PWA تسک‌یار (بدون نیاز به ورود؛ داده کاربر ندارند) ----------

    public function pwaManifest(Request $request): Response
    {
        $file = dirname(__DIR__, 3) . '/assets/pwa/manifest.webmanifest';
        if (!is_file($file)) {
            return Response::notFound('منیفست پیدا نشد');
        }
        return new Response((string) file_get_contents($file), 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function pwaServiceWorker(Request $request): Response
    {
        $file = dirname(__DIR__, 3) . '/assets/pwa/sw.js';
        if (!is_file($file)) {
            return Response::notFound('سرویس‌ورکر پیدا نشد');
        }
        return new Response((string) file_get_contents($file), 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache',
            'Service-Worker-Allowed' => '/me/',
        ]);
    }

    // ریدایرکت مسیرهای قدیمی به نمای تازه
    public function dayRedirect(Request $request): Response
    {
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        return Response::redirect('/me?date=' . $date . '&view=daily');
    }

    /** تقویم ماهانه: گرید ماه + نوار هفته + تایم‌لاین (ویوی calendar.php). */
    public function calendar(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        // بازهٔ کارها = ماه شمسیِ روز فعال (نه ماه میلادی)، تا لبه‌های ماه جا نمانند.
        [$__jy, $__jm] = Personal::toJalali((int) substr($date, 0, 4), (int) substr($date, 5, 2), (int) substr($date, 8, 2));
        [$__gy, $__gm, $__gd] = Personal::fromJalali($__jy, $__jm, 1);
        $monthFirst = sprintf('%04d-%02d-%02d', $__gy, $__gm, $__gd);
        $monthLast = date('Y-m-d', strtotime($monthFirst . ' +' . (Personal::jalaliMonthDays($__jy, $__jm) - 1) . ' days'));
        $settings = $this->store()->settings($uid);
        return $this->page2($request, 'calendar', [
            'tasks' => $this->store()->tasksForRange($uid, $monthFirst, $monthLast),
            'month' => substr($date, 0, 7),
            'monthFirst' => $monthFirst,
            'today' => date('Y-m-d'),
            'date' => $date,
            'workload' => $this->storeHas('workloadForDay') ? (int) $this->store()->workloadForDay($uid, $date) : 0,
            'dayCapacity' => (int) ($settings['day_capacity'] ?? 480),
        ], 'تقویم', 'calendar');
    }

    public function weekRedirect(Request $request): Response
    {
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        return Response::redirect('/me?date=' . $date . '&view=weekly');
    }

    public function todosRedirect(Request $request): Response
    {
        return Response::redirect('/me');
    }

    // ---------- تسک یکپارچه (JSON) ----------

    public function taskSave(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        // کلیدهای تازه به استور پاس داده می‌شوند؛ taskCreate/taskUpdate فعلی
        // فقط title/date/hour/category/priority/duration/note را می‌خوانند و
        // بقیه را نادیده می‌گیرند (فهرست دقیق در گزارش نهایی).
        $recur = (string) $request->input('recur', 'none');
        $recurUntil = trim((string) $request->input('recur_until', ''));
        $remindAt = trim((string) $request->input('remind_at', ''));
        $startDate = trim((string) $request->input('start_date', ''));
        $recurMode = (string) $request->input('recur_mode', 'fixed');
        $f = [
            'title' => trim((string) $request->input('title', '')),
            'category' => (string) $request->input('category', 'personal'),
            'priority' => (string) $request->input('priority', 'medium'),
            'date' => trim((string) $request->input('date', '')),
            'hour' => trim((string) $request->input('hour', 'all-day')),
            'duration' => (int) $request->input('duration', 30),
            'note' => trim((string) $request->input('note', '')),
            'tags' => $this->parseTags((string) $request->input('tags', '')),
            'recur' => in_array($recur, ['none', 'daily', 'weekly', 'monthly'], true) ? $recur : 'none',
            'recur_until' => Personal::validDate($recurUntil) ? $recurUntil : '',
            'recur_mode' => in_array($recurMode, ['fixed', 'after'], true) ? $recurMode : 'fixed',
            'remind_at' => Personal::validTime($remindAt) ? $remindAt : '',
            'start_date' => Personal::validDate($startDate) ? $startDate : '',
            'project_id' => max(0, (int) $request->input('project_id', 0)),
        ];
        $kind = (string) $request->input('kind', '');
        $id = (int) $request->input('id', 0);
        $task = ($kind !== '' && $id > 0)
            ? $this->store()->taskUpdate((int) $this->user()['id'], $kind, $id, $f)
            : $this->store()->taskCreate((int) $this->user()['id'], $f);
        if ($task === null) {
            return Response::json(['ok' => false, 'error' => 'عنوان و تاریخ معتبر لازم است.'], 400);
        }
        // برچسب‌ها: $f['tags'] فعلاً در استور خوانده نمی‌شود (گزارش نهایی)؛
        // اگر setTodoTags پیاده شده باشد و فرم برچسب فرستاده باشد، همان‌جا ست می‌شود.
        // فرم بدون فیلد tags نباید برچسب‌های قبلی را پاک کند، پس فقط وقتی فیلد آمده است.
        if (($task['kind'] ?? '') === 'todo' && $request->input('tags', null) !== null && $this->storeHas('setTodoTags')) {
            $this->store()->setTodoTags((int) $this->user()['id'], (int) $task['id'], $this->parseTags((string) $request->input('tags', '')));
        }
        return Response::json(['ok' => true, 'task' => $task]);
    }

    public function taskToggleAction(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $done = $this->store()->taskToggle(
            (int) $this->user()['id'],
            (string) ($params['kind'] ?? ''),
            (int) ($params['id'] ?? 0)
        );
        if ($done === null) {
            return Response::json(['ok' => false], 404);
        }
        return Response::json(['ok' => true, 'done' => $done]);
    }

    public function taskDeleteAction(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $this->store()->taskDelete(
            (int) $this->user()['id'],
            (string) ($params['kind'] ?? ''),
            (int) ($params['id'] ?? 0)
        );
        return Response::json(['ok' => true]);
    }

    /** چرت یادآوری: remind_at را به «اکنون + minutes» می‌برد (الحاقیه جمینای). */
    public function taskSnooze(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $minutes = max(1, min(1440, (int) $request->input('minutes', 15)));
        if (!$this->storeHas('taskSnooze')) {
            return $this->back('/me', '', 'چرت یادآوری هنوز فعال نشده است.');
        }
        $this->store()->taskSnooze(
            (int) $this->user()['id'],
            (string) ($params['kind'] ?? ''),
            (int) ($params['id'] ?? 0),
            $minutes
        );
        return $this->back('/me', 'یادآوری ' . $minutes . ' دقیقه به تعویق افتاد.');
    }

    // ---------- تودو ----------

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
        $uid = (int) $this->user()['id'];
        $selected = null;
        $selectedLinks = [];
        $selId = (int) $request->input('selected', 0);
        if ($selId > 0) {
            $selected = $this->store()->note($uid, $selId);
            if ($selected !== null && $this->storeHas('noteLinks')) {
                $selectedLinks = $this->store()->noteLinks($uid, $selId);
            }
        }
        return $this->page2($request, 'notes', [
            'notes' => $this->store()->notes($uid),
            'tags' => $this->storeHas('tags') ? $this->store()->tags($uid) : [],
            'selected' => $selected,
            'note' => $selected,
            'noteLinks' => $selectedLinks,
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
        return $this->page2($request, 'note', [
            'note' => $note,
            'noteLinks' => $this->storeHas('noteLinks')
                ? $this->store()->noteLinks((int) $this->user()['id'], (int) $note['id']) : [],
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
        if ($request->input('tags', null) !== null && $this->storeHas('setNoteTags')) {
            $this->store()->setNoteTags((int) $this->user()['id'], $id, $this->parseTags((string) $request->input('tags', '')));
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
        return $this->page2($request, 'projects', [
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
        return $this->page2($request, 'project', [
            'project' => $project,
            'needs' => $this->store()->needs((int) $project['id']),
            'linkedNotes' => $this->linkedNotesForProject((int) $this->user()['id'], (int) $project['id']),
            'projectTasks' => $this->storeHas('tasksForProject')
                ? $this->store()->tasksForProject((int) $this->user()['id'], (int) $project['id']) : [],
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
        return $this->page2($request, 'shared', [
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
        return $this->page2($request, 'shared_view', [
            'type' => $type,
            'type_label' => Personal::SHAREABLE[$type],
            'item' => $item,
            'needs' => $type === 'project' ? $this->store()->needs($id) : [],
            'readonly' => true,
        ], 'مشاهده اشتراکی', 'shared');
    }

    // ---------- جست‌وجوی سراسری ----------

    public function search(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $q = $this->trunc(trim((string) $request->input('q', '')), 200);
        $results = ($q !== '' && $this->storeHas('search'))
            ? $this->store()->search((int) $this->user()['id'], $q) : [];
        return $this->page2($request, 'search', [
            'results' => $results,
            'q' => $q,
        ], 'جست‌وجو', 'search');
    }

    // ---------- انتقال ناتمام‌ها ----------

    public function rollover(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if (!$this->storeHas('rollover')) {
            return $this->back('/me', '', 'انتقال ناتمام‌ها هنوز فعال نشده است.');
        }
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $today = date('Y-m-d');
        $count = (int) $this->store()->rollover((int) $this->user()['id'], $yesterday, $today);
        return $count > 0
            ? $this->back('/me', $count . ' کار ناتمام به امروز منتقل شد.')
            : $this->back('/me', 'کار ناتمامی برای انتقال نبود.');
    }

    // ---------- نماهای ذخیره‌شده ----------

    public function filterSave(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $uid = (int) $this->user()['id'];
        $one = $this->sanitizeSavedFilters([[
            'name' => (string) $request->input('name', ''),
            'tag' => (string) $request->input('tag', ''),
            'category' => (string) $request->input('category', ''),
            'priority' => (string) $request->input('priority', ''),
        ]]);
        if ($one === []) {
            return $this->back('/me', '', 'نام یا فیلتر را بنویسید.');
        }
        $new = $this->fullSettingsForSave($uid);
        $list = $this->sanitizeSavedFilters($new['saved_filters'] ?? []);
        $list[] = $one[0];
        $new['saved_filters'] = $list;
        $this->store()->settingsSave($uid, $new);
        return $this->back('/me', 'نما ذخیره شد.');
    }

    public function filterDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $uid = (int) $this->user()['id'];
        $new = $this->fullSettingsForSave($uid);
        $list = $this->sanitizeSavedFilters($new['saved_filters'] ?? []);
        $idx = (int) ($params['idx'] ?? -1);
        if (isset($list[$idx])) {
            unset($list[$idx]);
            $new['saved_filters'] = array_values($list);
            $this->store()->settingsSave($uid, $new);
            return $this->back('/me', 'نما حذف شد.');
        }
        return $this->back('/me');
    }

    // ---------- عادت‌ها ----------

    public function habits(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $habits = $this->storeHas('habits') ? $this->store()->habits($uid) : [];
        foreach ($habits as &$h) {
            $hid = (int) ($h['id'] ?? 0);
            $h['streak'] = $this->storeHas('habitStreak') ? (int) $this->store()->habitStreak($uid, $hid) : 0;
            $h['record'] = $this->storeHas('habitRecordStreak') ? (int) $this->store()->habitRecordStreak($uid, $hid) : $h['streak'];
            $h['heatmap'] = $this->storeHas('habitHeatmap') ? $this->store()->habitHeatmap($uid, $hid) : [];
        }
        unset($h);
        return $this->page2($request, 'habits', [
            'habits' => $habits,
            'today' => date('Y-m-d'),
        ], 'عادت‌ها', 'habits');
    }

    public function habitCreate(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/me/habits', '', 'عنوان عادت را بنویسید.');
        }
        if (!$this->storeHas('habitCreate')) {
            return $this->back('/me/habits', '', 'عادت‌ها هنوز فعال نشده‌اند.');
        }
        $target = max(1, min(7, (int) $request->input('target_per_week', 7)));
        $this->store()->habitCreate((int) $this->user()['id'], $title, $target);
        return $this->back('/me/habits', 'عادت اضافه شد.');
    }

    public function habitToggle(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        if (!$this->storeHas('habitToggle')) {
            return $this->back('/me/habits', '', 'عادت‌ها هنوز فعال نشده‌اند.');
        }
        $this->store()->habitToggle((int) $this->user()['id'], (int) ($params['id'] ?? 0), $date);
        return $this->back('/me/habits');
    }

    public function habitDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if (!$this->storeHas('habitDelete')) {
            return $this->back('/me/habits', '', 'عادت‌ها هنوز فعال نشده‌اند.');
        }
        $this->store()->habitDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/habits', 'عادت حذف شد.');
    }

    // ---------- مرور هفتگی ----------

    public function review(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $weekInput = trim((string) $request->input('week', ''));
        $weekSat = Personal::validDate($weekInput)
            ? Personal::weekSaturday($weekInput) : Personal::weekSaturday(date('Y-m-d'));
        return $this->page2($request, 'review', [
            'stats' => $this->storeHas('reviewStats')
                ? $this->store()->reviewStats((int) $this->user()['id'], $weekSat) : [],
            'weekSat' => $weekSat,
            'weekSun' => date('Y-m-d', strtotime($weekSat . ' +6 days')),
            'today' => date('Y-m-d'),
        ], 'مرور هفتگی', 'review');
    }

    // ---------- آمار ----------

    public function stats(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $today = date('Y-m-d');
        $weekSat = Personal::weekSaturday($today);
        // قرارداد ویوی stats: کلیدهای focus_7/focus_30/done_7/done_30/done_total/
        // by_category/habit_streaks؛ فقط کلیدهای پاس‌شده رندر می‌شوند.
        $from30 = date('Y-m-d', strtotime($today . ' -29 days'));
        $from7 = date('Y-m-d', strtotime($today . ' -6 days'));
        $tasks30 = $this->store()->tasksForRange($uid, $from30, $today);
        $tasks7 = $this->store()->tasksForRange($uid, $from7, $today);
        $done30 = array_values(array_filter($tasks30, static fn ($t) => (int) ($t['done'] ?? 0) === 1));
        $done7 = array_values(array_filter($tasks7, static fn ($t) => (int) ($t['done'] ?? 0) === 1));
        $byCategory = [];
        foreach ($done30 as $t) {
            $c = (string) ($t['category'] ?? 'personal');
            $byCategory[$c] = ($byCategory[$c] ?? 0) + 1;
        }
        $allTasks = $this->store()->tasksForRange($uid, '2000-01-01', '2099-12-31');
        $doneTotal = count(array_filter($allTasks, static fn ($t) => (int) ($t['done'] ?? 0) === 1));
        $habitStreaks = [];
        foreach ($this->storeHas('habits') ? $this->store()->habits($uid) : [] as $h) {
            $habitStreaks[] = [
                'title' => (string) ($h['title'] ?? ''),
                'streak' => $this->storeHas('habitStreak')
                    ? (int) $this->store()->habitStreak($uid, (int) ($h['id'] ?? 0)) : 0,
            ];
        }
        return $this->page2($request, 'stats', [
            'stats' => [
                'focus_7' => $this->storeHas('pomoMinutesForRange') ? (int) $this->store()->pomoMinutesForRange($uid, $from7, $today) : 0,
                'focus_30' => $this->storeHas('pomoMinutesForRange') ? (int) $this->store()->pomoMinutesForRange($uid, $from30, $today) : 0,
                'done_7' => count($done7),
                'done_30' => count($done30),
                'done_total' => $doneTotal,
                'by_category' => $byCategory,
                'habit_streaks' => $habitStreaks,
            ],
            'weekSat' => $weekSat,
            'today' => $today,
        ], 'آمار', 'stats');
    }

    // ---------- بورد و ماتریس ----------

    public function board(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $monthFirst = date('Y-m-01');
        $monthLast = date('Y-m-t');
        return $this->page2($request, 'board', [
            'tasks' => $this->store()->tasksForRange($uid, $monthFirst, $monthLast),
            'categories' => $this->store()->settings($uid)['categories'],
            'monthFirst' => $monthFirst,
            'monthLast' => $monthLast,
            'today' => date('Y-m-d'),
        ], 'بورد', 'board');
    }

    public function matrix(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $monthFirst = date('Y-m-01');
        $monthLast = date('Y-m-t');
        return $this->page2($request, 'matrix', [
            'tasks' => $this->store()->tasksForRange($uid, $monthFirst, $monthLast),
            'today' => date('Y-m-d'),
        ], 'ماتریس آیزنهاور', 'matrix');
    }

    // ---------- اهداف + مایل‌استون‌ها ----------

    public function goals(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $goals = $this->storeHas('goals') ? $this->store()->goals($uid) : [];
        // قرارداد ویوی goals: $goalProgress نگاشت id=>درصد است.
        $goalProgress = [];
        if ($this->storeHas('goalProgress')) {
            foreach ($goals as &$g) {
                $pct = (int) $this->store()->goalProgress($uid, (int) ($g['id'] ?? 0));
                $g['progress'] = $pct;
                $goalProgress[(int) ($g['id'] ?? 0)] = $pct;
            }
            unset($g);
        }
        return $this->page2($request, 'goals', [
            'goals' => $goals,
            'goalProgress' => $goalProgress,
            'today' => date('Y-m-d'),
        ], 'اهداف', 'goals');
    }

    public function goalCreate(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/me/goals', '', 'عنوان هدف را بنویسید.');
        }
        if (!$this->storeHas('goalCreate')) {
            return $this->back('/me/goals', '', 'اهداف هنوز فعال نشده‌اند.');
        }
        $target = trim((string) $request->input('target_date', ''));
        $id = $this->store()->goalCreate(
            (int) $this->user()['id'], $title, Personal::validDate($target) ? $target : ''
        );
        if (is_int($id) && $id > 0) {
            return $this->back('/me/goals/' . $id, 'هدف ساخته شد.');
        }
        return $this->back('/me/goals', '', 'ساخت هدف ممکن نشد.');
    }

    private function ownedGoal(int $uid, int $goalId): ?array
    {
        if ($this->storeHas('goal')) {
            return $this->store()->goal($uid, $goalId);
        }
        if ($this->storeHas('goals')) {
            foreach ($this->store()->goals($uid) as $g) {
                if ((int) ($g['id'] ?? 0) === $goalId) {
                    return $g;
                }
            }
        }
        return null;
    }

    public function goalShow(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $goalId = (int) ($params['id'] ?? 0);
        $goal = $this->ownedGoal($uid, $goalId);
        if ($goal === null) {
            return Response::notFound('هدف پیدا نشد');
        }
        return $this->page2($request, 'goal', [
            'goal' => $goal,
            'milestones' => $this->storeHas('milestones') ? $this->store()->milestones($goalId) : [],
            'goalTasks' => $this->storeHas('goalTasks') ? $this->store()->goalTasks($uid, $goalId) : [],
            'goalProgress' => $this->storeHas('goalProgress') ? (int) $this->store()->goalProgress($uid, $goalId) : 0,
            'today' => date('Y-m-d'),
        ], 'هدف', 'goals');
    }

    public function goalDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if (!$this->storeHas('goalDelete')) {
            return $this->back('/me/goals', '', 'اهداف هنوز فعال نشده‌اند.');
        }
        $this->store()->goalDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back('/me/goals', 'هدف حذف شد.');
    }

    public function milestoneAdd(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $goalId = (int) ($params['id'] ?? 0);
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/me/goals/' . $goalId, '', 'عنوان مایل‌استون را بنویسید.');
        }
        if ($this->storeHas('goal') || $this->storeHas('goals')) {
            if ($this->ownedGoal((int) $this->user()['id'], $goalId) === null) {
                return Response::notFound('هدف پیدا نشد');
            }
        }
        if (!$this->storeHas('milestoneAdd')) {
            return $this->back('/me/goals/' . $goalId, '', 'اهداف هنوز فعال نشده‌اند.');
        }
        $this->store()->milestoneAdd((int) $this->user()['id'], $goalId, $title);
        return $this->back('/me/goals/' . $goalId, 'مایل‌استون اضافه شد.');
    }

    public function milestoneToggle(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $goalId = (int) $request->input('goal_id', 0);
        if (!$this->storeHas('milestoneToggle')) {
            return $this->back($goalId > 0 ? '/me/goals/' . $goalId : '/me/goals', '', 'اهداف هنوز فعال نشده‌اند.');
        }
        $this->store()->milestoneToggle((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back($goalId > 0 ? '/me/goals/' . $goalId : '/me/goals');
    }

    public function milestoneDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $goalId = (int) $request->input('goal_id', 0);
        if (!$this->storeHas('milestoneDelete')) {
            return $this->back($goalId > 0 ? '/me/goals/' . $goalId : '/me/goals', '', 'اهداف هنوز فعال نشده‌اند.');
        }
        $this->store()->milestoneDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back($goalId > 0 ? '/me/goals/' . $goalId : '/me/goals', 'مایل‌استون حذف شد.');
    }

    // ---------- لینک تسک به هدف (الحاقیه جمینای) ----------

    public function goalLinkTask(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $goalId = (int) ($params['id'] ?? 0);
        $todoId = (int) $request->input('todo_id', 0);
        if ($todoId <= 0) {
            return $this->back('/me/goals/' . $goalId, '', 'تسک را انتخاب کنید.');
        }
        if (($this->storeHas('goal') || $this->storeHas('goals'))
            && $this->ownedGoal((int) $this->user()['id'], $goalId) === null) {
            return Response::notFound('هدف پیدا نشد');
        }
        if (!$this->storeHas('goalLinkTask')) {
            return $this->back('/me/goals/' . $goalId, '', 'لینک تسک به هدف هنوز فعال نشده است.');
        }
        if (!$this->store()->goalLinkTask((int) $this->user()['id'], $goalId, $todoId)) {
            return $this->back('/me/goals/' . $goalId, '', 'تسک یا هدف پیدا نشد.');
        }
        return $this->back('/me/goals/' . $goalId, 'تسک به هدف لینک شد.');
    }

    public function goalUnlinkTask(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        // امضای واقعی استور: goalUnlinkTask(userId, goalId, todoId) — سطر لینک
        // شناسهٔ جداگانه ندارد. فرم باید goal_id و todo_id بفرستد؛ اگر فقط
        // todo_id آمده باشد، {id} مسیر همان goal_id فرض می‌شود.
        $todoId = (int) $request->input('todo_id', 0);
        $goalId = (int) $request->input('goal_id', 0);
        if ($goalId <= 0 && $todoId > 0) {
            $goalId = (int) ($params['id'] ?? 0);
        }
        if ($goalId <= 0 || $todoId <= 0) {
            return $this->back('/me/goals', '', 'هدف یا تسک مشخص نیست.');
        }
        if (!$this->storeHas('goalUnlinkTask')) {
            return $this->back('/me/goals/' . $goalId, '', 'لینک تسک به هدف هنوز فعال نشده است.');
        }
        $this->store()->goalUnlinkTask((int) $this->user()['id'], $goalId, $todoId);
        return $this->back('/me/goals/' . $goalId, 'لینک تسک برداشته شد.');
    }

    // ---------- ژورنال ----------

    public function journal(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        return $this->page2($request, 'journal', [
            'entry' => $this->storeHas('journal') ? $this->store()->journal((int) $this->user()['id'], $date) : null,
            'date' => $date,
            'today' => date('Y-m-d'),
        ], 'ژورنال', 'journal');
    }

    public function journalSave(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $date = (string) $request->input('date', date('Y-m-d'));
        if (!Personal::validDate($date)) {
            $date = date('Y-m-d');
        }
        $mood = (string) $request->input('mood', 'ok');
        if (!in_array($mood, ['great', 'good', 'ok', 'bad', 'awful'], true)) {
            $mood = 'ok';
        }
        if (!$this->storeHas('journalSave')) {
            return $this->back('/me/journal?date=' . $date, '', 'ژورنال هنوز فعال نشده است.');
        }
        $this->store()->journalSave((int) $this->user()['id'], $date, (string) $request->input('body', ''), $mood);
        return $this->back('/me/journal?date=' . $date, 'ژورنال ذخیره شد.');
    }

    // ---------- خروجی ----------

    public function export(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        $uid = (int) $this->user()['id'];
        $format = (string) $request->input('format', 'json');
        if (!in_array($format, ['json', 'csv', 'md'], true)) {
            return $this->back('/me', '', 'فرمت خروجی نامعتبر است.');
        }
        if ($format === 'csv') {
            $tasks = $this->store()->tasksForRange($uid, '2000-01-01', '2099-12-31');
            $fp = fopen('php://temp', 'r+');
            fputcsv($fp, ['kind', 'title', 'date', 'category', 'priority', 'duration', 'done']);
            foreach ($tasks as $t) {
                fputcsv($fp, [
                    (string) ($t['kind'] ?? ''), (string) ($t['title'] ?? ''),
                    (string) ($t['date'] ?? ''), (string) ($t['category'] ?? ''),
                    (string) ($t['priority'] ?? ''), (int) ($t['duration'] ?? 0),
                    (int) ($t['done'] ?? 0),
                ]);
            }
            rewind($fp);
            $body = (string) stream_get_contents($fp);
            fclose($fp);
            return new Response($body, 200, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="vasleh-me-export.csv"',
            ]);
        }
        // JSON کامل از exportData؛ اگر هنوز نیست، از متدهای موجود سرهم می‌شود
        // (بدون ژورنال/عادت کامل — گزارش نهایی) و هرگز عدد جعل نمی‌شود.
        $data = $this->storeHas('exportData')
            ? $this->store()->exportData($uid)
            : [
                'tasks' => $this->store()->tasksForRange($uid, '2000-01-01', '2099-12-31'),
                'notes' => $this->store()->notes($uid),
                'projects' => $this->store()->projects($uid),
                'habits' => $this->storeHas('habits') ? $this->store()->habits($uid) : [],
            ];
        if ($format === 'md') {
            return new Response($this->exportMarkdown($data), 200, [
                'Content-Type' => 'text/markdown; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="vasleh-me-export.md"',
            ]);
        }
        return new Response(
            (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            200,
            [
                'Content-Type' => 'application/json; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="vasleh-me-export.json"',
            ]
        );
    }

    /** Markdown خروجی: نوت‌ها و تسک‌ها به‌صورت تیتر + چک‌لیست (شکل exportData تحمل‌پذیر است). */
    private function exportMarkdown(mixed $data): string
    {
        $lines = ['# خروجی مدیریت شخصی', ''];
        $notes = is_array($data) ? ($data['notes'] ?? []) : [];
        if (is_array($notes) && $notes !== []) {
            $lines[] = '## یادداشت‌ها';
            $lines[] = '';
            foreach ($notes as $n) {
                if (!is_array($n)) {
                    continue;
                }
                $lines[] = '### ' . (string) ($n['title'] ?? '');
                $lines[] = '';
                $body = trim((string) ($n['body'] ?? ''));
                if ($body !== '') {
                    $lines[] = $body;
                    $lines[] = '';
                }
            }
        }
        $tasks = [];
        if (is_array($data)) {
            foreach (['tasks', 'todos', 'events'] as $key) {
                if (isset($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $t) {
                        if (is_array($t)) {
                            $tasks[] = $t;
                        }
                    }
                }
            }
        }
        if ($tasks !== []) {
            $lines[] = '## کارها';
            $lines[] = '';
            foreach ($tasks as $t) {
                $mark = (int) ($t['done'] ?? 0) === 1 ? 'x' : ' ';
                $date = (string) ($t['date'] ?? $t['due_date'] ?? '');
                $lines[] = '- [' . $mark . '] ' . (string) ($t['title'] ?? '')
                    . ($date !== '' ? ' (' . $date . ')' : '');
            }
            $lines[] = '';
        }
        return implode("\n", $lines);
    }

    // ---------- لاگ پومودورو (fetch) ----------

    public function pomoLog(Request $request): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $minutes = max(1, min(480, (int) $request->input('minutes', 0)));
        $taskId = (int) $request->input('task_id', 0);
        if (!$this->storeHas('pomoLog')) {
            return Response::json(['ok' => false, 'error' => 'لاگ پومودورو هنوز فعال نشده است.'], 501);
        }
        $this->store()->pomoLog((int) $this->user()['id'], $minutes, $taskId > 0 ? $taskId : null);
        return Response::json(['ok' => true]);
    }

    // ---------- زیرکارها ----------

    public function subtaskAdd(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $todoId = (int) ($params['id'] ?? 0);
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->backFrom($request, '/me', '', 'عنوان زیرکار را بنویسید.');
        }
        if (!$this->storeHas('subtaskAdd')) {
            return $this->backFrom($request, '/me', '', 'زیرکارها هنوز فعال نشده‌اند.');
        }
        if ($this->store()->subtaskAdd((int) $this->user()['id'], $todoId, $title) === null) {
            return $this->backFrom($request, '/me', '', 'کار پیدا نشد.');
        }
        return $this->backFrom($request, '/me', 'زیرکار اضافه شد.');
    }

    public function subtaskToggle(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if (!$this->storeHas('subtaskToggle')) {
            return $this->backFrom($request, '/me', '', 'زیرکارها هنوز فعال نشده‌اند.');
        }
        $this->store()->subtaskToggle((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->backFrom($request, '/me');
    }

    public function subtaskDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if (!$this->storeHas('subtaskDelete')) {
            return $this->backFrom($request, '/me', '', 'زیرکارها هنوز فعال نشده‌اند.');
        }
        $this->store()->subtaskDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->backFrom($request, '/me', 'زیرکار حذف شد.');
    }

    // ---------- لینک نوت ----------

    public function noteLinkAdd(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $noteId = (int) ($params['id'] ?? 0);
        if ($this->store()->note((int) $this->user()['id'], $noteId) === null) {
            return Response::notFound('یادداشت پیدا نشد');
        }
        $type = (string) $request->input('item_type', '');
        $itemId = (int) $request->input('item_id', 0);
        // الحاقیه: رویداد هم لینک‌پذیر است (todo/event/project).
        if (!in_array($type, ['todo', 'event', 'project'], true) || $itemId <= 0) {
            return $this->back('/me/notes/' . $noteId, '', 'نوع آیتم یا شناسه نامعتبر است.');
        }
        if (!$this->storeHas('noteLinkAdd')) {
            return $this->back('/me/notes/' . $noteId, '', 'لینک نوت هنوز فعال نشده است.');
        }
        if (!$this->store()->noteLinkAdd((int) $this->user()['id'], $noteId, $type, $itemId)) {
            return $this->back('/me/notes/' . $noteId, '', 'آیتم لینک‌شونده پیدا نشد.');
        }
        return $this->back('/me/notes/' . $noteId, 'لینک اضافه شد.');
    }

    public function noteLinkDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $noteId = (int) $request->input('note_id', 0);
        $back = $noteId > 0 ? '/me/notes/' . $noteId : '/me/notes';
        if (!$this->storeHas('noteLinkDelete')) {
            return $this->back($back, '', 'لینک نوت هنوز فعال نشده است.');
        }
        $this->store()->noteLinkDelete((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        return $this->back($back, 'لینک برداشته شد.');
    }

    // ---------- کپی پروژه ----------

    public function projectDuplicate(Request $request, array $params): Response
    {
        if (($r = $this->requireUser()) !== null) {
            return $r;
        }
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if (!$this->storeHas('duplicateProject')) {
            return $this->back('/me/projects', '', 'کپی پروژه هنوز فعال نشده است.');
        }
        $newId = $this->store()->duplicateProject((int) $this->user()['id'], (int) ($params['id'] ?? 0));
        if (is_int($newId) && $newId > 0) {
            return Response::redirect('/me/projects/' . $newId);
        }
        return $this->back('/me/projects', '', 'کپی پروژه ممکن نشد.');
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
        return Response::html(View::render('personal', 'me', 'taskyar_layout', [
            'title' => (string) $item['title'],
            'content' => $inner,
            'user' => null,
            'tab' => '',
            'flash' => null,
            'error' => null,
            'public' => true,
        ]));
    }
}
