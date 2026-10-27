<?php
declare(strict_types=1);

namespace Modules\Shop;

/** آپلود و سرو عکس محصول: فایل‌ها در storage/uploads/shop می‌نشینند و از مسیر
 *  /shop/image/{name} سرو می‌شوند؛ نه در گیت می‌روند و نه سینک زنده دستشان می‌زند. */
final class ProductImage
{
    public const MAX_BYTES = 2 * 1024 * 1024;
    public const EXTENSIONS = [
        'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
        'webp' => 'image/webp', 'gif' => 'image/gif',
    ];

    public function __construct(private readonly string $basePath) {}

    public function dir(): string
    {
        return $this->basePath . '/storage/uploads/shop';
    }

    /** اعتبارسنجی فایل آپلودی؛ null یعنی معتبر یا اصلاً فایلی انتخاب نشده. */
    public function validate(?array $file): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return 'آپلود عکس ناموفق بود.';
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            return 'حجم عکس باید کمتر از ۲ مگابایت باشد.';
        }
        $ext = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!isset(self::EXTENSIONS[$ext])) {
            return 'فرمت عکس باید jpg، png، webp یا gif باشد.';
        }
        if (@getimagesize((string) ($file['tmp_name'] ?? '')) === false) {
            return 'فایل انتخاب‌شده عکس معتبر نیست.';
        }
        return null;
    }

    /** ذخیره فایل معتبر؛ مسیر عمومی (/shop/image/…) یا null. */
    public function store(?array $file, int $productId): ?string
    {
        if ($file === null || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));
        if (!isset(self::EXTENSIONS[$ext])) {
            return null;
        }
        if (!is_dir($this->dir())) {
            mkdir($this->dir(), 0777, true);
        }
        $name = 'p' . $productId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        if (!move_uploaded_file((string) $file['tmp_name'], $this->dir() . '/' . $name)) {
            return null;
        }
        return '/shop/image/' . $name;
    }

    public static function isUploaded(?string $image): bool
    {
        return is_string($image) && str_starts_with($image, '/shop/image/');
    }

    public function deleteFor(?string $image): void
    {
        if (!self::isUploaded($image)) {
            return;
        }
        $file = $this->dir() . '/' . basename((string) $image);
        if (is_file($file)) {
            unlink($file);
        }
    }

    /** خواندن فایل عکس برای سرو عمومی؛ [محتوا، نوع] یا null. */
    public function read(string $name): ?array
    {
        if (!preg_match('/^[A-Za-z0-9_.-]+$/', $name)) {
            return null;
        }
        $ext = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
        if (!isset(self::EXTENSIONS[$ext])) {
            return null;
        }
        $file = $this->dir() . '/' . basename($name);
        if (!is_file($file)) {
            return null;
        }
        $content = file_get_contents($file);
        return $content === false ? null : [$content, self::EXTENSIONS[$ext]];
    }
}
