<?php
declare(strict_types=1);

namespace Modules\Admin;

/**
 * نصب ماژول از فایل ZIP — قلب «هر نرم‌افزار یک ماژول است»:
 * زیپ باید دقیقاً یک پوشه ریشه هم‌نام نامک ماژول داشته باشد و داخلش core.php.
 * مسیرهای خطرناک (تراورس/مطلق) رد می‌شوند؛ روی ماژول موجود نصب نمی‌شود؛
 * تعریف ماژول قبل از فعال‌شدن خوانده و اعتبارسنجی می‌شود.
 * بعد از نصب، ماژول از درخواست بعدی زنده است (روت/منو/مایگریشن خودش)
 * و اگر خراب باشد، سخت‌سازی هسته فقط خودش را کنار می‌گذارد.
 */
final class ModuleInstaller
{
    private const MAX_ZIP = 10 * 1024 * 1024;       // ۱۰ مگابایت
    private const MAX_TOTAL = 50 * 1024 * 1024;     // مجموع بازشده: ۵۰ مگابایت
    private const MAX_ENTRIES = 2000;

    public function __construct(
        private readonly string $modulesDir,
        private readonly string $storageDir,
    ) {}

    /** @param array{tmp_name?:string,error?:int,size?:int} $upload یک عضو $_FILES */
    public function install(array $upload): array
    {
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($upload['tmp_name'])) {
            return $this->fail('فایلی سالم نرسید؛ دوباره انتخاب کنید');
        }
        if ((int) ($upload['size'] ?? 0) > self::MAX_ZIP) {
            return $this->fail('حجم ZIP بیش از ۱۰ مگابایت است');
        }
        if (!class_exists(\ZipArchive::class)) {
            return $this->fail('افزونه zip روی این هاست فعال نیست');
        }
        $zip = new \ZipArchive();
        if ($zip->open((string) $upload['tmp_name']) !== true) {
            return $this->fail('فایل ZIP باز نشد');
        }

        $tops = [];
        $total = 0;
        $hasCore = false;
        $count = $zip->numFiles;
        if ($count > self::MAX_ENTRIES) {
            $zip->close();
            return $this->fail('تعداد فایل‌های ZIP بیش از حد است');
        }
        for ($i = 0; $i < $count; $i++) {
            $name = str_replace('\\', '/', (string) $zip->getNameIndex($i));
            if (str_starts_with($name, '/') || preg_match('#(^|/)\.\.(/|$)#', $name)) {
                $zip->close();
                return $this->fail('مسیر خطرناک داخل ZIP هست: ' . $name);
            }
            $stat = $zip->statIndex($i);
            $total += (int) ($stat['size'] ?? 0);
            $parts = explode('/', trim($name, '/'));
            if ($parts[0] !== '') {
                $tops[$parts[0]] = true;
            }
            if (count($parts) === 2 && $parts[1] === 'core.php') {
                $hasCore = true;
            }
        }
        if ($total > self::MAX_TOTAL) {
            $zip->close();
            return $this->fail('حجم بازشده ZIP بیش از ۵۰ مگابایت است');
        }
        if (count($tops) !== 1) {
            $zip->close();
            return $this->fail('ZIP باید دقیقاً یک پوشه ریشه (هم‌نام ماژول) داشته باشد');
        }
        $slug = (string) array_key_first($tops);
        if (!preg_match('/^[a-z][a-z0-9_-]*$/', $slug)) {
            $zip->close();
            return $this->fail('نام پوشه ماژول معتبر نیست (حروف کوچک لاتین، عدد، - و _)');
        }
        if (!$hasCore) {
            $zip->close();
            return $this->fail('داخل پوشه ماژول فایل core.php نیست');
        }
        if (is_dir($this->modulesDir . '/' . $slug)) {
            $zip->close();
            return $this->fail('ماژولی با نامک «' . $slug . '» از قبل هست؛ برای جایگزینی اول پوشه‌اش را از هاست پاک کنید');
        }

        $tmp = $this->storageDir . '/tmp-install-' . bin2hex(random_bytes(4));
        if (!mkdir($tmp, 0775, true) && !is_dir($tmp)) {
            $zip->close();
            return $this->fail('ساخت پوشه موقت نشد');
        }
        $zip->extractTo($tmp);
        $zip->close();

        $coreFile = $tmp . '/' . $slug . '/core.php';
        try {
            $definition = require $coreFile;
        } catch (\Throwable $error) {
            $this->removeTree($tmp);
            return $this->fail('فایل core.php ماژول خطا دارد: ' . substr($error->getMessage(), 0, 160));
        }
        if (!is_array($definition) || ($definition['slug'] ?? null) !== $slug) {
            $this->removeTree($tmp);
            return $this->fail('مقدار slug داخل core.php باید دقیقاً «' . $slug . '» باشد');
        }
        if (!rename($tmp . '/' . $slug, $this->modulesDir . '/' . $slug)) {
            $this->removeTree($tmp);
            return $this->fail('انتقال پوشه ماژول به modules نشد');
        }
        $this->removeTree($tmp);
        return ['ok' => true, 'slug' => $slug];
    }

    private function fail(string $error): array
    {
        return ['ok' => false, 'error' => $error];
    }

    private function removeTree(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($dir);
    }
}
