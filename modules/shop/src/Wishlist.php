<?php
declare(strict_types=1);

namespace Modules\Shop;

use Modules\Session\SessionManager;

/** علاقه‌مندی‌های مهمان در سشن (بدون حساب کاربری): فهرست شناسه‌های محصول. */
final class Wishlist
{
    private const KEY = 'shop_wishlist';

    public function __construct(private readonly SessionManager $session) {}

    /** @return int[] */
    public function ids(): array
    {
        $raw = $this->session->get(self::KEY, []);
        if (!is_array($raw)) {
            return [];
        }
        $ids = [];
        foreach ($raw as $id) {
            $ids[] = (int) $id;
        }
        return array_values(array_unique($ids));
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function toggle(int $productId): bool
    {
        $ids = $this->ids();
        if (in_array($productId, $ids, true)) {
            $ids = array_values(array_filter($ids, static fn (int $id): bool => $id !== $productId));
            $this->session->set(self::KEY, $ids);
            return false;
        }
        $ids[] = $productId;
        $this->session->set(self::KEY, $ids);
        return true;
    }

    public function count(): int
    {
        return count($this->ids());
    }

    /** محصولات منتشرشده‌ی ذخیره‌شده، به ترتیب ذخیره. */
    public function products(Catalog $catalog): array
    {
        $out = [];
        foreach ($this->ids() as $id) {
            $product = $catalog->product($id);
            if ($product !== null && $product['status'] === 'published') {
                $out[] = $product;
            }
        }
        return $out;
    }
}
