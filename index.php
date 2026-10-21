<?php
declare(strict_types=1);

/*
 * index.php فقط یک کار دارد:
 * درخواست‌ها را جمع‌آوری می‌کند و به هسته تحویل می‌دهد.
 * هسته درخواست هر ماژول را به همان ماژول می‌فرستد.
 */

require __DIR__ . '/core/bootstrap.php';

use Core\Kernel;
use Core\Request;

(new Kernel(__DIR__))->handle(Request::capture())->send();
