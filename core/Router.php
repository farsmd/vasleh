<?php
declare(strict_types=1);

namespace Core;

/**
 * روتر هسته: هر ماژول روت‌های خودش را ثبت می‌کند؛
 * روتر فقط تطبیق می‌دهد و درخواست را به هندلر همان ماژول می‌سپارد.
 * الگو از {param} پشتیبانی می‌کند.
 */
final class Router
{
    /** @var array<int, array{method:string, regex:string, handler:mixed, module:string}> */
    private array $routes = [];

    public function add(string $method, string $pattern, mixed $handler, string $module): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
        $this->routes[] = ['method' => strtoupper($method), 'regex' => $regex, 'handler' => $handler, 'module' => $module];
    }

    public function get(string $pattern, mixed $handler, string $module): void
    {
        $this->add('GET', $pattern, $handler, $module);
    }

    public function post(string $pattern, mixed $handler, string $module): void
    {
        $this->add('POST', $pattern, $handler, $module);
    }

    public function dispatch(Request $request, Container $container): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method) {
                continue;
            }
            if (preg_match($route['regex'], $request->path, $matches) !== 1) {
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $handler = $route['handler'];
            if (is_array($handler)) {
                [$class, $method] = $handler;
                $handler = [new $class($container), $method];
            }
            $result = $handler($request, $params);
            return $result instanceof Response ? $result : Response::html((string) $result);
        }

        return Response::notFound('مسیری برای این آدرس ثبت نشده است');
    }
}
