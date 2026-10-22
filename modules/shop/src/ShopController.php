<?php
declare(strict_types=1);

namespace Modules\Shop;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** صفحات عمومی فروشگاه: ویترین، محصول، سبد، تسویه، پیگیری، لیست قیمت و فاکتور. */
final class ShopController
{
    public function __construct(private readonly Container $container) {}

    private function catalog(): Catalog
    {
        return $this->container->get('shop.catalog');
    }

    private function settings(): ShopSettings
    {
        return $this->container->get('shop.settings');
    }

    private function cart(): Cart
    {
        return $this->container->get('shop.cart');
    }

    private function page(string $view, array $data, string $title, int $status = 200): Response
    {
        $settings = $this->settings();
        $content = View::render('shop', 'front', $view, $data + [
            'currency' => $settings->get('currency', 'تومان'),
        ]);
        return Response::html(View::render('shop', 'front', 'layout', [
            'title' => $title,
            'content' => $content,
            'shopTitle' => $settings->get('shop_title', 'فروشگاه'),
            'cartCount' => $this->cart()->count(),
            'categories' => $this->catalog()->categories(),
        ]), $status);
    }

    public function index(Request $request): Response
    {
        $catSlug = rawurldecode((string) $request->input('cat', ''));
        $catalog = $this->catalog();
        $activeCat = $catSlug !== '' ? $catalog->categoryBySlug($catSlug) : null;
        return $this->page('index', [
            'products' => $catalog->productsPublished($activeCat !== null ? (int) $activeCat['id'] : 0),
            'activeCat' => $activeCat,
        ], $this->settings()->get('shop_title', 'فروشگاه'));
    }

    public function product(Request $request, array $params): Response
    {
        $catalog = $this->catalog();
        $product = $catalog->productBySlug(rawurldecode((string) ($params['slug'] ?? '')));
        if ($product === null) {
            return $this->page('message', ['heading' => 'یافت نشد', 'text' => 'این محصول وجود ندارد یا منتشر نشده است.'], 'یافت نشد', 404);
        }
        $fields = [];
        $values = $catalog->valuesFor((int) $product['id']);
        foreach ($catalog->fields() as $field) {
            $value = $values[(int) $field['id']] ?? '';
            if ($value !== '') {
                $fields[] = ['title' => $field['title'], 'value' => $value, 'type' => $field['type']];
            }
        }
        return $this->page('product', [
            'product' => $product,
            'fields' => $fields,
            'variants' => $catalog->variantsFor((int) $product['id']),
            'attrs' => $catalog->attributesBySlug(),
            'category' => (int) $product['category_id'] > 0 ? $catalog->category((int) $product['category_id']) : null,
            'added' => $request->input('added') !== null,
        ], (string) $product['title']);
    }

    // ---------- سبد خرید ----------

    public function cartAdd(Request $request): Response
    {
        $catalog = $this->catalog();
        $productId = (int) $request->input('product_id', 0);
        $variantId = (int) $request->input('variant_id', 0);
        $qty = max(1, (int) $request->input('qty', 1));
        $product = $catalog->product($productId);
        if ($product !== null && $product['status'] === 'published') {
            $this->cart()->add($productId, $variantId, $qty);
            return Response::redirect('/shop/product/' . rawurlencode((string) $product['slug']) . '?added=1');
        }
        return Response::redirect('/shop');
    }

    public function cartShow(Request $request): Response
    {
        $items = $this->cart()->items($this->catalog());
        $subtotal = 0;
        foreach ($items as $row) {
            $subtotal += $row['line_total'];
        }
        $shipping = Cart::shippingFor($subtotal, $this->settings());
        $taxPercent = $this->settings()->getInt('tax_percent', 0);
        $tax = $taxPercent > 0 ? (int) round($subtotal * $taxPercent / 100) : 0;
        return $this->page('cart', [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'taxPercent' => $taxPercent,
            'total' => $subtotal + $shipping + $tax,
        ], 'سبد خرید');
    }

    public function cartUpdate(Request $request): Response
    {
        $key = (string) $request->input('key', '');
        $this->cart()->setQty($key, (int) $request->input('qty', 0));
        return Response::redirect('/shop/cart');
    }

    public function cartRemove(Request $request): Response
    {
        $this->cart()->remove((string) $request->input('key', ''));
        return Response::redirect('/shop/cart');
    }

    // ---------- تسویه و ثبت سفارش ----------

    public function checkoutForm(Request $request): Response
    {
        if ($this->cart()->count() === 0) {
            return Response::redirect('/shop/cart');
        }
        return $this->checkoutPage([], null);
    }

    private function checkoutPage(array $old, ?string $error): Response
    {
        $items = $this->cart()->items($this->catalog());
        $subtotal = 0;
        foreach ($items as $row) {
            $subtotal += $row['line_total'];
        }
        $shipping = Cart::shippingFor($subtotal, $this->settings());
        $taxPercent = $this->settings()->getInt('tax_percent', 0);
        $tax = $taxPercent > 0 ? (int) round($subtotal * $taxPercent / 100) : 0;
        return $this->page('checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'taxPercent' => $taxPercent,
            'total' => $subtotal + $shipping + $tax,
            'old' => $old,
            'error' => $error,
            'orderNote' => $this->settings()->get('order_note', ''),
        ], 'ثبت سفارش');
    }

    public function checkoutPlace(Request $request): Response
    {
        $catalog = $this->catalog();
        $cart = $this->cart();
        $old = [
            'name' => trim((string) $request->input('name', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'email' => trim((string) $request->input('email', '')),
            'address' => trim((string) $request->input('address', '')),
            'note' => trim((string) $request->input('note', '')),
        ];
        $items = $cart->items($catalog);
        if ($items === []) {
            return Response::redirect('/shop/cart');
        }
        if ($old['name'] === '' || $old['phone'] === '') {
            return $this->checkoutPage($old, 'نام و شماره موبایل الزامی است.');
        }
        foreach ($items as $row) {
            if ($row['stock'] !== null && $row['qty'] > $row['stock']) {
                return $this->checkoutPage($old, 'موجودی «' . $row['product']['title'] . '» کافی نیست (موجودی: ' . $row['stock'] . ').');
            }
        }
        $subtotal = 0;
        $orderItems = [];
        foreach ($items as $row) {
            $subtotal += $row['line_total'];
            $orderItems[] = [
                'product_id' => (int) $row['product']['id'],
                'variant_id' => $row['variant'] !== null ? (int) $row['variant']['id'] : 0,
                'title' => (string) $row['product']['title'],
                'options_text' => $row['options_text'],
                'unit_price' => $row['unit_price'],
                'qty' => $row['qty'],
            ];
        }
        $shipping = Cart::shippingFor($subtotal, $this->settings());
        $taxPercent = $this->settings()->getInt('tax_percent', 0);
        [, $code] = $this->container->get('shop.orders')->create($old, $orderItems, $shipping, $taxPercent);
        foreach ($orderItems as $item) {
            $catalog->decreaseStock($item['product_id'], $item['variant_id'], $item['qty']);
        }
        $cart->clear();
        if ($this->container->has('logger')) {
            $this->container->get('logger')->system('ثبت سفارش فروشگاه', $code, $old['phone']);
        }
        return Response::redirect('/shop/success/' . $code);
    }

    public function success(Request $request, array $params): Response
    {
        $code = (string) ($params['code'] ?? '');
        $order = $this->container->get('shop.orders')->findByCode($code);
        if ($order === null) {
            return Response::redirect('/shop');
        }
        return $this->page('message', [
            'heading' => 'سفارش شما ثبت شد',
            'text' => 'کد پیگیری سفارش: ' . $code . ' — برای دیدن وضعیت سفارش از صفحه «پیگیری سفارش» با همین کد و شماره موبایلتان استفاده کنید.',
        ], 'سفارش ثبت شد');
    }

    // ---------- پیگیری و فاکتور ----------

    public function trackForm(Request $request): Response
    {
        return $this->page('track', ['order' => null, 'error' => null, 'code' => '', 'phone' => ''], 'پیگیری سفارش');
    }

    public function trackLookup(Request $request): Response
    {
        $code = trim((string) $request->input('code', ''));
        $phone = trim((string) $request->input('phone', ''));
        $order = $this->container->get('shop.orders')->track($code, $phone);
        return $this->page('track', [
            'order' => $order,
            'error' => $order === null ? 'سفارشی با این کد و شماره موبایل یافت نشد.' : null,
            'code' => $code,
            'phone' => $phone,
            'statuses' => Orders::STATUSES,
        ], 'پیگیری سفارش');
    }

    /** فاکتور عمومی — فقط با کد سفارش + شماره موبایل صاحب سفارش. */
    public function invoice(Request $request, array $params): Response
    {
        $orders = $this->container->get('shop.orders');
        $order = $orders->track((string) ($params['code'] ?? ''), (string) $request->input('phone', ''));
        if ($order === null) {
            return $this->page('message', ['heading' => 'یافت نشد', 'text' => 'برای دیدن فاکتور، از صفحه پیگیری سفارش وارد شوید.'], 'فاکتور', 404);
        }
        $settings = $this->settings();
        return Response::html(View::render('shop', 'front', 'invoice', [
            'order' => $order,
            'items' => $order['items'],
            'statuses' => Orders::STATUSES,
            'shopTitle' => $settings->get('shop_title', 'فروشگاه'),
            'currency' => $settings->get('currency', 'تومان'),
        ]));
    }

    public function pricelist(Request $request): Response
    {
        $catalog = $this->catalog();
        $byCategory = [];
        foreach ($catalog->productsPublished() as $product) {
            $byCategory[(int) $product['category_id']][] = $product;
        }
        $variants = [];
        foreach ($byCategory as $products) {
            foreach ($products as $product) {
                $variants[(int) $product['id']] = $catalog->variantsFor((int) $product['id']);
            }
        }
        return $this->page('pricelist', [
            'byCategory' => $byCategory,
            'categories' => $catalog->categories(),
            'variants' => $variants,
            'attrs' => $catalog->attributesBySlug(),
        ], 'لیست قیمت');
    }
}
