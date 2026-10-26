<?php
declare(strict_types=1);

namespace Modules\Admin;

use Core\Container;
use Core\Request;
use Core\Response;

/**
 * داده نمونه برای تست واقعی سیستم — فقط مالک.
 *
 * بارگذاری، فقط ردیف‌های تازه با پسوند «(نمونه)» می‌سازد و شناسه همه آن‌ها
 * را در storage/demo-data.json ثبت می‌کند؛ پاک‌سازی دقیقاً همان ردیف‌ها را
 * حذف می‌کند و به داده واقعی دست نمی‌زند. اگر وسط تست، روی ردیف نمونه
 * داده واقعی سوار شده باشد (مثلاً موجودی واقعی به انبار نمونه رفته باشد)،
 * آن انبار/حساب باقی می‌ماند و در پیام پاک‌سازی گفته می‌شود.
 */
final class DemoController
{
    private const REGISTRY = '/storage/demo-data.json';

    public function __construct(private readonly Container $container) {}

    private function admin(): AdminController
    {
        return new AdminController($this->container);
    }

    private function csrf(): string
    {
        return $this->container->has('session') ? $this->container->get('session')->csrfToken() : '';
    }

    private function ownerOnly(): ?Response
    {
        $user = $this->container->has('session') ? $this->container->get('session')->user() : null;
        if (($user['role'] ?? '') !== 'owner') {
            return Response::forbidden('این کار فقط برای مالک است');
        }
        return null;
    }

    private function registryPath(): string
    {
        return (string) $this->container->get('base_path') . self::REGISTRY;
    }

    /** رجیستری بارگذاری‌شده یا null. */
    private function registry(): ?array
    {
        $file = $this->registryPath();
        if (!is_file($file)) {
            return null;
        }
        $data = json_decode((string) file_get_contents($file), true);
        return is_array($data) ? $data : null;
    }

    private function writeRegistry(array $reg): void
    {
        $file = $this->registryPath();
        $tmp = $file . '.tmp' . bin2hex(random_bytes(4));
        file_put_contents($tmp, json_encode($reg, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        rename($tmp, $file);
    }

    public function index(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        return $this->admin()->page('demo', [
            'reg' => $this->registry(),
            'csrf' => $this->csrf(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'داده نمونه');
    }

    public function load(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        if ($this->registry() !== null) {
            return Response::redirect('/admin/demo?err=' . urlencode('داده نمونه قبلاً بارگذاری شده است؛ اول پاک‌سازی کنید.'));
        }
        foreach (['shop.catalog' => 'فروشگاه', 'shop.orders' => 'فروشگاه', 'inventory.stock' => 'انبار', 'production.manager' => 'تولید', 'accounting.ledger' => 'حسابداری'] as $service => $label) {
            if (!$this->container->has($service)) {
                return Response::redirect('/admin/demo?err=' . urlencode("ماژول $label فعال نیست؛ اول آن را فعال کنید."));
            }
        }
        $reg = ['loaded_at' => date('Y-m-d H:i:s')];
        try {
            $this->buildDemo($reg);
            $this->writeRegistry($reg);
        } catch (\Throwable $e) {
            $this->cleanRegistered($reg);
            return Response::redirect('/admin/demo?err=' . urlencode('بارگذاری ناتمام ماند و ردیف‌های ساخته‌شده برگشت خورد: ' . $e->getMessage()));
        }
        return Response::redirect('/admin/demo?msg=' . urlencode('داده نمونه بارگذاری شد. برای حذف همه آن‌ها، «پاک‌سازی داده نمونه» را بزنید.'));
    }

    public function clean(Request $request): Response
    {
        if ($deny = $this->ownerOnly()) {
            return $deny;
        }
        $reg = $this->registry();
        if ($reg === null) {
            return Response::redirect('/admin/demo?err=' . urlencode('داده نمونه‌ای بارگذاری نشده است.'));
        }
        $leftovers = $this->cleanRegistered($reg);
        if ($leftovers !== []) {
            return Response::redirect('/admin/demo?err=' . urlencode('پاک‌سازی انجام شد ولی این‌ها باقی ماندند: ' . implode('، ', $leftovers)));
        }
        @unlink($this->registryPath());
        return Response::redirect('/admin/demo?msg=' . urlencode('داده نمونه کاملاً پاک شد.'));
    }

    /** ساخت سناریوی نمونه؛ شناسه‌ها در $reg جمع می‌شوند. */
    private function buildDemo(array &$reg): void
    {
        $stock = $this->container->get('inventory.stock');
        $catalog = $this->container->get('shop.catalog');
        $orders = $this->container->get('shop.orders');
        $prod = $this->container->get('production.manager');
        $ledger = $this->container->get('accounting.ledger');
        $today = date('Y-m-d');

        // انبار و کالاها
        $wh = $stock->warehouseSave(['title' => 'انبار مرکزی (نمونه)', 'location' => 'کارگاه', 'is_default' => 0, 'active' => 1, 'sort' => 0]);
        $reg['warehouses'] = [$wh];
        $materials = [
            ['پروفیل آلومینیوم (نمونه)', 'DEMO-PROFILE', 'متر', 100.0],
            ['ریسه LED (نمونه)', 'DEMO-STRIP', 'متر', 200.0],
            ['درایور ۲۴ وات (نمونه)', 'DEMO-DRIVER', 'عدد', 40.0],
            ['درپوش (نمونه)', 'DEMO-CAP', 'عدد', 200.0],
        ];
        $itemIds = [];
        foreach ($materials as [$title, $sku, $unit, $qty]) {
            $id = $stock->itemSave(['title' => $title, 'sku' => $sku, 'unit' => $unit, 'kind' => 'material', 'min_stock' => 0, 'shop_product_id' => 0, 'note' => 'کالای نمونه', 'active' => 1]);
            $itemIds[$sku] = $id;
            $stock->move('in', $id, $wh, $qty, 'DEMO-OPEN', 'موجودی اول دوره نمونه', 'demo');
        }
        $outId = $stock->itemSave(['title' => 'چراغ خطی ۱ متری (نمونه)', 'sku' => 'DEMO-LIGHT', 'unit' => 'عدد', 'kind' => 'product', 'min_stock' => 0, 'shop_product_id' => 0, 'note' => 'خروجی تولید نمونه', 'active' => 1]);
        $itemIds['DEMO-LIGHT'] = $outId;
        $reg['inv_items'] = array_values($itemIds);

        // فرمول و دستور تولید (برنامه‌ریزی‌شده؛ شروع و تکمیلش با خودتان)
        $recipeId = $prod->recipeSave(
            ['title' => 'چراغ خطی ۱ متری (نمونه)', 'output_item_id' => $outId, 'output_qty' => 1, 'note' => 'فرمول نمونه', 'active' => 1],
            [
                ['material_item_id' => $itemIds['DEMO-PROFILE'], 'qty' => 1],
                ['material_item_id' => $itemIds['DEMO-STRIP'], 'qty' => 1],
                ['material_item_id' => $itemIds['DEMO-DRIVER'], 'qty' => 1],
                ['material_item_id' => $itemIds['DEMO-CAP'], 'qty' => 2],
            ]
        );
        $reg['recipes'] = [$recipeId];
        $order = $prod->createOrder($recipeId, 10, $wh, 'دستور نمونه برای تست', 'demo');
        if (empty($order['ok'])) {
            throw new \RuntimeException($order['error'] ?? 'ساخت دستور تولید ناموفق بود.');
        }
        $reg['prod_orders'] = [(int) $order['id']];

        // فروشگاه
        $catId = $catalog->categorySave(['parent_id' => 0, 'title' => 'روشنایی (نمونه)', 'slug' => 'demo-lighting', 'description' => 'دسته نمونه', 'sort' => 0]);
        $reg['shop_categories'] = [$catId];
        $products = [
            ['چراغ خطی ۱ متری (نمونه)', 'demo-linear-light', 'DEMO-LIGHT', 2500000, 20],
            ['لامپ LED ۱۲ وات (نمونه)', 'demo-led-bulb', 'DEMO-BULB', 450000, 100],
            ['درایور ۲۴ وات (نمونه)', 'demo-driver', 'DEMO-DRIVER', 650000, 30],
        ];
        $prodIds = [];
        foreach ($products as [$title, $slug, $sku, $price, $qty]) {
            $prodIds[$sku] = $catalog->productSave(['category_id' => $catId, 'title' => $title, 'slug' => $slug, 'sku' => $sku, 'short_desc' => 'محصول نمونه', 'description' => '<p>محصول نمونه برای تست واقعی سیستم.</p>', 'price' => $price, 'sale_price' => 0, 'stock' => $qty, 'track_stock' => 1, 'status' => 'published', 'image' => '', 'sort' => 0]);
        }
        $reg['shop_products'] = array_values($prodIds);
        // اتصال خروجی تولید به محصول فروشگاهی (تکمیل تولید، موجودی فروشگاه را هم بالا می‌برد)
        $stock->itemSave(['title' => 'چراغ خطی ۱ متری (نمونه)', 'sku' => 'DEMO-LIGHT', 'unit' => 'عدد', 'kind' => 'product', 'min_stock' => 0, 'shop_product_id' => $prodIds['DEMO-LIGHT'], 'note' => 'خروجی تولید نمونه', 'active' => 1], $outId);

        // سفارش‌ها (مثل تسویه واقعی، موجودی فروشگاه کم می‌شود)
        [$o1] = $orders->create(
            ['name' => 'مشتری نمونه', 'phone' => '09120000000', 'note' => 'سفارش نمونه — با پاک‌سازی داده نمونه حذف می‌شود'],
            [
                ['product_id' => $prodIds['DEMO-LIGHT'], 'title' => 'چراغ خطی ۱ متری (نمونه)', 'unit_price' => 2500000, 'qty' => 2],
                ['product_id' => $prodIds['DEMO-BULB'], 'title' => 'لامپ LED ۱۲ وات (نمونه)', 'unit_price' => 450000, 'qty' => 5],
            ],
            0, 0
        );
        $catalog->decreaseStock($prodIds['DEMO-LIGHT'], 0, 2);
        $catalog->decreaseStock($prodIds['DEMO-BULB'], 0, 5);
        [$o2] = $orders->create(
            ['name' => 'مشتری نمونه ۲', 'phone' => '09120000001', 'note' => 'سفارش نمونه پرداخت‌شده — برای تست ایمپورت به حسابداری'],
            [['product_id' => $prodIds['DEMO-DRIVER'], 'title' => 'درایور ۲۴ وات (نمونه)', 'unit_price' => 650000, 'qty' => 1]],
            0, 0
        );
        $catalog->decreaseStock($prodIds['DEMO-DRIVER'], 0, 1);
        $orders->setPayment($o2, 'paid');
        $orders->setStatus($o2, 'processing');
        $reg['shop_orders'] = [$o1, $o2];

        // حسابداری
        $cashId = $ledger->accountSave(['title' => 'صندوق نمونه', 'type' => 'cash', 'opening_balance' => 5000000, 'note' => 'حساب نمونه', 'active' => 1]);
        $bankId = $ledger->accountSave(['title' => 'بانک نمونه', 'type' => 'bank', 'opening_balance' => 20000000, 'note' => 'حساب نمونه', 'active' => 1]);
        $reg['acc_accounts'] = [$cashId, $bankId];
        $catIncome = $this->findCategory($ledger, 'فروش', 'income');
        $catExpense = $this->findCategory($ledger, 'خرید مواد', 'expense');
        $t1 = $ledger->transactionSave(['account_id' => $cashId, 'category_id' => $catIncome, 'kind' => 'income', 'amount' => 3200000, 'date' => $today, 'ref' => 'DEMO-SALE-1', 'party' => 'مشتری نمونه', 'description' => 'فروش نمونه', 'actor' => 'demo']);
        $t2 = $ledger->transactionSave(['account_id' => $cashId, 'category_id' => $catExpense, 'kind' => 'expense', 'amount' => 1500000, 'date' => $today, 'ref' => 'DEMO-BUY-1', 'party' => 'تأمین‌کننده نمونه', 'description' => 'خرید مواد نمونه', 'actor' => 'demo']);
        $reg['acc_transactions'] = [$t1, $t2];
        $tr = $ledger->transfer($cashId, $bankId, 1000000, $today, 'انتقال نمونه', 'demo');
        if (empty($tr['ok'])) {
            throw new \RuntimeException($tr['error'] ?? 'انتقال نمونه ناموفق بود.');
        }
        $db = $this->container->get('db')->pdo();
        $stmt = $db->prepare('SELECT id FROM acc_transfers WHERE from_account = :f AND to_account = :t AND amount = :a AND note = :n ORDER BY id DESC LIMIT 1');
        $stmt->execute(['f' => $cashId, 't' => $bankId, 'a' => 1000000, 'n' => 'انتقال نمونه']);
        $transferId = (int) $stmt->fetchColumn();
        $reg['acc_transfers'] = $transferId > 0 ? [$transferId] : [];
    }

    private function findCategory($ledger, string $title, string $kind): int
    {
        foreach ($ledger->categories($kind) as $c) {
            if ($c['title'] === $title) {
                return (int) $c['id'];
            }
        }
        return $ledger->categorySave(['title' => $title, 'kind' => $kind, 'sort' => 0]);
    }

    /**
     * حذف ردیف‌های رجیستری (ترتیب معکوس وابستگی). موارد باقی‌مانده را برمی‌گرداند.
     * حذف‌ها تحمل‌پذیرند: ردیفی که دستی حذف شده باشد، خطا نمی‌دهد.
     */
    private function cleanRegistered(array $reg): array
    {
        $leftovers = [];
        $orders = $this->container->has('shop.orders') ? $this->container->get('shop.orders') : null;
        $catalog = $this->container->has('shop.catalog') ? $this->container->get('shop.catalog') : null;
        $prod = $this->container->has('production.manager') ? $this->container->get('production.manager') : null;
        $stock = $this->container->has('inventory.stock') ? $this->container->get('inventory.stock') : null;
        $ledger = $this->container->has('accounting.ledger') ? $this->container->get('accounting.ledger') : null;
        $db = $this->container->has('db') ? $this->container->get('db')->pdo() : null;

        if ($orders !== null) {
            foreach ($reg['shop_orders'] ?? [] as $id) {
                $orders->delete((int) $id);
            }
        }
        if ($db !== null) {
            foreach ($reg['prod_orders'] ?? [] as $id) {
                $db->prepare('DELETE FROM prod_order_lines WHERE order_id = :id')->execute(['id' => (int) $id]);
                $db->prepare('DELETE FROM prod_orders WHERE id = :id')->execute(['id' => (int) $id]);
            }
        }
        if ($prod !== null) {
            foreach ($reg['recipes'] ?? [] as $id) {
                $prod->recipeDelete((int) $id);
            }
        }
        if ($catalog !== null) {
            foreach ($reg['shop_products'] ?? [] as $id) {
                $catalog->productDelete((int) $id);
            }
            foreach ($reg['shop_categories'] ?? [] as $id) {
                $catalog->categoryDelete((int) $id);
            }
        }
        if ($stock !== null) {
            foreach ($reg['inv_items'] ?? [] as $id) {
                $stock->itemDelete((int) $id);
            }
            foreach ($reg['warehouses'] ?? [] as $id) {
                if (!$stock->warehouseDelete((int) $id)) {
                    $leftovers[] = 'انبار نمونه (موجودی واقعی دارد)';
                }
            }
        }
        if ($ledger !== null) {
            foreach ($reg['acc_transactions'] ?? [] as $id) {
                $ledger->transactionDelete((int) $id);
            }
            foreach ($reg['acc_transfers'] ?? [] as $id) {
                $ledger->transferDelete((int) $id);
            }
            foreach ($reg['acc_accounts'] ?? [] as $id) {
                if (!$ledger->accountDelete((int) $id)) {
                    $leftovers[] = 'حساب نمونه (سند دارد)';
                }
            }
        }
        return $leftovers;
    }
}
