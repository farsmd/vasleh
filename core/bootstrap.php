<?php
declare(strict_types=1);

/* اتولودر ساده بدون Composer — مناسب هاست اشتراکی:
   Core\X            => core/X.php
   Modules\<slug>\X  => modules/<slug>/src/X.php */

spl_autoload_register(static function (string $class): void {
    $root = dirname(__DIR__);

    if (str_starts_with($class, 'Core\\')) {
        $file = $root . '/core/' . str_replace('\\', '/', substr($class, 5)) . '.php';
    } elseif (str_starts_with($class, 'Modules\\')) {
        $parts = explode('\\', substr($class, 8));
        $module = strtolower((string) array_shift($parts));
        if (!preg_match('/^[a-z][a-z0-9_-]*$/', $module)) {
            return;
        }
        $file = $root . '/modules/' . $module . '/src/' . implode('/', $parts) . '.php';
    } else {
        return;
    }

    if (is_file($file)) {
        require $file;
    }
});

require __DIR__ . '/helpers.php';
