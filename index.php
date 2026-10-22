<?php
declare(strict_types=1);

/*
 * index.php فقط یک کار دارد:
 * درخواست‌ها را جمع‌آوری می‌کند و به هسته تحویل می‌دهد.
 * هسته درخواست هر ماژول را به همان ماژول می‌فرستد.
 */

// روی سرور واقعی فایل‌های استاتیک را خود وب‌سرور می‌دهد و درخواست به اینجا
// نمی‌رسد؛ این خط فقط برای سرور توسعه PHP است تا assets مستقیم سرو شوند.
$vaslehPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (str_starts_with($vaslehPath, '/assets/') && is_file(__DIR__ . $vaslehPath)) {
    return false;
}

require __DIR__ . '/core/bootstrap.php';

use Core\Kernel;
use Core\Request;

(new Kernel(__DIR__))->handle(Request::capture())->send();
