<?php
declare(strict_types=1);

namespace Modules\Backup;

/**
 * موتور پشتیبان‌گیری وصله:
 * هر بکاپ یک ZIP با manifest.json است — files/ برای کد برنامه و storage/
 * برای داده‌ها (دیتابیس با VACUUM INTO سازگار گرفته می‌شود).
 * بکاپ‌ها در storage/backups می‌نشینند و هیچ‌وقت داخل خود بکاپ نمی‌آیند.
 */
final class BackupManager
{
    public function __construct(private readonly string $base) {}

    public function dir(): string
    {
        $dir = $this->base . '/storage/backups';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        return $dir;
    }

    // ---------- ساخت ----------

    /**
     * @return array{name: string, size: int, type: string}
     */
    public function create(string $type, bool $withFiles, bool $withDatabase, bool $withStorage, bool $auto = false): array
    {
        $name = ($auto ? 'auto-' : '') . 'vasleh-' . $type . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(2)) . '.zip';
        $tmpDir = $this->base . '/storage/tmp-backup-' . bin2hex(random_bytes(4));
        mkdir($tmpDir, 0775, true);
        $zipPath = $tmpDir . '/backup.zip';
        $fileCount = 0;
        try {
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('ساخت فایل ZIP ممکن نشد');
            }
            if ($withFiles) {
                foreach ($this->collect($this->base, ['storage']) as $rel) {
                    $zip->addFile($this->base . '/' . $rel, 'files/' . $rel);
                    $fileCount++;
                }
            }
            if ($withDatabase) {
                $dbCopy = $tmpDir . '/database.sqlite';
                $this->dumpDatabase($dbCopy);
                $zip->addFile($dbCopy, 'storage/database.sqlite');
            }
            if ($withStorage) {
                foreach ($this->collect($this->base . '/storage', ['backups']) as $rel) {
                    if (str_starts_with($rel, 'tmp-backup-') || str_starts_with($rel, 'tmp-install-')) {
                        continue;
                    }
                    // دیتابیس فقط از بخش دیتابیس (اسنپ‌شات سازگار VACUUM) می‌آید؛
                    // کپی خام فایل زنده و فایل‌های گذرای wal/shm/journal هم ناسازگارند
                    // و بعد از بازگردانی دیتابیس را malformed می‌کنند — هیچ‌وقت وارد بکاپ نمی‌شوند.
                    if (in_array($rel, ['database.sqlite', 'database.sqlite-wal', 'database.sqlite-shm', 'database.sqlite-journal'], true)) {
                        continue;
                    }
                    $zip->addFile($this->base . '/storage/' . $rel, 'storage/' . $rel);
                    $fileCount++;
                }
            }
            $manifest = [
                'app'        => 'vasleh',
                'version'    => $this->appVersion(),
                'type'       => $type,
                'auto'       => $auto,
                'created_at' => date('Y-m-d H:i:s'),
                'includes'   => ['files' => $withFiles, 'database' => $withDatabase, 'storage' => $withStorage],
                'files'      => $fileCount,
            ];
            $zip->addFromString('manifest.json', (string) json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $zip->close();
            rename($zipPath, $this->dir() . '/' . $name);
        } finally {
            $this->removeDir($tmpDir);
        }
        return ['name' => $name, 'size' => (int) filesize($this->dir() . '/' . $name), 'type' => $type];
    }

    /** کپی سازگار از دیتابیس زنده با VACUUM INTO؛ اگر نشد، کپی مستقیم فایل. */
    private function dumpDatabase(string $dest): void
    {
        $src = $this->base . '/storage/database.sqlite';
        if (!is_file($src)) {
            throw new \RuntimeException('فایل دیتابیس پیدا نشد');
        }
        try {
            $pdo = new \PDO('sqlite:' . $src);
            $pdo->exec('VACUUM INTO ' . $pdo->quote($dest));
        } catch (\Throwable) {
            copy($src, $dest);
        }
    }

    /** فهرست بازگشتی فایل‌های یک ریشه، با حذف پوشه‌های سطح اولِ نام‌برده. */
    private function collect(string $root, array $excludeTop): array
    {
        $out = [];
        if (!is_dir($root)) {
            return $out;
        }
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if (!$f->isFile()) {
                continue;
            }
            $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($root) + 1));
            $top = explode('/', $rel)[0];
            if (in_array($top, $excludeTop, true)) {
                continue;
            }
            $out[] = $rel;
        }
        sort($out);
        return $out;
    }

    // ---------- فهرست و فایل ----------

    /** @return array<int, array<string, mixed>> تازه‌ترین اول */
    public function all(): array
    {
        $out = [];
        foreach (glob($this->dir() . '/*.zip') ?: [] as $file) {
            $name = basename($file);
            $info = [
                'name'    => $name,
                'size'    => (int) filesize($file),
                'time'    => date('Y-m-d H:i:s', (int) filemtime($file)),
                'type'    => '?',
                'auto'    => str_starts_with($name, 'auto-'),
                'created' => '',
                'parts'   => [],
            ];
            $manifest = $this->readManifest($file);
            if ($manifest !== null) {
                $info['type'] = (string) ($manifest['type'] ?? '?');
                $info['created'] = (string) ($manifest['created_at'] ?? '');
                $info['parts'] = (array) ($manifest['includes'] ?? []);
            }
            $out[] = $info;
        }
        usort($out, static fn (array $a, array $b): int => strcmp($b['name'], $a['name']));
        return $out;
    }

    public function path(string $name): ?string
    {
        $name = basename($name);
        if (!str_ends_with($name, '.zip')) {
            return null;
        }
        $path = $this->dir() . '/' . $name;
        return is_file($path) ? $path : null;
    }

    public function delete(string $name): bool
    {
        $path = $this->path($name);
        return $path !== null && unlink($path);
    }

    public function readManifest(string $zipFile): ?array
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            return null;
        }
        $raw = $zip->getFromName('manifest.json');
        $zip->close();
        if ($raw === false) {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    // ---------- بازگردانی ----------

    /**
     * بازگردانی یک بکاپ وصله. قبل از اعمال، از وضعیت فعلی بکاپ ایمنی کامل گرفته می‌شود.
     * @return array{ok: bool, error: ?string, safety: ?string}
     */
    public function restore(string $zipFile): array
    {
        $manifest = $this->readManifest($zipFile);
        if ($manifest === null || ($manifest['app'] ?? '') !== 'vasleh') {
            return ['ok' => false, 'error' => 'این فایل، بکاپ معتبر وصله نیست (manifest ندارد)', 'safety' => null];
        }
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            return ['ok' => false, 'error' => 'بازکردن فایل ZIP ممکن نشد', 'safety' => null];
        }
        // اعتبارسنجی همه مسیرها قبل از هر تغییری: بدون تراورس و فقط زیر files/ و storage/
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = (string) $zip->getNameIndex($i);
            if ($entry === 'manifest.json') {
                continue;
            }
            if (!str_starts_with($entry, 'files/') && !str_starts_with($entry, 'storage/')) {
                $zip->close();
                return ['ok' => false, 'error' => 'مسیر نامعتبر در بکاپ: ' . $entry, 'safety' => null];
            }
            if (str_starts_with($entry, 'storage/backups/')) {
                $zip->close();
                return ['ok' => false, 'error' => 'بکاپ نباید پوشه بکاپ‌ها را بازنویسی کند', 'safety' => null];
            }
            foreach (explode('/', $entry) as $seg) {
                if ($seg === '..') {
                    $zip->close();
                    return ['ok' => false, 'error' => 'مسیر خطرناک در بکاپ: ' . $entry, 'safety' => null];
                }
            }
        }

        $safety = $this->create('pre-restore', true, true, true);

        $tmpDir = $this->base . '/storage/tmp-restore-' . bin2hex(random_bytes(4));
        mkdir($tmpDir, 0775, true);
        try {
            $zip->extractTo($tmpDir);
            $zip->close();
            $includes = (array) ($manifest['includes'] ?? []);
            if (!empty($includes['files']) && is_dir($tmpDir . '/files')) {
                $this->copyTree($tmpDir . '/files', $this->base, ['storage']);
            }
            if (is_dir($tmpDir . '/storage')) {
                $this->copyTree($tmpDir . '/storage', $this->base . '/storage', ['backups'], true);
            }
        } finally {
            $this->removeDir($tmpDir);
        }
        return ['ok' => true, 'error' => null, 'safety' => $safety['name']];
    }

    /** کپی بازگشتی با rename اتمیک برای دیتابیس تا فایل نیمه‌کاره ننشیند. */
    private function copyTree(string $srcRoot, string $destRoot, array $excludeTop, bool $atomicDb = false): void
    {
        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($srcRoot, \FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            if (!$f->isFile()) {
                continue;
            }
            $rel = str_replace('\\', '/', substr($f->getPathname(), strlen($srcRoot) + 1));
            if (in_array(explode('/', $rel)[0], $excludeTop, true)) {
                continue;
            }
            if (str_starts_with($rel, 'tmp-backup-') || str_starts_with($rel, 'tmp-restore-') || str_starts_with($rel, 'tmp-install-')) {
                continue;
            }
            // فایل‌های گذرای دیتابیس هرگز بازگردانی نمی‌شوند (جفتِ دیتابیس دیگری‌اند).
            if (in_array($rel, ['database.sqlite-wal', 'database.sqlite-shm', 'database.sqlite-journal'], true)) {
                continue;
            }
            $dest = $destRoot . '/' . $rel;
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0775, true);
            }
            if ($atomicDb && $rel === 'database.sqlite') {
                // اول همراه‌های کهنه مقصد پاک می‌شوند تا wal قدیمی کنار دیتابیس
                // بازگردانی‌شده ننشیند و فایل را malformed نکند؛ بعد rename اتمیک.
                foreach (['-wal', '-shm', '-journal'] as $suffix) {
                    if (is_file($dest . $suffix)) {
                        unlink($dest . $suffix);
                    }
                }
                $tmp = $dest . '.restore-' . bin2hex(random_bytes(3));
                copy($f->getPathname(), $tmp);
                rename($tmp, $dest);
            } else {
                copy($f->getPathname(), $dest);
            }
        }
    }

    // ---------- بکاپ دوره‌ای ----------

    public function periodicSettings(): array
    {
        $defaults = ['enabled' => false, 'freq' => 'daily', 'type' => 'full', 'keep' => 5];
        $file = $this->base . '/storage/backup-settings.json';
        if (is_file($file)) {
            $data = json_decode((string) file_get_contents($file), true);
            if (is_array($data)) {
                return array_merge($defaults, $data);
            }
        }
        return $defaults;
    }

    public function savePeriodicSettings(array $settings): void
    {
        file_put_contents(
            $this->base . '/storage/backup-settings.json',
            (string) json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    public function lastRun(): int
    {
        $file = $this->base . '/storage/backup-state.json';
        if (is_file($file)) {
            $data = json_decode((string) file_get_contents($file), true);
            return (int) ($data['last_run'] ?? 0);
        }
        return 0;
    }

    /** اگر موعد بکاپ دوره‌ای رسیده باشد، می‌سازد و قدیمی‌های اضافه را هرس می‌کند. */
    public function maybeRunPeriodic(): void
    {
        $settings = $this->periodicSettings();
        if (empty($settings['enabled'])) {
            return;
        }
        $interval = match ((string) $settings['freq']) {
            'weekly'  => 7 * 86400,
            'monthly' => 30 * 86400,
            default   => 86400,
        };
        if (time() - $this->lastRun() < $interval) {
            return;
        }
        $lock = fopen($this->base . '/storage/backup.lock', 'c');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            return;
        }
        try {
            $isDb = ($settings['type'] ?? 'full') === 'database';
            $this->create($isDb ? 'database' : 'full', !$isDb, true, !$isDb, true);
            file_put_contents(
                $this->base . '/storage/backup-state.json',
                (string) json_encode(['last_run' => time()])
            );
            $keep = max(1, (int) ($settings['keep'] ?? 5));
            $autos = array_values(array_filter($this->all(), static fn (array $b): bool => $b['auto']));
            foreach (array_slice($autos, $keep) as $old) {
                $this->delete((string) $old['name']);
            }
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function appVersion(): string
    {
        $file = $this->base . '/version.json';
        if (is_file($file)) {
            $v = json_decode((string) file_get_contents($file), true);
            if (is_array($v)) {
                return ($v['core'] ?? 0) . '.' . ($v['system'] ?? 0) . '.' . ($v['other'] ?? 0);
            }
        }
        return '?';
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $f) {
            $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
        }
        rmdir($dir);
    }
}
