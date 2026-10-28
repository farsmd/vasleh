<?php
declare(strict_types=1);

namespace Modules\Telegram;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;
use Modules\Shop\Cart;
use Modules\Shop\Catalog;
use Modules\Shop\ShopSettings;

/**
 * سمت عمومی تلگرام: وبهوک ربات + مینی‌اپ فروشگاه.
 * وبهوک هیچ‌وقت فروشگاه را نمی‌شکند (همه‌چیز try/catch + فقط لاگ).
 * سفارش مینی‌اپ دقیقاً از همان مسیر ثبت سفارش فروشگاه می‌گذرد تا در
 * پنل سفارش‌ها دیده شود و همان پیامک ثبت سفارش را بگیرد.
 */
final class TgController
{
    /** آدرس مینی‌اپ روی هاست محصول (دکمهٔ ربات همین را باز می‌کند). */
    public const MINIAPP_URL = 'https://atroweb.ir/tgapp';

    public function __construct(private readonly Container $container) {}

    private function settings(): TelegramSettings
    {
        return $this->container->get('telegram.settings');
    }

    // ---------- وبهوک ربات ----------

    public function webhook(Request $request): Response
    {
        $settings = $this->settings();
        try {
            // راز وبهوک: فقط وقتی رازی ثبت شده، هدر باید با آن برابر باشد.
            $stored = $settings->webhookSecret();
            if ($stored !== '') {
                $header = (string) ($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '');
                if (!hash_equals($stored, $header)) {
                    return Response::json(['ok' => false, 'error' => 'forbidden'], 403);
                }
            }
            $raw = (string) file_get_contents('php://input');
            $update = json_decode($raw, true);
            if (!is_array($update) || !isset($update['message']['chat']['id'])) {
                return Response::json(['ok' => true]);
            }
            $token = $settings->token();
            if ($token === '') {
                return Response::json(['ok' => true]);
            }
            $chatId = $update['message']['chat']['id'];
            $text = trim((string) ($update['message']['text'] ?? ''));
            $client = TelegramClient::fromEnv($token);
            if ($text === '/start' || str_starts_with($text, '/start ')) {
                $client->sendMessage($chatId, $settings->welcomeText(), [
                    'inline_keyboard' => [[
                        ['text' => 'باز کردن فروشگاه', 'web_app' => ['url' => self::MINIAPP_URL]],
                    ]],
                ]);
            } elseif ($text !== '') {
                $client->sendMessage($chatId, 'برای خرید از فروشگاه، دکمه «باز کردن فروشگاه» را بزنید.');
            }
        } catch (\Throwable $e) {
            // وبهوک هرگز نباید خطا را به تلگرام برگرداند (حلقهٔ تلاش مجدد)؛ فقط لاگ.
            error_log('[telegram] webhook: ' . $e->getMessage());
        }
        return Response::json(['ok' => true]);
    }

    // ---------- مینی‌اپ ----------

    /** روش‌های پرداخت فروشگاه: خطوط «کلید: برچسب» + زیبال وقتی آماده است. */
    private function paymentMethods(): array
    {
        if (!$this->container->has('shop.settings')) {
            return [];
        }
        /** @var ShopSettings $shopSettings */
        $shopSettings = $this->container->get('shop.settings');
        $methods = [];
        foreach (preg_split('/\r\n|\r|\n/', $shopSettings->get('payment_methods', '')) ?: [] as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }
            $parts = explode(':', $line, 2);
            $key = trim($parts[0]);
            $label = trim($parts[1] ?? $parts[0]);
            if ($key !== '' && $label !== '') {
                $methods[] = [$key, $label];
            }
        }
        if ($this->container->has('zibal.payments') && $this->container->get('zibal.payments')->ready()) {
            $methods[] = ['zibal', 'پرداخت اینترنتی (زیبال)'];
        }
        return $methods;
    }

    public function miniapp(Request $request): Response
    {
        if (!$this->container->has('shop.catalog') || !$this->container->has('shop.settings')) {
            return Response::html('<h1>فروشگاه در دسترس نیست</h1>', 503);
        }
        /** @var Catalog $catalog */
        $catalog = $this->container->get('shop.catalog');
        /** @var ShopSettings $shopSettings */
        $shopSettings = $this->container->get('shop.settings');
        $q = trim((string) $request->input('q', ''));
        $rows = $q !== '' ? $catalog->searchPublished($q) : $catalog->productsPublished();
        $products = [];
        foreach ($rows as $row) {
            $products[] = [
                'id' => (int) $row['id'],
                'title' => (string) $row['title'],
                'short_desc' => (string) ($row['short_desc'] ?? ''),
                'price' => Catalog::effectivePrice($row),
                'orig_price' => (int) ($row['price'] ?? 0),
                'sale_price' => (int) ($row['sale_price'] ?? 0),
                'image' => Catalog::displayImage($row),
            ];
        }
        return Response::html(View::render('telegram', 'front', 'tgapp', [
            'appName' => 'وصله',
            'shopTitle' => $shopSettings->get('shop_title', 'فروشگاه'),
            'currency' => $shopSettings->get('currency', 'تومان'),
            'products' => $products,
            'q' => $q,
            'paymentMethods' => $this->paymentMethods(),
        ]));
    }

    /** ثبت سفارش از مینی‌اپ؛ پاسخ همیشه JSON است تا جاوااسکریپت مینی‌اپ آن را نشان دهد. */
    public function order(Request $request): Response
    {
        if (!$this->container->has('shop.orders') || !$this->container->has('shop.catalog') || !$this->container->has('shop.settings')) {
            return Response::json(['ok' => false, 'error' => 'فروشگاه در دسترس نیست.'], 503);
        }
        // ورودی فرم (urlencoded) یا JSON؛ هر دو پذیرفته می‌شوند.
        $jsonBody = [];
        $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode((string) file_get_contents('php://input'), true);
            $jsonBody = is_array($decoded) ? $decoded : [];
        }
        $in = static function (string $key) use ($request, $jsonBody): string {
            $v = $request->input($key, $jsonBody[$key] ?? '');
            return is_scalar($v) ? trim((string) $v) : '';
        };
        $name = $in('name');
        $mobile = $in('mobile');
        $address = $in('address');
        $method = $in('payment_method');
        $initData = $in('init_data');

        // احراز تلگرام: وقتی توکن ربات ثبت شده، امضای initData باید معتبر باشد.
        // (وقتی هنوز رباتی ثبت نشده، مینی‌اپ در حالت راه‌اندازی است و مثل
        // تسویهٔ مهمان وب عمل می‌کند؛ در محصول با توکن ثبت‌شده، سخت‌گیرانه است.)
        $telegramUserId = null;
        $token = $this->settings()->token();
        if ($token !== '') {
            $auth = TelegramAuth::validateInitData($initData, $token);
            if (!$auth['ok']) {
                return Response::json(['ok' => false, 'error' => 'مینی‌اپ باید از داخل تلگرام باز شود.'], 403);
            }
            $telegramUserId = is_array($auth['user']) ? (int) ($auth['user']['id'] ?? 0) : null;
        }

        if ($name === '' || $mobile === '') {
            return Response::json(['ok' => false, 'error' => 'نام و شماره موبایل الزامی است.'], 400);
        }
        $itemsRaw = json_decode($in('items'), true);
        if (!is_array($itemsRaw) || $itemsRaw === []) {
            return Response::json(['ok' => false, 'error' => 'سبد خرید خالی است.'], 400);
        }
        /** @var Catalog $catalog */
        $catalog = $this->container->get('shop.catalog');
        /** @var ShopSettings $shopSettings */
        $shopSettings = $this->container->get('shop.settings');
        $orderItems = [];
        $subtotal = 0;
        foreach ($itemsRaw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $productId = (int) ($item['id'] ?? 0);
            $qty = max(1, min(99, (int) ($item['qty'] ?? 0)));
            $product = $productId > 0 ? $catalog->product($productId) : null;
            if ($product === null || (string) ($product['status'] ?? '') !== 'published') {
                return Response::json(['ok' => false, 'error' => 'یک کالا در سبد یافت نشد یا منتشر نیست.'], 400);
            }
            if ((int) ($product['track_stock'] ?? 0) === 1 && $qty > (int) ($product['stock'] ?? 0)) {
                return Response::json(['ok' => false, 'error' => 'موجودی «' . (string) $product['title'] . '» کافی نیست.'], 400);
            }
            $price = Catalog::effectivePrice($product);
            $subtotal += $price * $qty;
            $orderItems[] = [
                'product_id' => $productId,
                'variant_id' => 0,
                'title' => (string) $product['title'],
                'options_text' => '',
                'unit_price' => $price,
                'qty' => $qty,
                'cost_price' => (int) ($product['cost_price'] ?? 0),
            ];
        }
        if ($orderItems === []) {
            return Response::json(['ok' => false, 'error' => 'سبد خرید خالی است.'], 400);
        }
        $methods = $this->paymentMethods();
        $methodKeys = array_column($methods, 0);
        if ($methodKeys !== [] && !in_array($method, $methodKeys, true)) {
            return Response::json(['ok' => false, 'error' => 'روش پرداخت را انتخاب کنید.'], 400);
        }
        $shipping = Cart::shippingFor($subtotal, $shopSettings);
        $taxPercent = $shopSettings->getInt('tax_percent', 0);
        $note = 'سفارش از مینی‌اپ تلگرام';
        if ($telegramUserId !== null && $telegramUserId > 0) {
            $note .= ' (کاربر تلگرام: ' . $telegramUserId . ')';
        }
        [, $code] = $this->container->get('shop.orders')->create(
            ['name' => $name, 'phone' => $mobile, 'email' => '', 'address' => $address, 'note' => $note],
            $orderItems,
            $shipping,
            $taxPercent,
            ['payment_method' => in_array($method, $methodKeys, true) ? $method : '', 'customer_id' => 0],
        );
        foreach ($orderItems as $item) {
            $catalog->decreaseStock($item['product_id'], $item['variant_id'], $item['qty']);
        }
        if ($this->container->has('logger')) {
            $this->container->get('logger')->system('ثبت سفارش مینی‌اپ تلگرام', $code, $mobile);
        }
        // همان پیامک ثبت سفارشِ سفارش‌های وب؛ خرابی‌اش سفارش را نمی‌شکند.
        if ($this->container->has('sms.sender')) {
            try {
                $this->container->get('sms.sender')->sendOrderPlaced($code, $mobile);
            } catch (\Throwable) {
            }
        }
        // زیبال: مثل تسویهٔ وب، پرداخت معلق ساخته و لینک درگاه برگردانده می‌شود؛
        // برگشت از درگاه همان /zibal/callback وب است.
        if ($method === 'zibal' && $this->container->has('zibal.payments') && $this->container->get('zibal.payments')->ready()) {
            $tax = $taxPercent > 0 ? (int) round($subtotal * $taxPercent / 100) : 0;
            $total = $subtotal + $shipping + $tax;
            $amountRial = $total * 10;
            if ($amountRial < 1000) {
                return Response::json(['ok' => true, 'code' => $code, 'payment_error' => 'مبلغ سفارش برای پرداخت اینترنتی کافی نیست. سفارش ثبت شده و پرداخت نشده است.']);
            }
            $payments = $this->container->get('zibal.payments');
            $client = $this->container->get('zibal.client');
            $payId = $payments->createPending($code, $amountRial);
            $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $local = $host === 'localhost' || str_starts_with($host, '127.0.0.1') || str_starts_with($host, '192.168.') || str_starts_with($host, '10.');
            $scheme = (!$local && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) || str_starts_with($host, 'atroweb.ir') ? 'https' : 'http';
            $callback = $scheme . '://' . $host . '/zibal/callback';
            $res = $client->request($payments->merchant(), $amountRial, $callback, 'پرداخت سفارش ' . $code, $code);
            if (!($res['ok'] ?? false)) {
                $payments->markFailed($payId, 'failed', (string) ($res['message'] ?? ''));
                return Response::json(['ok' => true, 'code' => $code, 'payment_error' => 'اتصال به درگاه زیبال ناموفق بود. سفارش ثبت شده و پرداخت نشده است.']);
            }
            $payments->setTrack($payId, (string) $res['trackId']);
            return Response::json(['ok' => true, 'code' => $code, 'payment_url' => \Modules\Zibal\ZibalClient::startUrl((string) $res['trackId'])]);
        }
        return Response::json(['ok' => true, 'code' => $code]);
    }
}
