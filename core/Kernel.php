<?php
declare(strict_types=1);

namespace Core;

/**
 * هسته: درخواست را از index.php می‌گیرد، ماژول‌ها را لود می‌کند،
 * سرویس‌ها و روت‌ها و هوک‌هایشان را جمع می‌کند و درخواست را
 * به ماژول مرتبط می‌سپارد. هسته هیچ ماژولی را نمی‌شناسد؛
 * همه‌چیز از تعریف core.php خود ماژول‌ها می‌آید.
 */
final class Kernel
{
    public function __construct(private readonly string $basePath) {}

    public function handle(Request $request): Response
    {
        View::init($this->basePath);

        $loader = new ModuleLoader(
            $this->basePath . '/modules',
            $this->basePath . '/storage/modules-state.json',
            $this->basePath . '/storage/modules-meta.json',
        );

        $container = new Container();
        $container->setInstance('loader', $loader);
        $container->setInstance('base_path', $this->basePath);
        $container->setInstance('app.key', $this->appKey());

        $active = $loader->active();

        // ۱) سرویس‌های ماژول‌ها
        foreach ($active as $definition) {
            foreach ((array) ($definition['services'] ?? []) as $id => $factory) {
                $container->set((string) $id, $factory);
            }
        }

        try {
            // ۲) مایگریشن‌های ماژول‌ها (هر ماژول جدول‌های خودش را می‌سازد)
            foreach ($active as $definition) {
                if (isset($definition['migrate']) && is_callable($definition['migrate'])) {
                    ($definition['migrate'])($container);
                }
            }

            // ۳) روت‌های ماژول‌ها
            $router = new Router();
            foreach ($active as $slug => $definition) {
                if (isset($definition['routes']) && is_callable($definition['routes'])) {
                    ($definition['routes'])($router, $container, $slug);
                }
            }

            // ۴) هوک‌های قبل از اجرا (مثل گارد امنیتی ماژول session)
            foreach ($active as $definition) {
                $hook = $definition['hooks']['before_dispatch'] ?? null;
                if (is_callable($hook)) {
                    $early = $hook($request, $container);
                    if ($early instanceof Response) {
                        return $this->secure($early);
                    }
                }
            }

            // ۵) سپردن درخواست به ماژول مرتبط
            return $this->secure($router->dispatch($request, $container));
        } catch (\Throwable $error) {
            error_log('[kernel] ' . $error->getMessage());
            return $this->secure(new Response('<h1>۵۰۰</h1><p>خطای داخلی رخ داد.</p>', 500));
        }
    }

    /** هدرهای امنیتی پایه روی همه پاسخ‌ها. */
    private function secure(Response $response): Response
    {
        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            ->withHeader('Referrer-Policy', 'same-origin');
    }

    /** کلید برنامه برای امضای کوکی‌ها — خودکار ساخته می‌شود و از وب در دسترس نیست. */
    private function appKey(): string
    {
        $file = $this->basePath . '/storage/app.key';
        if (!is_file($file)) {
            file_put_contents($file, bin2hex(random_bytes(32)));
        }
        return trim((string) file_get_contents($file));
    }
}
