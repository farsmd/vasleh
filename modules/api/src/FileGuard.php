<?php
declare(strict_types=1);

namespace Modules\Api;

use InvalidArgumentException;

/**
 * نگهبان مسیر فایل‌های API:
 * همه مسیرها نسبت به ریشه برنامه تفسیر می‌شوند؛ خروج از ریشه (../ و مسیر مطلق)
 * ممنوع است و فایل‌های حیاتی (کلید برنامه، فایل دیتابیس و فایل‌های WAL آن)
 * نه خوانده می‌شوند، نه نوشته، نه حذف.
 */
final class FileGuard
{
    private string $base;

    /** نام فایل‌هایی در storage/ که هرگز از راه فایل‌ها در دسترس نیستند. */
    private const DENIED_BASENAMES = ['app.key', 'database.sqlite', 'database.sqlite-wal', 'database.sqlite-shm'];

    public const MAX_READ = 2_000_000; // ۲ مگابایت

    public function __construct(string $basePath)
    {
        $real = realpath($basePath);
        if ($real === false) {
            throw new InvalidArgumentException('ریشه برنامه پیدا نشد');
        }
        $this->base = $real;
    }

    /** مسیر نسبی کاربر را به مسیر مطلق امن تبدیل می‌کند. */
    public function resolve(string $relative, bool $mustExist = false): string
    {
        $relative = str_replace('\\', '/', trim($relative));
        if ($relative === '' || $relative === '.') {
            return $this->base;
        }
        if (str_contains($relative, "\0") || str_starts_with($relative, '/') || preg_match('#^[a-zA-Z]:/#', $relative) === 1) {
            throw new InvalidArgumentException('مسیر نامعتبر است');
        }
        $parts = [];
        foreach (explode('/', $relative) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                throw new InvalidArgumentException('خروج از ریشه برنامه مجاز نیست');
            }
            $parts[] = $part;
        }
        $clean = implode('/', $parts);
        $this->assertNotDenied($clean);

        $candidate = $this->base . '/' . $clean;
        if (file_exists($candidate)) {
            $real = realpath($candidate);
            if ($real === false || ($real !== $this->base && !str_starts_with($real, $this->base . '/'))) {
                throw new InvalidArgumentException('مسیر بیرون از ریشه برنامه است');
            }
            return $real;
        }
        if ($mustExist) {
            throw new InvalidArgumentException('مسیر پیدا نشد: ' . $clean);
        }
        // برای نوشتن فایل تازه: نزدیک‌ترین جد موجود باید داخل ریشه باشد
        // (بقیه مسیر هنگام نوشتن ساخته می‌شود).
        $prefix = $parts;
        $rest = [];
        while ($prefix !== [] && !file_exists($this->base . '/' . implode('/', $prefix))) {
            array_unshift($rest, array_pop($prefix));
        }
        $anchor = $prefix === [] ? $this->base : realpath($this->base . '/' . implode('/', $prefix));
        if ($anchor === false || ($anchor !== $this->base && !str_starts_with($anchor, $this->base . '/'))) {
            throw new InvalidArgumentException('مسیر بیرون از ریشه برنامه است');
        }
        return $rest === [] ? $anchor : $anchor . '/' . implode('/', $rest);
    }

    private function assertNotDenied(string $clean): void
    {
        $base = basename($clean);
        if (str_starts_with($clean, 'storage/') && in_array($base, self::DENIED_BASENAMES, true)) {
            throw new InvalidArgumentException('این فایل از راه API در دسترس نیست');
        }
    }

    /** @return array<int, array{name:string, type:string, size:int|null, mtime:int}> */
    public function list(string $relative): array
    {
        $dir = $this->resolve($relative, true);
        if (!is_dir($dir)) {
            throw new InvalidArgumentException('مسیر پوشه نیست');
        }
        $out = [];
        foreach (scandir($dir) ?: [] as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }
            $childRel = ($relative === '' || $relative === '.' ? '' : rtrim($relative, '/') . '/') . $name;
            try {
                $this->assertNotDenied($childRel);
            } catch (InvalidArgumentException) {
                continue; // فایل‌های ممنوعه حتی فهرست هم نمی‌شوند
            }
            $path = $dir . '/' . $name;
            $isDir = is_dir($path);
            $out[] = [
                'name'  => $name,
                'type'  => $isDir ? 'dir' : 'file',
                'size'  => $isDir ? null : (int) filesize($path),
                'mtime' => (int) filemtime($path),
            ];
        }
        usort($out, static fn (array $a, array $b): int => [$a['type'] !== 'dir', $a['name']] <=> [$b['type'] !== 'dir', $b['name']]);
        return $out;
    }

    /** @return array{content:string, encoding:string, size:int} */
    public function read(string $relative): array
    {
        $file = $this->resolve($relative, true);
        if (!is_file($file)) {
            throw new InvalidArgumentException('مسیر فایل نیست');
        }
        $size = (int) filesize($file);
        if ($size > self::MAX_READ) {
            throw new InvalidArgumentException('فایل بزرگ‌تر از سقف خواندن است (۲ مگابایت)');
        }
        $bytes = (string) file_get_contents($file);
        if (str_contains($bytes, "\0") || preg_match('//u', $bytes) !== 1) {
            return ['content' => base64_encode($bytes), 'encoding' => 'base64', 'size' => $size];
        }
        return ['content' => $bytes, 'encoding' => 'utf8', 'size' => $size];
    }

    public function write(string $relative, string $bytes): int
    {
        if ($relative === '' || $relative === '.') {
            throw new InvalidArgumentException('مسیر فایل لازم است');
        }
        $file = $this->resolve($relative);
        if (is_dir($file)) {
            throw new InvalidArgumentException('مسیر پوشه است');
        }
        $dir = dirname($file);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new InvalidArgumentException('ساخت پوشه ناممکن شد');
        }
        if (file_put_contents($file, $bytes) === false) {
            throw new InvalidArgumentException('نوشتن فایل ناموفق بود');
        }
        return strlen($bytes);
    }

    public function delete(string $relative): void
    {
        $path = $this->resolve($relative, true);
        if ($path === $this->base) {
            throw new InvalidArgumentException('حذف ریشه مجاز نیست');
        }
        if (is_dir($path)) {
            if (count(scandir($path) ?: []) > 2) {
                throw new InvalidArgumentException('پوشه خالی نیست');
            }
            rmdir($path);
            return;
        }
        unlink($path);
    }
}
