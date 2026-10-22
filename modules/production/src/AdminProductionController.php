<?php
declare(strict_types=1);

namespace Modules\Production;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;
use Modules\Inventory\Stock;

/** پنل ساخت و تولید: فرمول‌ها (BOM) و دستورهای تولید. */
final class AdminProductionController
{
    public function __construct(private readonly Container $container) {}

    private function prod(): Production
    {
        return $this->container->get('production.manager');
    }

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
        $content = View::render('production', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
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

    // ---------- فرمول‌ها ----------

    public function recipes(Request $request): Response
    {
        return $this->page('recipes', [
            'recipes' => $this->prod()->recipes(),
            'stats' => $this->prod()->stats(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'فرمول‌های ساخت');
    }

    public function recipeCreateForm(Request $request): Response
    {
        return $this->recipeForm(null, [], '/admin/production/recipes/create', 'فرمول تازه');
    }

    public function recipeEditForm(Request $request, array $params): Response
    {
        $recipe = $this->prod()->recipe((int) ($params['id'] ?? 0));
        if ($recipe === null) {
            return $this->back('/admin/production/recipes', '', 'فرمول یافت نشد.');
        }
        return $this->recipeForm($recipe, $this->prod()->recipeLines((int) $recipe['id']), '/admin/production/recipes/edit/' . $recipe['id'], 'ویرایش فرمول');
    }

    private function recipeForm(?array $recipe, array $lines, string $action, string $title, ?string $error = null): Response
    {
        return $this->page('recipe_form', [
            'recipe' => $recipe ?? ['title' => '', 'output_item_id' => 0, 'output_qty' => 1, 'note' => '', 'active' => 1],
            'lines' => $lines,
            'action' => $action,
            'items' => $this->stock()->items(),
            'error' => $error,
        ], $title);
    }

    private function readRecipeForm(Request $request): array
    {
        $lines = [];
        $rows = $request->input('line', []);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (is_array($row) && (int) ($row['material_item_id'] ?? 0) > 0 && (float) ($row['qty'] ?? 0) > 0) {
                    $lines[] = ['material_item_id' => (int) $row['material_item_id'], 'qty' => (float) $row['qty']];
                }
            }
        }
        $data = [
            'title' => trim((string) $request->input('title', '')),
            'output_item_id' => (int) $request->input('output_item_id', 0),
            'output_qty' => (float) $request->input('output_qty', 1),
            'note' => trim((string) $request->input('note', '')),
            'active' => $request->input('active') ? 1 : 0,
        ];
        $error = null;
        if ($data['title'] === '') {
            $error = 'عنوان فرمول الزامی است.';
        } elseif ($data['output_item_id'] < 1 || $this->stock()->item($data['output_item_id']) === null) {
            $error = 'کالای خروجی (محصول ساخته‌شده) را انتخاب کنید.';
        } elseif ($data['output_qty'] <= 0) {
            $error = 'مقدار خروجی هر بچ باید بزرگ‌تر از صفر باشد.';
        } elseif ($lines === []) {
            $error = 'دست‌کم یک ماده اولیه لازم است.';
        }
        return [$data, $lines, $error];
    }

    public function recipeCreate(Request $request): Response
    {
        [$data, $lines, $error] = $this->readRecipeForm($request);
        if ($error !== null) {
            return $this->recipeForm($data, $lines, '/admin/production/recipes/create', 'فرمول تازه', $error);
        }
        $this->prod()->recipeSave($data, $lines);
        $this->log('ساخت فرمول تولید', $data['title']);
        return $this->back('/admin/production/recipes', 'فرمول ساخته شد.');
    }

    public function recipeUpdate(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        if ($this->prod()->recipe($id) === null) {
            return $this->back('/admin/production/recipes', '', 'فرمول یافت نشد.');
        }
        [$data, $lines, $error] = $this->readRecipeForm($request);
        if ($error !== null) {
            $data['id'] = $id;
            return $this->recipeForm($data, $lines, '/admin/production/recipes/edit/' . $id, 'ویرایش فرمول', $error);
        }
        $this->prod()->recipeSave($data, $lines, $id);
        $this->log('ویرایش فرمول تولید', $data['title']);
        return $this->back('/admin/production/recipes', 'فرمول به‌روز شد.');
    }

    public function recipeDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $recipe = $this->prod()->recipe($id);
        if ($recipe !== null) {
            $this->prod()->recipeDelete($id);
            $this->log('حذف فرمول تولید', (string) $recipe['title']);
        }
        return $this->back('/admin/production/recipes', 'فرمول حذف شد.');
    }

    // ---------- دستورهای تولید ----------

    public function orders(Request $request): Response
    {
        $status = $request->input('status');
        return $this->page('orders', [
            'orders' => $this->prod()->orders(is_string($status) ? $status : null),
            'statuses' => Production::STATUSES,
            'activeStatus' => is_string($status) ? $status : '',
            'stats' => $this->prod()->stats(),
            'warehouses' => $this->stock()->warehouses(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'دستورهای تولید');
    }

    public function orderCreateForm(Request $request): Response
    {
        return $this->page('order_form', [
            'recipes' => array_values(array_filter($this->prod()->recipes(), fn ($r) => (int) $r['active'] === 1)),
            'warehouses' => $this->stock()->warehouses(true),
            'defaultWarehouse' => $this->stock()->defaultWarehouse(),
            'preselected' => (int) $request->input('recipe', 0),
            'error' => null,
        ], 'دستور تولید تازه');
    }

    public function orderCreate(Request $request): Response
    {
        $result = $this->prod()->createOrder(
            (int) $request->input('recipe_id', 0),
            (float) $request->input('batches', 1),
            (int) $request->input('warehouse_id', 0),
            trim((string) $request->input('note', '')),
            $this->actor()
        );
        if (!$result['ok']) {
            return $this->back('/admin/production/orders/create', '', (string) $result['error']);
        }
        $this->log('ساخت دستور تولید', '#' . $result['id']);
        return Response::redirect('/admin/production/orders/' . $result['id'] . '?msg=' . urlencode('دستور تولید ساخته شد.'));
    }

    public function orderDetail(Request $request, array $params): Response
    {
        $order = $this->prod()->order((int) ($params['id'] ?? 0));
        if ($order === null) {
            return $this->back('/admin/production/orders', '', 'دستور یافت نشد.');
        }
        $warehouse = $this->stock()->warehouse((int) $order['warehouse_id']);
        return $this->page('order', [
            'order' => $order,
            'lines' => $this->prod()->availability($order),
            'canStart' => $this->prod()->canStart($order),
            'warehouse' => $warehouse,
            'statuses' => Production::STATUSES,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'دستور تولید ' . $order['code']);
    }

    public function orderStart(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $result = $this->prod()->start($id, $this->actor());
        if (!$result['ok']) {
            return $this->back('/admin/production/orders/' . $id, '', (string) $result['error']);
        }
        $order = $this->prod()->order($id);
        $this->log('شروع تولید', (string) ($order['code'] ?? ''));
        return $this->back('/admin/production/orders/' . $id, 'تولید شروع شد؛ مواد از انبار کسر شد.');
    }

    public function orderComplete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $order = $this->prod()->order($id);
        $result = $this->prod()->complete($id, $this->actor());
        if (!$result['ok']) {
            return $this->back('/admin/production/orders/' . $id, '', (string) $result['error']);
        }
        // اتصال به فروشگاه: موجودی محصول متناظر هم زیاد می‌شود.
        $shopMsg = '';
        if ($order !== null && $this->container->has('shop.catalog')) {
            $outItem = $this->stock()->item((int) $order['output_item_id']);
            $shopId = (int) ($outItem['shop_product_id'] ?? 0);
            if ($shopId > 0) {
                $catalog = $this->container->get('shop.catalog');
                $product = $catalog->product($shopId);
                if ($product !== null) {
                    $qty = (int) round((float) $order['output_qty']);
                    if ($qty > 0) {
                        $catalog->productQuickPrice($shopId, (int) $product['price'], (int) $product['sale_price'], (int) $product['stock'] + $qty);
                        $shopMsg = ' موجودی محصول فروشگاه هم ' . $qty . ' واحد زیاد شد.';
                    }
                }
            }
        }
        $this->log('اتمام تولید', (string) ($order['code'] ?? ''));
        return $this->back('/admin/production/orders/' . $id, 'تولید تمام شد؛ خروجی وارد انبار شد.' . $shopMsg);
    }

    public function orderCancel(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $order = $this->prod()->order($id);
        $result = $this->prod()->cancel($id, $this->actor());
        if (!$result['ok']) {
            return $this->back('/admin/production/orders/' . $id, '', (string) $result['error']);
        }
        $this->log('لغو دستور تولید', (string) ($order['code'] ?? ''));
        return $this->back('/admin/production/orders/' . $id, 'دستور لغو شد.');
    }
}
