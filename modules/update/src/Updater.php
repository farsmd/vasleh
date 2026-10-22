<?php
declare(strict_types=1);

namespace Modules\Update;

use RuntimeException;
use ZipArchive;

/**
 * آپدیتر گیت‌هابی وصله.
 *
 * نسخه‌بندی سه‌بخشی در فایل version.json ریشه: {"core":X,"system":Y,"other":Z}
 * نمایش: X.Y.Z — عدد اول نسخه هسته، دوم ماژول‌های سیستمی، سوم بقیه ماژول‌ها و فایل‌ها.
 *
 * قواعد ایمنی (درس‌های آپدیتر لاینرلایت):
 * - پوشه storage هرگز دست نمی‌خورد: دیتابیس، app.key، وضعیت‌ها و بکاپ‌ها آنجاست.
 * - قبل از نصب، از همه فایل‌هایی که عوض/حذف می‌شوند بکاپ در storage/backups می‌گیرد.
 * - حذف فایل فقط برای فایل‌هایی است که قبلاً از گیت‌هاب نصب شده‌اند
 *   (مانیفست storage/update-manifest.json) — ماژول‌های دستی کاربر هرگز حذف نمی‌شوند.
 * - منبع پیش‌فرض گیت‌هاب است؛ برای تست/آینه، storage/update-source.json
 *   می‌تواند {version_url, zip_url} دیگری بدهد.
 */
final class Updater
{
    public const REPO = 'farsmd/vasleh';
    public const BRANCH = 'main';

    private const PARTS = ['core', 'system', 'other'];

    public function __construct(private readonly string $base) {}

    // ---------- نسخه ----------

    /** @return array{core:int, system:int, other:int} */
    public function localVersion(): array
    {
        return $this->readVersionFile($this->base . '/version.json') ?? ['core' => 0, 'system' => 0, 'other' => 0];
    }

    /** @param array{core:int, system:int, other:int} $v */
    public static function format(array $v): string
    {
        return $v['core'] . '.' . $v['system'] . '.' . $v['other'];
    }

    /** @return array{core:int, system:int, other:int}|null */
    private function readVersionFile(string $file): ?array
    {
        if (!is_file($file)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data)) {
            return null;
        }
        return [
            'core'   => (int) ($data['core'] ?? 0),
            'system' => (int) ($data['system'] ?? 0),
            'other'  => (int) ($data['other'] ?? 0),
        ];
    }

    /** مقایسه سه‌بخشی: بزرگ‌تر بودن هر بخشِ چپ‌تر تعیین‌کننده است. */
    public static function isNewer(array $remote, array $local): bool
    {
        foreach (self::PARTS as $part) {
            if ($remote[$part] !== $local[$part]) {
                return $remote[$part] > $local[$part];
            }
        }
        return false;
    }

    // ---------- منبع ----------

    /** @return array{version_url:string, zip_url:string, commits_url:?string} */
    public function source(): array
    {
        $overrideFile = $this->base . '/storage/update-source.json';
        if (is_file($overrideFile)) {
            $data = json_decode((string) file_get_contents($overrideFile), true);
            if (is_array($data) && !empty($data['version_url']) && !empty($data['zip_url'])) {
                return [
                    'version_url' => (string) $data['version_url'],
                    'zip_url'     => (string) $data['zip_url'],
                    'commits_url' => null,
                ];
            }
        }
        return [
            'version_url' => 'https://raw.githubusercontent.com/' . self::REPO . '/' . self::BRANCH . '/version.json',
            'zip_url'     => 'https://codeload.github.com/' . self::REPO . '/zip/refs/heads/' . self::BRANCH,
            'commits_url' => 'https://api.github.com/repos/' . self::REPO . '/commits?per_page=6&sha=' . self::BRANCH,
        ];
    }

    private function fetch(string $url, int $timeout = 30): string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_USERAGENT      => 'Vasleh-Updater',
            ]);
            $out = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($out === false || $code >= 400) {
                throw new RuntimeException('دانلود ناموفق بود (' . ($code ?: $err) . '): ' . $url);
            }
            return (string) $out;
        }
        $context = stream_context_create(['http' => [
            'method'          => 'GET',
            'timeout'         => $timeout,
            'follow_location' => 1,
            'max_redirects'   => 5,
            'header'          => "User-Agent: Vasleh-Updater\r\n",
        ]]);
        $out = @file_get_contents($url, false, $context);
        if ($out === false) {
            throw new RuntimeException('دانلود ناموفق بود: ' . $url);
        }
        return $out;
    }

    // ---------- بررسی ----------

    /** پیش‌نیازهای هاست برای آپدیت. */
    public function preflight(): array
    {
        return [
            ['label' => 'اکستنشن ZipArchive', 'ok' => class_exists(ZipArchive::class)],
            ['label' => 'دانلود از اینترنت (cURL یا allow_url_fopen)', 'ok' => function_exists('curl_init') || (bool) ini_get('allow_url_fopen')],
            ['label' => 'پوشه storage قابل نوشتن', 'ok' => is_writable($this->base . '/storage')],
            ['label' => 'فایل version.json موجود', 'ok' => is_file($this->base . '/version.json')],
        ];
    }

    /**
     * وضعیت نسخه: محلی، روی گیت‌هاب، تازه‌تر هست یا نه، کدام بخش‌ها عوض شده‌اند
     * و آخرین کامیت‌ها برای نمایش.
     */
    public function check(): array
    {
        $local = $this->localVersion();
        $out = [
            'local'   => $local,
            'remote'  => null,
            'newer'   => false,
            'changed' => ['core' => false, 'system' => false, 'other' => false],
            'commits' => [],
            'error'   => null,
        ];
        try {
            $source = $this->source();
            $remote = $this->readVersionData($this->fetch($source['version_url'], 15));
            if ($remote === null) {
                throw new RuntimeException('فایل نسخه گیت‌هاب خوانا نیست');
            }
            $out['remote'] = $remote;
            $out['newer'] = self::isNewer($remote, $local);
            foreach (self::PARTS as $part) {
                $out['changed'][$part] = $remote[$part] !== $local[$part];
            }
            if ($source['commits_url'] !== null) {
                try {
                    $commits = json_decode($this->fetch($source['commits_url'], 15), true);
                    foreach ((array) $commits as $commit) {
                        $out['commits'][] = [
                            'message' => strtok((string) ($commit['commit']['message'] ?? ''), "\n"),
                            'date'    => (string) ($commit['commit']['author']['date'] ?? ''),
                            'sha'     => substr((string) ($commit['sha'] ?? ''), 0, 7),
                        ];
                    }
                } catch (\Throwable) {
                    // فهرست کامیت‌ها فقط نمایشی است؛ نبودنش مانع آپدیت نیست.
                }
            }
        } catch (\Throwable $e) {
            $out['error'] = $e->getMessage();
        }
        return $out;
    }

    /** @return array{core:int, system:int, other:int}|null */
    private function readVersionData(string $json): ?array
    {
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }
        return [
            'core'   => (int) ($data['core'] ?? 0),
            'system' => (int) ($data['system'] ?? 0),
            'other'  => (int) ($data['other'] ?? 0),
        ];
    }

    // ---------- نصب ----------

    /**
     * دانلود و نصب نسخه گیت‌هاب.
     * @return array{from:string, to:string, written:array{core:int, system:int, other:int}, deleted:int, backup:string, log:string[]}
     */
    public function apply(): array
    {
        foreach ($this->preflight() as $item) {
            if (!$item['ok']) {
                throw new RuntimeException('پیش‌نیاز برآورده نیست: ' . $item['label']);
            }
        }
        $lockFile = $this->base . '/storage/update.lock';
        $lock = fopen($lockFile, 'c');
        if ($lock === false || !flock($lock, LOCK_EX | LOCK_NB)) {
            throw new RuntimeException('یک آپدیت دیگر در حال اجراست');
        }

        $log = [];
        $stamp = date('Ymd-His');
        $work = $this->base . '/storage/tmp-update-' . $stamp;
        try {
            $local = $this->localVersion();
            $source = $this->source();

            // ۱) دانلود و استخراج امن
            if (!mkdir($work . '/extracted', 0775, true)) {
                throw new RuntimeException('ساخت پوشه موقت ناموفق بود');
            }
            $zipPath = $work . '/remote.zip';
            file_put_contents($zipPath, $this->fetch($source['zip_url'], 120));
            $zip = new ZipArchive();
            if ($zip->open($zipPath) !== true) {
                throw new RuntimeException('فایل زیپ دانلودشده باز نشد');
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);
                if (str_contains($name, '..') || str_starts_with($name, '/')) {
                    throw new RuntimeException('مسیر مشکوک داخل زیپ: ' . $name);
                }
            }
            $zip->extractTo($work . '/extracted');
            $zip->close();
            $log[] = 'نسخه گیت‌هاب دانلود و استخراج شد.';

            $srcRoot = $this->findSourceRoot($work . '/extracted');
            if ($srcRoot === null) {
                throw new RuntimeException('ساختار زیپ ناشناخته است (index.php پیدا نشد)');
            }
            $remote = $this->readVersionFile($srcRoot . '/version.json');
            if ($remote === null) {
                throw new RuntimeException('version.json داخل زیپ نیست');
            }
            if (!self::isNewer($remote, $local)) {
                throw new RuntimeException('نسخه گیت‌هاب تازه‌تر نیست (' . self::format($remote) . ')');
            }

            // ۲) فهرست فایل‌های تازه + دسته‌بندی (storage هرگز)
            $remoteFiles = $this->listFiles($srcRoot);
            $remoteFiles = array_values(array_filter(
                $remoteFiles,
                static fn (string $rel): bool => !str_starts_with($rel, 'storage/'),
            ));
            $systemSlugs = $this->systemSlugs($srcRoot);

            $willWrite = [];
            foreach ($remoteFiles as $rel) {
                $target = $this->base . '/' . $rel;
                if (!is_file($target) || sha1_file($target) !== sha1_file($srcRoot . '/' . $rel)) {
                    $willWrite[$rel] = $this->bucket($rel, $systemSlugs);
                }
            }
            $manifest = $this->readManifest();
            $willDelete = [];
            if ($manifest !== null) {
                foreach ($manifest as $rel) {
                    if (!in_array($rel, $remoteFiles, true) && is_file($this->base . '/' . $rel)) {
                        $willDelete[] = $rel;
                    }
                }
            }
            $log[] = 'فایل‌های تازه/تغییرکرده: ' . count($willWrite) . ' — حذف‌شده از نسخه قبل: ' . count($willDelete);

            // ۳) بکاپ از هرچه عوض یا حذف می‌شود
            $backupDir = $this->base . '/storage/backups/' . $stamp;
            foreach (array_merge(array_keys($willWrite), $willDelete) as $rel) {
                $from = $this->base . '/' . $rel;
                if (is_file($from)) {
                    $to = $backupDir . '/' . $rel;
                    if (!is_dir(dirname($to))) {
                        mkdir(dirname($to), 0775, true);
                    }
                    copy($from, $to);
                }
            }
            $log[] = 'بکاپ گرفته شد: storage/backups/' . $stamp;

            // ۴) نوشتن فایل‌ها (موقت + جابه‌جایی)، بعد حذف‌ها
            $counts = ['core' => 0, 'system' => 0, 'other' => 0];
            foreach ($willWrite as $rel => $bucket) {
                $target = $this->base . '/' . $rel;
                if (!is_dir(dirname($target))) {
                    mkdir(dirname($target), 0775, true);
                }
                $tmp = $target . '.vasleh-tmp';
                copy($srcRoot . '/' . $rel, $tmp);
                rename($tmp, $target);
                $counts[$bucket]++;
            }
            foreach ($willDelete as $rel) {
                unlink($this->base . '/' . $rel);
            }

            // ۵) مانیفست تازه — مبنای حذف‌های آپدیت بعد
            file_put_contents(
                $this->base . '/storage/update-manifest.json',
                json_encode($remoteFiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            );
            $log[] = 'نصب کامل شد: ' . self::format($local) . ' ← ' . self::format($remote);

            return [
                'from'    => self::format($local),
                'to'      => self::format($remote),
                'written' => $counts,
                'deleted' => count($willDelete),
                'backup'  => 'storage/backups/' . $stamp,
                'log'     => $log,
            ];
        } finally {
            $this->removeTree($work);
            flock($lock, LOCK_UN);
            fclose($lock);
            @unlink($lockFile);
        }
    }

    // ---------- ابزارها ----------

    private function findSourceRoot(string $dir): ?string
    {
        if (is_file($dir . '/index.php')) {
            return $dir;
        }
        foreach (glob($dir . '/*', GLOB_ONLYDIR) ?: [] as $sub) {
            if (is_file($sub . '/index.php')) {
                return $sub;
            }
        }
        return null;
    }

    /** @return string[] مسیرهای نسبی همه فایل‌های زیر $root */
    private function listFiles(string $root): array
    {
        $out = [];
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($it as $file) {
            if ($file->isFile()) {
                $out[] = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
            }
        }
        sort($out);
        return $out;
    }

    /** ماژول‌های سیستمی نسخه تازه از روی core.php خودشان خوانده می‌شوند. */
    private function systemSlugs(string $srcRoot): array
    {
        $slugs = [];
        foreach (glob($srcRoot . '/modules/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $core = $dir . '/core.php';
            if (is_file($core) && preg_match("/'system'\\s*=>\\s*true/", (string) file_get_contents($core)) === 1) {
                $slugs[] = basename($dir);
            }
        }
        return $slugs;
    }

    private function bucket(string $rel, array $systemSlugs): string
    {
        if (str_starts_with($rel, 'core/') || in_array($rel, ['index.php', '.htaccess', 'version.json'], true)) {
            return 'core';
        }
        if (preg_match('#^modules/([a-z0-9_-]+)/#', $rel, $m) === 1 && in_array($m[1], $systemSlugs, true)) {
            return 'system';
        }
        return 'other';
    }

    /** @return string[]|null */
    private function readManifest(): ?array
    {
        $file = $this->base . '/storage/update-manifest.json';
        if (!is_file($file)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? array_values(array_map('strval', $data)) : null;
    }

    private function removeTree(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($dir);
    }
}
