<?php
declare(strict_types=1);

namespace Core;

/** پاسخ خروجی ماژول‌ها. */
final class Response
{
    public function __construct(
        public string $body = '',
        public int $status = 200,
        public array $headers = ['Content-Type' => 'text/html; charset=utf-8'],
    ) {}

    public static function html(string $body, int $status = 200): self
    {
        return new self($body, $status);
    }

    public static function json(array $data, int $status = 200): self
    {
        return new self(
            (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            $status,
            ['Content-Type' => 'application/json; charset=utf-8'],
        );
    }

    public static function redirect(string $to): self
    {
        return new self('', 302, ['Location' => $to]);
    }

    public static function forbidden(string $message = 'دسترسی ندارید'): self
    {
        return new self('<h1>۴۰۳</h1><p>' . e($message) . '</p>', 403);
    }

    public static function notFound(string $message = 'یافت نشد'): self
    {
        return new self('<h1>۴۰۴</h1><p>' . e($message) . '</p>', 404);
    }

    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->status);
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo $this->body;
    }
}
