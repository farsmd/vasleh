<?php
declare(strict_types=1);

namespace Modules\Shop;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;
use Modules\Session\SessionManager;
use Modules\Sms\SmsSender;

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

    private function coupons(): Coupons
    {
        return $this->container->get('shop.coupons');
    }

    private function wishlist(): Wishlist
    {
        return $this->container->get('shop.wishlist');
    }

    private function customers(): Customers
    {
        return $this->container->get('shop.customers');
    }

    /** مشتری واردشده فروشگاه (سشن جدا از کاربر ادمین) یا null. */
    private function templates(): ShopTemplates
    {
        return $this->container->get('shop.templates');
    }

    private function appVersion(): string
    {
        $file = dirname(__DIR__, 3) . '/version.json';
        if (!is_file($file)) {
            return '';
        }
        $j = json_decode((string) file_get_contents($file), true);
        if (!is_array($j)) {
            return '';
        }
        return (int) $j['core'] . '.' . (int) $j['system'] . '.' . (int) $j['other'];
    }

    /** نشان تعداد علاقه‌مندی‌ها (صفر باشد، خالی است). */
    private function wishlistBadge(int $count): string
    {
        if ($count <= 0) {
            return '';
        }
        return '<span class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white rounded-full text-[10px] flex items-center justify-center font-bold">' . $count . '</span>';
    }

    private function accountLink(?array $customer): string
    {
        $href = $customer !== null ? '/shop/account/orders' : '/shop/account';
        $title = $customer !== null ? 'حساب من' : 'ورود / ثبت‌نام';
        return '<a href="' . $href . '" class="relative w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-brand-600 flex items-center justify-center transition" title="' . $title . '">'
            . '<i class="fa-regular fa-user text-base"></i></a>';
    }

    private function categoriesNav(array $categories): string
    {
        $out = '';
        foreach ($categories as $c) {
            $out .= '<a href="/shop?cat=' . e(rawurlencode((string) $c['slug'])) . '" class="hover:text-brand-600 transition shrink-0">' . e((string) $c['title']) . '</a>';
        }
        return $out;
    }

    /** آیتم‌های دراور سبد — همان نشانه‌گذاری پوسته نکسوس. */
    private function cartDrawerItems(array $items, string $csrf): string
    {
        if ($items === []) {
            return '<div class="text-center py-12 text-slate-400 text-xs">سبد خرید شما فعلاً خالی است.</div>';
        }
        $out = '';
        foreach ($items as $row) {
            $product = $row['product'];
            $img = ($product['image'] ?? '') !== ''
                ? '<img src="' . e((string) $product['image']) . '" class="w-16 h-16 object-contain rounded-xl bg-white dark:bg-slate-900 p-1">'
                : '<span class="w-16 h-16 rounded-xl bg-white dark:bg-slate-900 text-slate-300 flex items-center justify-center text-xl"><i class="fa-regular fa-image"></i></span>';
            $opts = $row['options_text'] !== '' ? '<span class="text-[11px] text-slate-400">' . e((string) $row['options_text']) . '</span>' : '';
            $out .= '<div class="flex items-center gap-3.5 bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">'
                . $img
                . '<div class="flex-1 min-w-0">'
                . '<a href="/shop/product/' . e(rawurlencode((string) $product['slug'])) . '" class="text-xs font-extrabold text-slate-900 dark:text-white truncate block">' . e((string) $product['title']) . '</a>'
                . $opts
                . '<span class="text-xs font-black text-brand-600 block mt-1">' . number_format((int) $row['unit_price']) . ' تومان × ' . (int) $row['qty'] . '</span>'
                . '</div>'
                . '<form method="post" action="/shop/cart/remove">'
                . '<input type="hidden" name="_csrf" value="' . e($csrf) . '">'
                . '<input type="hidden" name="key" value="' . e((string) $row['key']) . '">'
                . '<button class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center" title="حذف"><i class="fa-solid fa-trash text-xs"></i></button>'
                . '</form></div>';
        }
        return $out;
    }

    private function customer(): ?array
    {
        $id = (int) $this->session()->get('shop_customer_id', 0);
        return $id > 0 ? $this->customers()->find($id) : null;
    }

    private function requireCustomer(): ?array
    {
        return $this->customer();
    }

    /** کوپن معتبر سشن برای این جمع (نامعتبر باشد، از سشن پاک می‌شود). */
    private function validCoupon(int $subtotal): array
    {
        $code = (string) $this->session()->get('shop_coupon', '');
        if ($code === '') {
            return [null, 0];
        }
        $coupon = $this->coupons()->findValid($code, $subtotal);
        if ($coupon === null) {
            $this->session()->set('shop_coupon', '');
            return [null, 0];
        }
        return [$coupon, Coupons::discountFor($coupon, $subtotal)];
    }

    /** خطوط «کلید: برچسب» تنظیمات → فهرست [کلید، برچسب]. */
    private static function parseKeyLines(string $raw): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            $parts = explode(':', $line, 2);
            $key = trim($parts[0]);
            $label = trim($parts[1] ?? $parts[0]);
            if ($key !== '' && $label !== '') {
                $out[] = [$key, $label];
            }
        }
        return $out;
    }

    /** خطوط ساده‌ی تنظیمات (هر خط یک بازه‌ی تحویل). */
    private static function parseLines(string $raw): array
    {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', $raw) ?: [] as $line) {
            $line = trim((string) $line);
            if ($line !== '') {
                $out[] = $line;
            }
        }
        return $out;
    }

    /** زیبال آماده است؟ ماژول فعال باشد و (حالت تست یا مرچنت ثبت‌شده). */
    private function zibalReady(): bool
    {
        if (!$this->container->has('zibal.payments')) {
            return false;
        }
        return $this->container->get('zibal.payments')->ready();
    }

    /** روش‌های پرداخت تسویه: تنظیمات فروشگاه + زیبال وقتی آماده است. */
    private function paymentMethods(): array
    {
        $methods = self::parseKeyLines($this->settings()->get('payment_methods', ''));
        if ($this->zibalReady()) {
            $methods[] = ['zibal', 'پرداخت اینترنتی (زیبال)'];
        }
        return $methods;
    }

    /** هدایت به درگاه زیبال پس از ثبت سفارش؛ ناموفق: برگشت به سبد با خطا. */
    private function zibalRedirect(string $code, int $totalToman): Response
    {
        $payments = $this->container->get('zibal.payments');
        $client = $this->container->get('zibal.client');
        $amountRial = $totalToman * 10;
        if ($amountRial < 1000) {
            return Response::redirect('/shop/cart?' . http_build_query(['err' => 'مبلغ سفارش برای پرداخت اینترنتی کافی نیست. سفارش ' . $code . ' ثبت شده و پرداخت نشده است.']));
        }
        $payId = $payments->createPending($code, $amountRial);
        $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $local = $host === 'localhost' || str_starts_with($host, '127.0.0.1') || str_starts_with($host, '192.168.') || str_starts_with($host, '10.');
        $scheme = (!$local && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) || str_starts_with($host, 'atroweb.ir') ? 'https' : 'http';
        $callback = $scheme . '://' . $host . '/zibal/callback';
        $res = $client->request($payments->merchant(), $amountRial, $callback, 'پرداخت سفارش ' . $code, $code);
        if (!($res['ok'] ?? false)) {
            $payments->markFailed($payId, 'failed', (string) ($res['message'] ?? ''));
            return Response::redirect('/shop/cart?' . http_build_query(['err' => 'اتصال به درگاه زیبال ناموفق بود (' . ($res['message'] ?? '') . ') — سفارش ' . $code . ' ثبت شده و پرداخت نشده است.']));
        }
        $payments->setTrack($payId, (string) $res['trackId']);
        return Response::redirect(\Modules\Zibal\ZibalClient::startUrl((string) $res['trackId']));
    }

    private function session(): SessionManager
    {
        return $this->container->get('session');
    }

    /** CSRF برای POSTهای عمومی فروشگاه (هوک سشن فقط /admin را پوشش می‌دهد). */
    private function csrfOk(Request $request): bool
    {
        return $this->session()->validCsrf($request->input('_csrf'));
    }

    private function page(string $view, array $data, string $title, int $status = 200): Response
    {
        $settings = $this->settings();
        $content = View::render('shop', 'front', $view, $data + [
            'currency' => $settings->get('currency', 'تومان'),
            'csrf' => $this->session()->csrfToken(),
            'categories' => $this->catalog()->categories(),
            'wishlistIds' => $this->wishlist()->ids(),
        ]);
        $tpl = $this->templates()->defaultTemplate();
        if ($tpl === null) {
            return Response::html(View::render('shop', 'front', 'layout', [
                'title' => $title,
                'content' => $content,
                'shopTitle' => $settings->get('shop_title', 'فروشگاه'),
                'shopAccent' => $settings->get('shop_accent', '#4f46e5'),
                'customer' => $this->customer(),
                'cartCount' => $this->cart()->count(),
                'cartItems' => $this->cart()->items($this->catalog()),
                'wishlistCount' => $this->wishlist()->count(),
                'categories' => $this->catalog()->categories(),
                'csrf' => $this->session()->csrfToken(),
            ]), $status);
        }
        // رندر با قالب فعال: اول توکن‌ها (بدون content) جایگزین می‌شوند تا
        // رشته‌های {{...}} داخل خودِ محتوا دست نخورده بمانند، بعد content می‌نشیند.
        $customer = $this->customer();
        $cartItems = $this->cart()->items($this->catalog());
        $cartCount = $this->cart()->count();
        $wishlistCount = $this->wishlist()->count();
        $csrf = $this->session()->csrfToken();
        $accent = $settings->get('shop_accent', '#4f46e5');
        $accent = preg_match('/^#[0-9a-fA-F]{6}$/', $accent) ? $accent : '#4f46e5';
        $tokens = [
            '{{title}}' => e($title),
            '{{shop_title}}' => e($settings->get('shop_title', 'فروشگاه')),
            '{{shop_accent}}' => e($accent),
            '{{app_version}}' => e($this->appVersion()),
            '{{cart_count}}' => (string) $cartCount,
            '{{wishlist_badge}}' => $this->wishlistBadge($wishlistCount),
            '{{account_link}}' => $this->accountLink($customer),
            '{{categories_nav}}' => $this->categoriesNav($this->catalog()->categories()),
            '{{cart_items}}' => $this->cartDrawerItems($cartItems, $csrf),
            '{{cart_total}}' => number_format(array_sum(array_map(static fn (array $ci): int => (int) $ci['line_total'], $cartItems))),
            '{{csrf}}' => e($csrf),
        ];
        $html = str_replace(array_keys($tokens), array_values($tokens), (string) $tpl['content']);
        $html = str_replace('{{content}}', $content, $html);
        return Response::html($html, $status);
    }

    public function index(Request $request): Response
    {
        $catSlug = rawurldecode((string) $request->input('cat', ''));
        $q = trim((string) $request->input('q', ''));
        $maxPrice = max(0, (int) $request->input('max_price', 0));
        $sort = (string) $request->input('sort', '');
        $sort = in_array($sort, ['cheapest', 'expensive', 'newest'], true) ? $sort : '';
        $saleOnly = $request->input('sale') === '1';
        $catalog = $this->catalog();
        $activeCat = $catSlug !== '' ? $catalog->categoryBySlug($catSlug) : null;
        $products = $catalog->productsPublished($activeCat !== null ? (int) $activeCat['id'] : 0);
        $products = array_values(array_filter($products, static function (array $p) use ($q, $maxPrice, $saleOnly): bool {
            if ($q !== '' && stripos((string) $p['title'], $q) === false && stripos((string) ($p['short_desc'] ?? ''), $q) === false) {
                return false;
            }
            if ($maxPrice > 0 && Catalog::effectivePrice($p) > $maxPrice) {
                return false;
            }
            if ($saleOnly && (int) $p['sale_price'] <= 0) {
                return false;
            }
            return true;
        }));
        if ($sort === 'cheapest') {
            usort($products, static fn (array $a, array $b): int => Catalog::effectivePrice($a) <=> Catalog::effectivePrice($b));
        } elseif ($sort === 'expensive') {
            usort($products, static fn (array $a, array $b): int => Catalog::effectivePrice($b) <=> Catalog::effectivePrice($a));
        }
        $featured = $catalog->featuredPublished();
        $catTitles = [];
        foreach ($catalog->categories() as $c) {
            $catTitles[(int) $c['id']] = (string) $c['title'];
        }
        return $this->page('index', [
            'products' => $products,
            'featured' => $featured,
            'variantsMap' => $this->variantsMapFor(array_merge($products, $featured)),
            'catTitles' => $catTitles,
            'activeCat' => $activeCat,
            'filters' => ['q' => $q, 'max_price' => $maxPrice, 'sort' => $sort, 'sale' => $saleOnly],
            'filtersActive' => $q !== '' || $maxPrice > 0 || $sort !== '' || $saleOnly || $activeCat !== null,
        ], $this->settings()->get('shop_title', 'فروشگاه'));
    }

    /** نقشه‌ی واریانت‌ها برای کارت‌ها (دکمه‌ی مشاهده سریع باید بداند محصول نوع دارد یا نه). */
    private function variantsMapFor(array $products): array
    {
        $map = [];
        foreach ($products as $p) {
            $map[(int) $p['id']] = $this->catalog()->variantsFor((int) $p['id']);
        }
        return $map;
    }

    /** جستجوی زنده: JSON محصولات منتشرشده‌ی هم‌خوان. */
    public function search(Request $request): Response
    {
        $out = [];
        foreach ($this->catalog()->searchPublished((string) $request->input('q', '')) as $p) {
            $out[] = [
                'title' => (string) $p['title'],
                'price' => Catalog::effectivePrice($p),
                'image' => Catalog::displayImage($p),
                'url' => '/shop/product/' . rawurlencode((string) $p['slug']),
            ];
        }
        return Response::json($out);
    }

    public function wishlistShow(Request $request): Response
    {
        $products = $this->wishlist()->products($this->catalog());
        $catTitles = [];
        foreach ($this->catalog()->categories() as $c) {
            $catTitles[(int) $c['id']] = (string) $c['title'];
        }
        return $this->page('wishlist', [
            'products' => $products,
            'variantsMap' => $this->variantsMapFor($products),
            'catTitles' => $catTitles,
            'wishReturn' => '/shop/wishlist',
        ], 'علاقه‌مندی‌ها');
    }

    public function wishlistToggle(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $product = $this->catalog()->product((int) $request->input('product_id', 0));
        if ($product !== null && $product['status'] === 'published') {
            $this->wishlist()->toggle((int) $product['id']);
        }
        $back = (string) $request->input('return', '/shop');
        if (!str_starts_with($back, '/shop')) {
            $back = '/shop';
        }
        return Response::redirect($back);
    }

    public function couponApply(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $subtotal = $this->cart()->subtotal($this->catalog());
        $code = trim((string) $request->input('code', ''));
        if ($this->coupons()->findValid($code, $subtotal) !== null) {
            $this->session()->set('shop_coupon', $code);
            return Response::redirect('/shop/cart?msg=' . rawurlencode('کوپن اعمال شد.'));
        }
        return Response::redirect('/shop/cart?err=' . rawurlencode('کوپن معتبر نیست یا منقضی شده است.'));
    }

    public function couponRemove(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $this->session()->set('shop_coupon', '');
        return Response::redirect('/shop/cart');
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
        $related = [];
        if ((int) $product['category_id'] > 0) {
            foreach ($catalog->productsPublished((int) $product['category_id']) as $other) {
                if ((int) $other['id'] !== (int) $product['id']) {
                    $related[] = $other;
                }
                if (count($related) >= 4) {
                    break;
                }
            }
        }
        return $this->page('product', [
            'product' => $product,
            'fields' => $fields,
            'variants' => $catalog->variantsFor((int) $product['id']),
            'attrs' => $catalog->attributesBySlug(),
            'category' => (int) $product['category_id'] > 0 ? $catalog->category((int) $product['category_id']) : null,
            'related' => $related,
            'variantsMap' => $this->variantsMapFor($related),
            'catTitles' => array_column(array_map(static fn (array $c): array => [(int) $c['id'], (string) $c['title']], $catalog->categories()), 1, 0),
            'added' => $request->input('added') !== null,
        ], (string) $product['title']);
    }

    // ---------- سبد خرید ----------

    public function cartAdd(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
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
        [$coupon, $discount] = $this->validCoupon($subtotal);
        $shipping = Cart::shippingFor($subtotal - $discount, $this->settings());
        $taxPercent = $this->settings()->getInt('tax_percent', 0);
        $tax = $taxPercent > 0 ? (int) round(($subtotal - $discount) * $taxPercent / 100) : 0;
        return $this->page('cart', [
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'shipping' => $shipping,
            'tax' => $tax,
            'taxPercent' => $taxPercent,
            'total' => $subtotal - $discount + $shipping + $tax,
            'freeOver' => $this->settings()->getInt('free_shipping_over', 0),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'سبد خرید');
    }

    public function cartUpdate(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $key = (string) $request->input('key', '');
        $this->cart()->setQty($key, (int) $request->input('qty', 0));
        return Response::redirect('/shop/cart');
    }

    public function cartRemove(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $this->cart()->remove((string) $request->input('key', ''));
        return Response::redirect('/shop/cart');
    }

    // ---------- تسویه و ثبت سفارش ----------

    public function checkoutForm(Request $request): Response
    {
        if ($this->cart()->count() === 0) {
            return Response::redirect('/shop/cart');
        }
        $old = [];
        if (($c = $this->customer()) !== null) {
            $old = ['name' => (string) $c['name'], 'phone' => (string) $c['phone'], 'address' => (string) $c['address']];
        }
        return $this->checkoutPage($old, null);
    }

    private function checkoutPage(array $old, ?string $error): Response
    {
        $items = $this->cart()->items($this->catalog());
        $subtotal = 0;
        foreach ($items as $row) {
            $subtotal += $row['line_total'];
        }
        [$coupon, $discount] = $this->validCoupon($subtotal);
        $shipping = Cart::shippingFor($subtotal - $discount, $this->settings());
        $taxPercent = $this->settings()->getInt('tax_percent', 0);
        $tax = $taxPercent > 0 ? (int) round(($subtotal - $discount) * $taxPercent / 100) : 0;
        $giftPrice = $this->settings()->getInt('gift_wrap_price', 0);
        return $this->page('checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'giftPrice' => $giftPrice,
            'deliverySlots' => self::parseLines($this->settings()->get('delivery_slots', '')),
            'paymentMethods' => $this->paymentMethods(),
            'shipping' => $shipping,
            'tax' => $tax,
            'taxPercent' => $taxPercent,
            'total' => $subtotal - $discount + $shipping + $tax,
            'old' => $old,
            'error' => $error,
            'orderNote' => $this->settings()->get('order_note', ''),
        ], 'ثبت سفارش');
    }

    public function checkoutPlace(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $catalog = $this->catalog();
        $cart = $this->cart();
        $old = [
            'name' => trim((string) $request->input('name', '')),
            'phone' => trim((string) $request->input('phone', '')),
            'email' => trim((string) $request->input('email', '')),
            'address' => trim((string) $request->input('address', '')),
            'note' => trim((string) $request->input('note', '')),
            'gift_wrap' => $request->input('gift_wrap') ? 1 : 0,
            'gift_message' => trim((string) $request->input('gift_message', '')),
            'delivery_slot' => trim((string) $request->input('delivery_slot', '')),
            'payment_method' => trim((string) $request->input('payment_method', '')),
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
        [$coupon, $discount] = $this->validCoupon($subtotal);
        $shipping = Cart::shippingFor($subtotal - $discount, $this->settings());
        $taxPercent = $this->settings()->getInt('tax_percent', 0);
        $giftFee = $old['gift_wrap'] === 1 ? $this->settings()->getInt('gift_wrap_price', 0) : 0;
        $slots = self::parseLines($this->settings()->get('delivery_slots', ''));
        $methods = $this->paymentMethods();
        $methodKeys = array_column($methods, 0);
        if ($methodKeys !== [] && !in_array($old['payment_method'], $methodKeys, true)) {
            return $this->checkoutPage($old, 'روش پرداخت را انتخاب کنید.');
        }
        $customer = $this->customer();
        $extra = [
            'customer_id' => $customer !== null ? (int) $customer['id'] : 0,
            'coupon_code' => $coupon !== null ? (string) $coupon['code'] : '',
            'discount' => $discount,
            'gift_wrap' => $old['gift_wrap'],
            'gift_fee' => $giftFee,
            'gift_message' => $old['gift_message'],
            'delivery_slot' => in_array($old['delivery_slot'], $slots, true) ? $old['delivery_slot'] : '',
            'payment_method' => in_array($old['payment_method'], $methodKeys, true) ? $old['payment_method'] : '',
        ];
        [, $code] = $this->container->get('shop.orders')->create($old, $orderItems, $shipping, $taxPercent, $extra);
        if ($coupon !== null) {
            $this->coupons()->markUsed((string) $coupon['code']);
        }
        foreach ($orderItems as $item) {
            $catalog->decreaseStock($item['product_id'], $item['variant_id'], $item['qty']);
        }
        $cart->clear();
        $this->session()->set('shop_coupon', '');
        if ($this->container->has('logger')) {
            $this->container->get('logger')->system('ثبت سفارش فروشگاه', $code, $old['phone']);
        }
        $this->notifyOrderPlaced($code, $old['phone']);
        if ($extra['payment_method'] === 'zibal') {
            $tax = $taxPercent > 0 ? (int) round(($subtotal - $discount) * $taxPercent / 100) : 0;
            $total = $subtotal - $discount + $giftFee + $shipping + $tax;
            return $this->zibalRedirect($code, (int) $total);
        }
        return Response::redirect('/shop/success/' . $code);
    }

    private function sms(): ?SmsSender
    {
        return $this->container->has('sms.sender') ? $this->container->get('sms.sender') : null;
    }

    /** پیامک ثبت سفارش؛ خرابی‌اش جریان تسویه را نمی‌شکند. */
    private function notifyOrderPlaced(string $code, string $phone): void
    {
        $sender = $this->sms();
        if ($sender === null) {
            return;
        }
        try {
            $sender->sendOrderPlaced($code, $phone);
        } catch (\Throwable) {
        }
    }

    // ---------- ناحیه کاربری فروشگاه ----------

    public function account(Request $request): Response
    {
        if ($this->customer() !== null) {
            return Response::redirect('/shop/account/orders');
        }
        return $this->page('account', [
            'error' => $request->input('err'),
            'flash' => $request->input('msg'),
            'old' => [],
            'otpPhone' => null,
        ], 'حساب من');
    }

    public function accountRegister(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if ($this->customer() !== null) {
            return Response::redirect('/shop/account/orders');
        }
        [$customer, $error] = $this->customers()->register(
            (string) $request->input('name', ''),
            (string) $request->input('phone', ''),
            (string) $request->input('password', '')
        );
        if ($error !== null) {
            return $this->page('account', [
                'error' => $error, 'flash' => null,
                'old' => ['name' => (string) $request->input('name', ''), 'phone' => (string) $request->input('phone', '')],
            ], 'حساب من');
        }
        $this->session()->set('shop_customer_id', (int) $customer['id']);
        return Response::redirect('/shop/account/orders');
    }

    public function accountLogin(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if ($this->customer() !== null) {
            return Response::redirect('/shop/account/orders');
        }
        $customer = $this->customers()->verify(
            (string) $request->input('phone', ''),
            (string) $request->input('password', '')
        );
        if ($customer === null) {
            return $this->page('account', [
                'error' => 'شماره موبایل یا رمز عبور درست نیست.', 'flash' => null,
                'old' => ['phone' => (string) $request->input('phone', '')],
            ], 'حساب من');
        }
        $this->session()->set('shop_customer_id', (int) $customer['id']);
        return Response::redirect('/shop/account/orders');
    }

    public function accountLogout(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $this->session()->set('shop_customer_id', 0);
        return Response::redirect('/shop/account');
    }

    public function accountOrders(Request $request): Response
    {
        $customer = $this->requireCustomer();
        if ($customer === null) {
            return Response::redirect('/shop/account');
        }
        return $this->page('account_orders', [
            'customer' => $customer,
            'orders' => $this->container->get('shop.orders')->byCustomer((int) $customer['id']),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'سفارش‌های من');
    }

    public function accountOrderDetail(Request $request, array $params): Response
    {
        $customer = $this->requireCustomer();
        if ($customer === null) {
            return Response::redirect('/shop/account');
        }
        $code = (string) ($params['code'] ?? '');
        $orders = $this->container->get('shop.orders');
        $order = $orders->findByCode($code);
        if ($order === null || (int) ($order['customer_id'] ?? 0) !== (int) $customer['id']) {
            return Response::redirect('/shop/account/orders');
        }
        return $this->page('account_order', [
            'customer' => $customer,
            'order' => $order,
            'items' => $orders->items((int) $order['id']),
        ], 'سفارش ' . $code);
    }

    public function accountProfile(Request $request): Response
    {
        $customer = $this->requireCustomer();
        if ($customer === null) {
            return Response::redirect('/shop/account');
        }
        return $this->page('profile', [
            'customer' => $customer,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'ویرایش حساب');
    }

    public function accountProfileSave(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $customer = $this->requireCustomer();
        if ($customer === null) {
            return Response::redirect('/shop/account');
        }
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            return $this->page('profile', [
                'customer' => $customer, 'flash' => null, 'error' => 'نام الزامی است.',
            ], 'ویرایش حساب');
        }
        $this->customers()->updateProfile((int) $customer['id'], $name, (string) $request->input('address', ''));
        return Response::redirect('/shop/account/profile?msg=' . rawurlencode('حساب به‌روز شد.'));
    }

    // ---------- ورود با کد پیامکی ----------

    public function accountOtpRequest(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if ($this->customer() !== null) {
            return Response::redirect('/shop/account/orders');
        }
        $phone = trim((string) $request->input('phone', ''));
        $customer = $this->customers()->findByPhone($phone);
        if ($customer === null) {
            return $this->page('account', [
                'error' => 'حسابی با این شماره موبایل ثبت نشده است؛ اول ثبت‌نام کنید.', 'flash' => null,
                'old' => ['phone' => $phone], 'otpPhone' => null,
            ], 'حساب من');
        }
        $sender = $this->sms();
        if ($sender === null) {
            return $this->page('account', [
                'error' => 'ارسال پیامک فعال نیست.', 'flash' => null,
                'old' => ['phone' => $phone], 'otpPhone' => null,
            ], 'حساب من');
        }
        [$ok, $error] = $sender->requestOtp($phone);
        if (!$ok) {
            return $this->page('account', [
                'error' => $error, 'flash' => null,
                'old' => ['phone' => $phone], 'otpPhone' => null,
            ], 'حساب من');
        }
        return $this->page('account', [
            'error' => null, 'flash' => 'کد تأیید پیامک شد.', 'old' => [], 'otpPhone' => $phone,
        ], 'حساب من');
    }

    public function accountOtpVerify(Request $request): Response
    {
        if (!$this->csrfOk($request)) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        if ($this->customer() !== null) {
            return Response::redirect('/shop/account/orders');
        }
        $phone = trim((string) $request->input('phone', ''));
        $code = trim((string) $request->input('code', ''));
        $customer = $this->customers()->findByPhone($phone);
        $sender = $this->sms();
        if ($customer === null || $sender === null || !$sender->verifyOtp($phone, $code)) {
            return $this->page('account', [
                'error' => 'کد درست نیست یا منقضی شده است.', 'flash' => null,
                'old' => ['phone' => $phone], 'otpPhone' => $phone,
            ], 'حساب من');
        }
        $this->session()->set('shop_customer_id', (int) $customer['id']);
        return Response::redirect('/shop/account/orders');
    }

    public function success(Request $request, array $params): Response
    {
        $code = (string) ($params['code'] ?? '');
        $order = $this->container->get('shop.orders')->findByCode($code);
        if ($order === null) {
            return Response::redirect('/shop');
        }
        $methodLabel = '';
        foreach ($this->paymentMethods() as [$key, $label]) {
            if ($key === (string) ($order['payment_method'] ?? '')) {
                $methodLabel = $label;
            }
        }
        return $this->page('success', [
            'order' => $order,
            'methodLabel' => $methodLabel,
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
