<?php
declare(strict_types=1);

namespace Core;

/**
 * ظرف سرویس‌ها: ماژول‌ها سرویس‌هایشان را اینجا ثبت می‌کنند
 * (مثل db از ماژول sqlite و session از ماژول session)
 * و ماژول‌های دیگر بدون دست‌کاری هسته از آن‌ها استفاده می‌کنند.
 */
final class Container
{
    /** @var array<string, callable> */
    private array $factories = [];

    /** @var array<string, mixed> */
    private array $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->instances[$id]);
    }

    public function setInstance(string $id, mixed $value): void
    {
        $this->instances[$id] = $value;
    }

    public function has(string $id): bool
    {
        return isset($this->factories[$id]) || array_key_exists($id, $this->instances);
    }

    public function get(string $id): mixed
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }
        if (!isset($this->factories[$id])) {
            throw new \RuntimeException('سرویس ثبت نشده است: ' . $id);
        }
        return $this->instances[$id] = ($this->factories[$id])($this);
    }
}
