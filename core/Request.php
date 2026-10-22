<?php
declare(strict_types=1);

namespace Core;

/** درخواست ورودی: متد، مسیر، کوئری و بدنه — یک‌جا و تمیز. */
final class Request
{
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $body = [],
    ) {}

    public static function capture(): self
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $rawPath = (string) (parse_url($uri, PHP_URL_PATH) ?? '/');

        // پشتیبانی از نصب در ساب‌فولدر هاست اشتراکی — فقط وقتی اسکریپت واقعاً
        // فرانت‌کنترلر (index.php) است؛ روی سرور توسعه PHP برای آدرس‌هایی که
        // بخش آخرشان نقطه دارد (مثل نام فایل بکاپ) SCRIPT_NAME خودِ مسیر
        // درخواست می‌شود و بریدن مسیر از روی آن، آدرس را خراب می‌کند.
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        if (basename($script) === 'index.php') {
            $scriptDir = rtrim(dirname($script), '/');
            if ($scriptDir !== '' && str_starts_with($rawPath, $scriptDir)) {
                $rawPath = substr($rawPath, strlen($scriptDir)) ?: '/';
            }
        }

        $path = '/' . trim($rawPath, '/');

        return new self(
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            $path === '/' ? '/' : rtrim($path, '/'),
            $_GET,
            $_POST,
        );
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }
}
