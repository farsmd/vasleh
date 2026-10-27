<?php
declare(strict_types=1);

namespace Modules\Inventory;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل انبارداری: داشبورد موجودی، کالاها، انبارها، ثبت حرکت، انتقال و کاردکس. */
final class AdminInventoryController
{
    public function __construct(private readonly Container $container) {}

    private function stock(): Stock
    {
        return $this->container->get('inventory.stock');
    }

    private function actor(): string
    {
        return (string) ($this->container->get('session')->user()['username'] ?? '');
    }

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('inventory', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title'   => $title,
            'content' => $content,
            'menu'    => $this->container->get('loader')->adminMenu(),
            'user'    => $session->user(),
            'csrf'    => $session->csrfToken(),
        ]));
    }

    private function log(string $action, string $target): void
    {
        if (!$this->container->has('logger') || !$this->container->has('session')) {
            return;
        }
        $actor = $this->actor();
        if ($actor !== '') {
            $this->container->get('logger')->user($actor, $action, $target);
        }
    }

    private function back(string $path, string $msg = '', string $err = ''): Response
    {
        $q = [];
        if ($msg !== '') {
            $q['msg'] = $msg;
        }
        if ($err !== '') {
            $q['err'] = $err;
        }
        return Response::redirect($path . ($q === [] ? '' : '?' . http_build_query($q)));
    }

    // ---------- داشبورد موجودی ----------

    public function dashboard(Request $request): Response
    {
        $stock = $this->stock();
        $warehouseId = (int) $request->input('warehouse', 0);
        return $this->page('dashboard', [
            'rows' => $stock->stockTable($warehouseId),
            'warehouses' => $stock->warehouses(),
            'warehouseId' => $warehouseId,
            'low' => $stock->lowStock(),
            'stats' => $stock->stats(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'موجودی انبار');
    }

    // ---------- کالاها ----------

    public function items(Request $request): Response
    {
        $stock = $this->stock();
        $edit = null;
        $editId = (int) $request->input('edit', 0);
        if ($editId > 0) {
            $edit = $stock->item($editId);
        }
        $shopProducts = [];
        if ($this->container->has('shop.catalog')) {
            $shopProducts = $this->container->get('shop.catalog')->productsAdmin();
        }
        return $this->page('items', [
            'items' => $stock->items(),
            'edit' => $edit,
            'totals' => array_map(fn ($i) => $stock->totalQty((int) $i['id']), $stock->items()),
            'shopProducts' => $shopProducts,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'کالاهای انبار');
    }

    public function itemSave(Request $request): Response
    {
        $stock = $this->stock();
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        $sku = trim((string) $request->input('sku', ''));
        if ($title === '') {
            return $this->back('/admin/inventory/items', '', 'عنوان کالا الزامی است.');
        }
        $bySku = $stock->itemBySku($sku);
        if ($bySku !== null && (int) $bySku['id'] !== $id) {
            return $this->back('/admin/inventory/items', '', 'این کد کالا (SKU) قبلاً ثبت شده است.');
        }
        $kind = (string) $request->input('kind', 'material');
        if (!in_array($kind, ['material', 'product', 'other'], true)) {
            $kind = 'material';
        }
        $stock->itemSave([
            'title' => $title,
            'sku' => $sku,
            'unit' => trim((string) $request->input('unit', 'عدد')) ?: 'عدد',
            'kind' => $kind,
            'min_stock' => (float) $request->input('min_stock', 0),
            'shop_product_id' => (int) $request->input('shop_product_id', 0),
            'note' => trim((string) $request->input('note', '')),
            'active' => $request->input('active') ? 1 : 0,
        ], $id);
        $this->log($id > 0 ? 'ویرایش کالای انبار' : 'ساخت کالای انبار', $title);
        return $this->back('/admin/inventory/items', 'کالا ذخیره شد.');
    }

    public function itemDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $item = $this->stock()->item($id);
        if ($item !== null) {
            $this->stock()->itemDelete($id);
            $this->log('حذف کالای انبار', (string) $item['title']);
        }
        return $this->back('/admin/inventory/items', 'کالا و سوابقش حذف شد.');
    }

    // ---------- انبارها ----------

    public function warehouses(Request $request): Response
    {
        $stock = $this->stock();
        $edit = null;
        $editId = (int) $request->input('edit', 0);
        if ($editId > 0) {
            $edit = $stock->warehouse($editId);
        }
        return $this->page('warehouses', [
            'warehouses' => $stock->warehouses(),
            'edit' => $edit,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'انبارها');
    }

    public function warehouseSave(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            return $this->back('/admin/inventory/warehouses', '', 'عنوان انبار الزامی است.');
        }
        $this->stock()->warehouseSave([
            'title' => $title,
            'location' => trim((string) $request->input('location', '')),
            'is_default' => $request->input('is_default') ? 1 : 0,
            'active' => $request->input('active') ? 1 : 0,
            'sort' => (int) $request->input('sort', 0),
        ], $id);
        $this->log($id > 0 ? 'ویرایش انبار' : 'ساخت انبار', $title);
        return $this->back('/admin/inventory/warehouses', 'انبار ذخیره شد.');
    }

    public function warehouseDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $w = $this->stock()->warehouse($id);
        if ($w === null) {
            return $this->back('/admin/inventory/warehouses');
        }
        if (!$this->stock()->warehouseDelete($id)) {
            return $this->back('/admin/inventory/warehouses', '', 'این انبار هنوز موجودی دارد و حذف نمی‌شود.');
        }
        $this->log('حذف انبار', (string) $w['title']);
        return $this->back('/admin/inventory/warehouses', 'انبار حذف شد.');
    }

    // ---------- حرکت‌ها ----------

    public function moveForm(Request $request): Response
    {
        $stock = $this->stock();
        return $this->page('move', [
            'items' => $stock->items(true),
            'warehouses' => $stock->warehouses(true),
            'defaultWarehouse' => $stock->defaultWarehouse(),
            'recent' => $stock->movements(0, 0, 30),
            'types' => Stock::TYPES,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'ثبت گردش انبار');
    }

    public function moveSave(Request $request): Response
    {
        $type = (string) $request->input('type', 'in');
        $result = $this->stock()->move(
            $type,
            (int) $request->input('item_id', 0),
            (int) $request->input('warehouse_id', 0),
            (float) $request->input('qty', 0),
            trim((string) $request->input('ref', '')),
            trim((string) $request->input('note', '')),
            $this->actor(),
            max(0.0, (float) $request->input('unit_price', 0))
        );
        if (!$result['ok']) {
            return $this->back('/admin/inventory/move', '', (string) $result['error']);
        }
        $this->log('گردش انبار (' . (Stock::TYPES[$type] ?? $type) . ')', 'موجودی تازه: ' . Stock::fmt($result['balance']));
        return $this->back('/admin/inventory/move', 'ثبت شد. موجودی فعلی: ' . Stock::fmt($result['balance']));
    }

    public function transferSave(Request $request): Response
    {
        $result = $this->stock()->transfer(
            (int) $request->input('item_id', 0),
            (int) $request->input('from_id', 0),
            (int) $request->input('to_id', 0),
            (float) $request->input('qty', 0),
            trim((string) $request->input('ref', '')),
            trim((string) $request->input('note', '')),
            $this->actor()
        );
        if (!$result['ok']) {
            return $this->back('/admin/inventory/move', '', (string) $result['error']);
        }
        $this->log('انتقال بین انبارها', '');
        return $this->back('/admin/inventory/move', 'انتقال انجام شد.');
    }

    // ---------- کاردکس ----------

    public function kardex(Request $request): Response
    {
        $stock = $this->stock();
        $itemId = (int) $request->input('item', 0);
        $warehouseId = (int) $request->input('warehouse', 0);
        return $this->page('kardex', [
            'items' => $stock->items(),
            'warehouses' => $stock->warehouses(),
            'itemId' => $itemId,
            'warehouseId' => $warehouseId,
            'item' => $itemId > 0 ? $stock->item($itemId) : null,
            'rows' => $stock->movements($itemId, $warehouseId, 300),
            'types' => Stock::TYPES,
        ], 'کاردکس انبار');
    }
}
