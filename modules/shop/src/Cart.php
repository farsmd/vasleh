<?php
declare(strict_types=1);

namespace Modules\Shop;

use Modules\Session\SessionManager;

/** سبد خرید مهمان/کاربر در سشن: کلید هر ردیف «p{product}v{variant}» است. */
final class Cart
{
    private const KEY = 'shop_cart';

    public function __construct(private readonly SessionManager $session) {}

    private function raw(): array
    {
        $cart = $this->session->get(self::KEY, []);
        return is_array($cart) ? $cart : [];
    }

    private function save(array $cart): void
    {
        $this->session->set(self::KEY, $cart);
    }

    public static function key(int $productId, int $variantId): string
    {
        return 'p' . $productId . 'v' . $variantId;
    }

    public function add(int $productId, int $variantId, int $qty): void
    {
        if ($qty < 1) {
            $qty = 1;
        }
        $cart = $this->raw();
        $key = self::key($productId, $variantId);
        $cart[$key] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'qty' => (int) ($cart[$key]['qty'] ?? 0) + $qty,
        ];
        $this->save($cart);
    }

    public function setQty(string $key, int $qty): void
    {
        $cart = $this->raw();
        if (!isset($cart[$key])) {
            return;
        }
        if ($qty < 1) {
            unset($cart[$key]);
        } else {
            $cart[$key]['qty'] = $qty;
        }
        $this->save($cart);
    }

    public function remove(string $key): void
    {
        $cart = $this->raw();
        unset($cart[$key]);
        $this->save($cart);
    }

    public function clear(): void
    {
        $this->save([]);
    }

    public function count(): int
    {
        $n = 0;
        foreach ($this->raw() as $row) {
            $n += (int) $row['qty'];
        }
        return $n;
    }

    /**
     * ردیف‌های سبد با قیمت و موجودی زنده از کاتالوگ.
     * هر ردیف: key, product, variant|null, options_text, unit_price, qty, line_total, stock (null=بدون محدودیت)
     */
    public function items(Catalog $catalog): array
    {
        $attrs = $catalog->attributesBySlug();
        $out = [];
        foreach ($this->raw() as $key => $row) {
            $product = $catalog->product((int) $row['product_id']);
            if ($product === null || $product['status'] !== 'published') {
                continue;
            }
            $variant = null;
            if ((int) $row['variant_id'] > 0) {
                $variant = $catalog->variant((int) $row['variant_id']);
                if ($variant === null || (int) $variant['product_id'] !== (int) $product['id']) {
                    $variant = null;
                }
            }
            $unit = $variant !== null ? Catalog::variantPrice($product, $variant) : Catalog::effectivePrice($product);
            $stock = null;
            if ((int) $product['track_stock'] === 1) {
                $stock = $variant !== null ? (int) $variant['stock'] : (int) $product['stock'];
            }
            $out[] = [
                'key' => $key,
                'product' => $product,
                'variant' => $variant,
                'options_text' => $variant !== null ? Catalog::optionsText($variant['options'], $attrs) : '',
                'unit_price' => $unit,
                'qty' => (int) $row['qty'],
                'line_total' => $unit * (int) $row['qty'],
                'stock' => $stock,
            ];
        }
        return $out;
    }

    public function subtotal(Catalog $catalog): int
    {
        $sum = 0;
        foreach ($this->items($catalog) as $row) {
            $sum += $row['line_total'];
        }
        return $sum;
    }

    /** هزینه ارسال با قانون «بالای سقف مشخص، رایگان». */
    public static function shippingFor(int $subtotal, ShopSettings $settings): int
    {
        if ($subtotal <= 0) {
            return 0;
        }
        $freeOver = $settings->getInt('free_shipping_over', 0);
        if ($freeOver > 0 && $subtotal >= $freeOver) {
            return 0;
        }
        return $settings->getInt('shipping_cost', 0);
    }
}
