<?php
declare(strict_types=1);

/** فرار امن برای خروجی HTML — همه ویوها باید از همین استفاده کنند. */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
