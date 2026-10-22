<?php
declare(strict_types=1);

namespace Modules\Backup;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل پشتیبان‌گیری — همه عملیات فقط برای مالک (بکاپ = کلیدهای پادشاهی). */
final class AdminBackupController
{
    private const MAX_UPLOAD = 104857600; // ۱۰۰ مگابایت

    public function __construct(private readonly Container $container) {}

    private function manager(): BackupManager
    {
        return $this->container->get('backup.manager');
    }

    private function ownerOnly(): ?Response
    {
        $user = $this->container->has('session') ? $this->container->get('session')->user() : null;
        if (($user['role'] ?? '') !== 'owner') {
            return Response::forbidden('پشتیبان‌گیری فقط برای مالک است');
        }
        return null;
    }

    private function log(string $action, string $target): void
    {
        if (!$this->container->has('logger') || !$this->container->has('session')) {
            return;
        }
        $actor = (string) ($this->container->get('session')->user()['username'] ?? '');
        if ($actor !== '') {
            $this->container->get('logger')->user($actor, $action, $target);
        }
    }

    public function index(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $session = $this->container->get('session');
        $backups = $this->manager()->all();
        $totalSize = array_sum(array_map(static fn (array $b): int => (int) $b['size'], $backups));
        $content = View::render('backup', 'admin', 'index', [
            'backups'  => $backups,
            'totalSize' => $totalSize,
            'periodic' => $this->manager()->periodicSettings(),
            'lastRun'  => $this->manager()->lastRun(),
            'flash'    => $request->input('msg'),
            'error'    => $request->input('err'),
            'csrf'     => $session->csrfToken(),
        ]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => 'پشتیبان‌گیری',
            'content' => $content,
            'menu'    => $this->container->get('loader')->adminMenu(),
            'user'    => $session->user(),
            'csrf'    => $session->csrfToken(),
        ]));
    }

    public function create(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $kind = (string) $request->input('kind', 'full');
        try {
            if ($kind === 'database') {
                $made = $this->manager()->create('database', false, true, false);
            } elseif ($kind === 'custom') {
                $files = !empty($request->input('part_files'));
                $db = !empty($request->input('part_database'));
                $storage = !empty($request->input('part_storage'));
                if (!$files && !$db && !$storage) {
                    return Response::redirect('/admin/backup?err=' . urlencode('برای بکاپ انتخابی دست‌کم یک بخش را انتخاب کنید'));
                }
                $made = $this->manager()->create('custom', $files, $db, $storage);
            } else {
                $made = $this->manager()->create('full', true, true, true);
            }
        } catch (\Throwable $e) {
            return Response::redirect('/admin/backup?err=' . urlencode('ساخت بکاپ نشد: ' . $e->getMessage()));
        }
        $this->log('ساخت بکاپ', $made['name']);
        return Response::redirect('/admin/backup?msg=' . urlencode('بکاپ ساخته شد: ' . $made['name']));
    }

    public function download(Request $request, array $params): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $path = $this->manager()->path((string) ($params['name'] ?? ''));
        if ($path === null) {
            return Response::notFound('بکاپ پیدا نشد');
        }
        $body = (string) file_get_contents($path);
        return new Response($body, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . basename($path) . '"',
            'Content-Length'      => (string) strlen($body),
        ]);
    }

    public function delete(Request $request, array $params): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $name = (string) ($params['name'] ?? '');
        if ($this->manager()->delete($name)) {
            $this->log('حذف بکاپ', $name);
        }
        return Response::redirect('/admin/backup?msg=' . urlencode('بکاپ حذف شد'));
    }

    public function restoreExisting(Request $request, array $params): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        if (empty($request->input('confirm'))) {
            return Response::redirect('/admin/backup?err=' . urlencode('برای بازگردانی، تیک تأیید را بزنید'));
        }
        $path = $this->manager()->path((string) ($params['name'] ?? ''));
        if ($path === null) {
            return Response::notFound('بکاپ پیدا نشد');
        }
        return $this->doRestore($path);
    }

    public function restoreUpload(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        if (empty($request->input('confirm'))) {
            return Response::redirect('/admin/backup?err=' . urlencode('برای بازگردانی، تیک تأیید را بزنید'));
        }
        $file = $_FILES['backup_zip'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return Response::redirect('/admin/backup?err=' . urlencode('فایل بکاپ را انتخاب کنید'));
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_UPLOAD) {
            return Response::redirect('/admin/backup?err=' . urlencode('فایل بکاپ بزرگ‌تر از ۱۰۰ مگابایت است'));
        }
        $tmp = (string) $this->container->get('base_path') . '/storage/tmp-restore-upload-' . bin2hex(random_bytes(4)) . '.zip';
        if (!move_uploaded_file((string) ($file['tmp_name'] ?? ''), $tmp)) {
            return Response::redirect('/admin/backup?err=' . urlencode('آپلود فایل نشد'));
        }
        try {
            return $this->doRestore($tmp);
        } finally {
            if (is_file($tmp)) {
                unlink($tmp);
            }
        }
    }

    private function doRestore(string $path): Response
    {
        try {
            $result = $this->manager()->restore($path);
        } catch (\Throwable $e) {
            return Response::redirect('/admin/backup?err=' . urlencode('بازگردانی نشد: ' . $e->getMessage()));
        }
        if (!$result['ok']) {
            return Response::redirect('/admin/backup?err=' . urlencode((string) $result['error']));
        }
        $this->log('بازگردانی بکاپ', basename($path) . ' (ایمنی: ' . (string) $result['safety'] . ')');
        return Response::redirect('/admin/backup?msg=' . urlencode(
            'بازگردانی انجام شد. قبل از آن از وضعیت قبلی بکاپ ایمنی گرفته شد: ' . (string) $result['safety']
            . ' — اگر دیتابیس برگشته باشد، شاید لازم شود دوباره وارد شوید.'
        ));
    }

    public function settingsSave(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $freq = (string) $request->input('freq', 'daily');
        if (!in_array($freq, ['daily', 'weekly', 'monthly'], true)) {
            $freq = 'daily';
        }
        $type = (string) $request->input('type', 'full');
        if (!in_array($type, ['full', 'database'], true)) {
            $type = 'full';
        }
        $this->manager()->savePeriodicSettings([
            'enabled' => !empty($request->input('enabled')),
            'freq'    => $freq,
            'type'    => $type,
            'keep'    => max(1, min(50, (int) $request->input('keep', 5))),
        ]);
        $this->log('تنظیمات بکاپ دوره‌ای', $freq . '/' . $type);
        return Response::redirect('/admin/backup?msg=' . urlencode('تنظیمات بکاپ دوره‌ای ذخیره شد'));
    }
}
