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
            $this->basePath . '/storage/migrations.json',
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
            // کش مایگریشن (۶٫۰): در حالت پایدار مایگریشن اجرا نمی‌شود؛ فقط
            // نصب تازه یا تغییر نسخه ماژول، مایگریشن آن را دوباره راه می‌اندازد.
            // خطای یک ماژول فقط خودش را کنار می‌گذارد، نه کل سیستم را:
            // سرویس‌هایش هم از ظرف حذف می‌شوند تا کسی صدایشان نزند.
            foreach ($active as $slug => $definition) {
                if (isset($definition['migrate']) && is_callable($definition['migrate'])) {
                    $migrateVersion = (string) ($definition['version'] ?? '');
                    if (!$loader->needsMigrate($slug, $migrateVersion)) {
                        continue;
                    }
                    try {
                        ($definition['migrate'])($container);
                        // فقط موفقیت ثبت می‌شود تا مایگریشن ناموفق، دفعه بعد دوباره تلاش شود.
                        $loader->markMigrated($slug, $migrateVersion);
                    } catch (\Throwable $error) {
                        error_log('[kernel] migrate ' . $slug . ': ' . $error->getMessage());
                        $loader->markError($slug, 'خطای مایگریشن: ' . substr($error->getMessage(), 0, 200));
                        if (method_exists($container, 'remove')) {
                            foreach (array_keys((array) ($definition['services'] ?? [])) as $serviceId) {
                                $container->remove((string) $serviceId);
                            }
                        }
                    }
                }
            }

            // ۳) روت‌های ماژول‌های سالم
            $router = new Router();
            foreach ($loader->active() as $slug => $definition) {
                if (isset($definition['routes']) && is_callable($definition['routes'])) {
                    try {
                        ($definition['routes'])($router, $container, $slug);
                    } catch (\Throwable $error) {
                        error_log('[kernel] routes ' . $slug . ': ' . $error->getMessage());
                        $loader->markError($slug, 'خطای ثبت روت: ' . substr($error->getMessage(), 0, 200));
                    }
                }
            }

            // ۴) هوک‌های قبل از اجرا (مثل گارد امنیتی ماژول session)
            // هوک خراب رد می‌شود و ماژول برای این درخواست کنار می‌رود؛
            // سیستم به‌خاطر هوک یک ماژول هرگز نمی‌خوابد.
            foreach ($loader->active() as $slug => $definition) {
                $hook = $definition['hooks']['before_dispatch'] ?? null;
                if (is_callable($hook)) {
                    try {
                        $early = $hook($request, $container);
                    } catch (\Throwable $error) {
                        error_log('[kernel] hook ' . $slug . ': ' . $error->getMessage());
                        $loader->markError($slug, 'خطای هوک: ' . substr($error->getMessage(), 0, 200));
                        continue;
                    }
                    if ($early instanceof Response) {
                        return $this->secure($early, $request, $container);
                    }
                }
            }

            // ۵) سپردن درخواست به ماژول مرتبط
            return $this->secure($router->dispatch($request, $container), $request, $container);
        } catch (\Throwable $error) {
            error_log('[kernel] ' . $error->getMessage());
            return $this->secure(new Response('<h1>۵۰۰</h1><p>خطای داخلی رخ داد.</p>', 500), $request, $container);
        }
    }

    /** هدرهای امنیتی پایه روی همه پاسخ‌ها + عبور از هوک صفحات خطا. */
    private function secure(Response $response, Request $request, Container $container): Response
    {
        $response = $this->applyErrorHooks($response, $request, $container);
        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'SAMEORIGIN')
            ->withHeader('Referrer-Policy', 'same-origin');
    }

    /**
     * هوک error_response (کلی، مخصوص هیچ ماژولی نیست): هر ماژول فعال
     * می‌تواند برای پاسخ خطا (وضعیت ۴۰۰ به بالا) بدنه جایگزین بدهد — مثلاً
     * صفحه خطای سفارشی CMS. اولین جایگزین برنده است؛ خطای هوک، پاسخ
     * اصلی را دست نمی‌زند و فقط لاگ می‌شود.
     */
    private function applyErrorHooks(Response $response, Request $request, Container $container): Response
    {
        if ($response->status < 400) {
            return $response;
        }
        if (!$container->has('loader')) {
            return $response;
        }
        foreach ($container->get('loader')->active() as $slug => $definition) {
            $hook = $definition['hooks']['error_response'] ?? null;
            if (!is_callable($hook)) {
                continue;
            }
            try {
                $replacement = $hook($response, $request, $container);
            } catch (\Throwable $error) {
                error_log('[kernel] error hook ' . $slug . ': ' . $error->getMessage());
                continue;
            }
            if ($replacement instanceof Response) {
                return $replacement;
            }
        }
        return $response;
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
