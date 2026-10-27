<?php
declare(strict_types=1);

namespace Modules\Shop;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل فروشگاه: محصولات، دسته‌بندی‌ها، فیلدها، ویژگی‌ها، لیست قیمت و تنظیمات. */
final class AdminShopController
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

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('shop', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
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
        $actor = (string) ($this->container->get('session')->user()['username'] ?? '');
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

    // ---------- محصولات ----------

    public function products(Request $request): Response
    {
        $catalog = $this->catalog();
        return $this->page('products', [
            'products' => $catalog->productsAdmin(),
            'categories' => $catalog->categories(),
            'currency' => $this->settings()->get('currency', 'تومان'),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'محصولات فروشگاه');
    }

    public function productCreateForm(Request $request): Response
    {
        return $this->productForm(null, '/admin/shop/products/create', 'محصول تازه');
    }

    public function productEditForm(Request $request, array $params): Response
    {
        $product = $this->catalog()->product((int) ($params['id'] ?? 0));
        if ($product === null) {
            return $this->back('/admin/shop/products', '', 'محصول یافت نشد.');
        }
        return $this->productForm($product, '/admin/shop/products/edit/' . $product['id'], 'ویرایش محصول');
    }

    private function productForm(?array $product, string $action, string $title, ?string $error = null, ?array $old = null): Response
    {
        $catalog = $this->catalog();
        $values = $product !== null ? $catalog->valuesFor((int) $product['id']) : [];
        if ($old !== null) {
            $product = $old['product'];
            $values = $old['values'];
            $variants = $old['variants'];
        } else {
            $variants = $product !== null ? $catalog->variantsFor((int) $product['id']) : [];
        }
        return $this->page('product_form', [
            'product' => $product ?? ['category_id' => 0, 'title' => '', 'slug' => '', 'sku' => '', 'short_desc' => '', 'description' => '', 'price' => 0, 'sale_price' => 0, 'stock' => 0, 'track_stock' => 1, 'status' => 'draft', 'image' => '', 'featured' => 0, 'sort' => 0],
            'action' => $action,
            'categories' => $catalog->categories(),
            'fields' => $catalog->fields(),
            'values' => $values,
            'attributes' => $catalog->attributes(),
            'variants' => $variants,
            'error' => $error,
        ], $title);
    }

    /** خواندن و اعتبارسنجی فرم محصول؛ [data, values, variants, error] */
    private function readProductForm(Request $request, int $id): array
    {
        $catalog = $this->catalog();
        $title = trim((string) $request->input('title', ''));
        $slug = trim((string) $request->input('slug', ''));
        if ($slug === '') {
            $slug = Catalog::slugify($title, 'product');
        }
        $data = [
            'category_id' => (int) $request->input('category_id', 0),
            'title' => $title,
            'slug' => $slug,
            'sku' => trim((string) $request->input('sku', '')),
            'short_desc' => trim((string) $request->input('short_desc', '')),
            'description' => (string) $request->input('description', ''),
            'price' => max(0, (int) $request->input('price', 0)),
            'sale_price' => max(0, (int) $request->input('sale_price', 0)),
            'stock' => (int) $request->input('stock', 0),
            'track_stock' => $request->input('track_stock') ? 1 : 0,
            'status' => $request->input('status') === 'published' ? 'published' : 'draft',
            'image' => trim((string) $request->input('image', '')),
            'featured' => $request->input('featured') ? 1 : 0,
            'sort' => (int) $request->input('sort', 0),
        ];
        $error = null;
        if ($title === '') {
            $error = 'عنوان محصول الزامی است.';
        } elseif ($catalog->productSlugExists($slug, $id)) {
            $error = 'این نامک قبلاً برای محصول دیگری استفاده شده است.';
        }

        $values = [];
        foreach ($catalog->fields() as $field) {
            $raw = $request->input('field_' . $field['id'], '');
            $values[(int) $field['id']] = is_array($raw) ? implode('، ', $raw) : trim((string) $raw);
            if ($error === null && (int) $field['required'] === 1 && $values[(int) $field['id']] === '') {
                $error = 'فیلد «' . $field['title'] . '» الزامی است.';
            }
        }

        $variants = [];
        $rows = $request->input('variant', []);
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $options = [];
                foreach ((array) ($row['attr'] ?? []) as $attrSlug => $attrValue) {
                    $attrValue = trim((string) $attrValue);
                    if ($attrValue !== '') {
                        $options[(string) $attrSlug] = $attrValue;
                    }
                }
                if ($options === []) {
                    continue;
                }
                $variants[] = [
                    'options' => $options,
                    'price' => isset($row['price']) && trim((string) $row['price']) !== '' ? max(0, (int) $row['price']) : null,
                    'sale_price' => isset($row['sale_price']) && trim((string) $row['sale_price']) !== '' ? max(0, (int) $row['sale_price']) : null,
                    'stock' => (int) ($row['stock'] ?? 0),
                    'sku' => trim((string) ($row['sku'] ?? '')),
                ];
            }
        }
        return [$data, $values, $variants, $error];
    }

    public function productCreate(Request $request): Response
    {
        [$data, $values, $variants, $error] = $this->readProductForm($request, 0);
        if ($error !== null) {
            return $this->productForm(null, '/admin/shop/products/create', 'محصول تازه', $error, ['product' => $data, 'values' => $values, 'variants' => $variants]);
        }
        $id = $this->catalog()->productSave($data);
        $this->catalog()->saveValues($id, $values);
        $this->catalog()->replaceVariants($id, $variants);
        $this->log('ساخت محصول', $data['title']);
        return $this->back('/admin/shop/products', 'محصول ساخته شد.');
    }

    public function productUpdate(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        if ($this->catalog()->product($id) === null) {
            return $this->back('/admin/shop/products', '', 'محصول یافت نشد.');
        }
        [$data, $values, $variants, $error] = $this->readProductForm($request, $id);
        if ($error !== null) {
            $data['id'] = $id;
            return $this->productForm($data, '/admin/shop/products/edit/' . $id, 'ویرایش محصول', $error, ['product' => $data, 'values' => $values, 'variants' => $variants]);
        }
        $this->catalog()->productSave($data, $id);
        $this->catalog()->saveValues($id, $values);
        $this->catalog()->replaceVariants($id, $variants);
        $this->log('ویرایش محصول', $data['title']);
        return $this->back('/admin/shop/products', 'محصول به‌روز شد.');
    }

    public function productDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $product = $this->catalog()->product($id);
        if ($product !== null) {
            $this->catalog()->productDelete($id);
            $this->log('حذف محصول', (string) $product['title']);
        }
        return $this->back('/admin/shop/products', 'محصول حذف شد.');
    }

    // ---------- دسته‌بندی‌ها ----------

    public function categories(Request $request): Response
    {
        $catalog = $this->catalog();
        $edit = null;
        $editId = (int) $request->input('edit', 0);
        if ($editId > 0) {
            $edit = $catalog->category($editId);
        }
        return $this->page('categories', [
            'categories' => $catalog->categories(),
            'edit' => $edit,
            'counts' => array_map(fn ($c) => $catalog->categoryProductCount((int) $c['id']), $catalog->categories()),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'دسته‌بندی‌های فروشگاه');
    }

    public function categorySave(Request $request): Response
    {
        $catalog = $this->catalog();
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        $slug = trim((string) $request->input('slug', ''));
        if ($slug === '') {
            $slug = Catalog::slugify($title, 'category');
        }
        if ($title === '') {
            return $this->back('/admin/shop/categories', '', 'عنوان دسته الزامی است.');
        }
        if ($catalog->categorySlugExists($slug, $id)) {
            return $this->back('/admin/shop/categories', '', 'این نامک قبلاً استفاده شده است.');
        }
        $parent = (int) $request->input('parent_id', 0);
        if ($parent === $id) {
            $parent = 0;
        }
        $catalog->categorySave([
            'parent_id' => $parent,
            'title' => $title,
            'slug' => $slug,
            'description' => trim((string) $request->input('description', '')),
            'sort' => (int) $request->input('sort', 0),
        ], $id);
        $this->log($id > 0 ? 'ویرایش دسته' : 'ساخت دسته', $title);
        return $this->back('/admin/shop/categories', 'دسته ذخیره شد.');
    }

    public function categoryDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $cat = $this->catalog()->category($id);
        if ($cat !== null) {
            $this->catalog()->categoryDelete($id);
            $this->log('حذف دسته', (string) $cat['title']);
        }
        return $this->back('/admin/shop/categories', 'دسته حذف شد.');
    }

    // ---------- فیلدهای سفارشی ----------

    public function fields(Request $request): Response
    {
        $edit = null;
        $editId = (int) $request->input('edit', 0);
        if ($editId > 0) {
            $edit = $this->catalog()->field($editId);
        }
        return $this->page('fields', [
            'fields' => $this->catalog()->fields(),
            'edit' => $edit,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'فیلدهای محصول');
    }

    public function fieldSave(Request $request): Response
    {
        $catalog = $this->catalog();
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        $key = trim((string) $request->input('fkey', ''));
        if ($key === '') {
            $key = 'f' . substr(md5($title . microtime()), 0, 8);
        }
        $type = (string) $request->input('type', 'text');
        if (!in_array($type, ['text', 'number', 'select', 'textarea', 'checkbox'], true)) {
            $type = 'text';
        }
        if ($title === '') {
            return $this->back('/admin/shop/fields', '', 'عنوان فیلد الزامی است.');
        }
        if ($catalog->fieldKeyExists($key, $id)) {
            return $this->back('/admin/shop/fields', '', 'این کلید قبلاً استفاده شده است.');
        }
        $catalog->fieldSave([
            'title' => $title,
            'fkey' => $key,
            'type' => $type,
            'options' => trim((string) $request->input('options', '')),
            'required' => $request->input('required') ? 1 : 0,
            'sort' => (int) $request->input('sort', 0),
        ], $id);
        $this->log($id > 0 ? 'ویرایش فیلد محصول' : 'ساخت فیلد محصول', $title);
        return $this->back('/admin/shop/fields', 'فیلد ذخیره شد.');
    }

    public function fieldDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $field = $this->catalog()->field($id);
        if ($field !== null) {
            $this->catalog()->fieldDelete($id);
            $this->log('حذف فیلد محصول', (string) $field['title']);
        }
        return $this->back('/admin/shop/fields', 'فیلد حذف شد.');
    }

    // ---------- ویژگی‌ها ----------

    public function attributes(Request $request): Response
    {
        return $this->page('attributes', [
            'attributes' => $this->catalog()->attributes(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'ویژگی‌های محصول');
    }

    public function attributeSave(Request $request): Response
    {
        $catalog = $this->catalog();
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        $slug = trim((string) $request->input('slug', ''));
        if ($slug === '') {
            $slug = Catalog::slugify($title, 'attr');
        }
        if ($title === '') {
            return $this->back('/admin/shop/attributes', '', 'عنوان ویژگی الزامی است.');
        }
        if ($catalog->attributeSlugExists($slug, $id)) {
            return $this->back('/admin/shop/attributes', '', 'این نامک ویژگی قبلاً استفاده شده است.');
        }
        $catalog->attributeSave(['title' => $title, 'slug' => $slug, 'sort' => (int) $request->input('sort', 0)], $id);
        $this->log($id > 0 ? 'ویرایش ویژگی' : 'ساخت ویژگی', $title);
        return $this->back('/admin/shop/attributes', 'ویژگی ذخیره شد.');
    }

    public function attributeDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $attr = $this->catalog()->attribute($id);
        if ($attr !== null) {
            $this->catalog()->attributeDelete($id);
            $this->log('حذف ویژگی', (string) $attr['title']);
        }
        return $this->back('/admin/shop/attributes', 'ویژگی حذف شد.');
    }

    public function attributeValueAdd(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $value = trim((string) $request->input('value', ''));
        if ($this->catalog()->attribute($id) !== null && $value !== '') {
            $this->catalog()->attributeValueAdd($id, $value, (int) $request->input('sort', 0));
        }
        return $this->back('/admin/shop/attributes', 'مقدار اضافه شد.');
    }

    public function attributeValueDelete(Request $request, array $params): Response
    {
        $this->catalog()->attributeValueDelete((int) ($params['id'] ?? 0));
        return $this->back('/admin/shop/attributes', 'مقدار حذف شد.');
    }

    // ---------- لیست قیمت ----------

    public function pricelist(Request $request): Response
    {
        $catalog = $this->catalog();
        $variants = [];
        foreach ($catalog->productsAdmin() as $product) {
            $variants[(int) $product['id']] = $catalog->variantsFor((int) $product['id']);
        }
        return $this->page('pricelist', [
            'products' => $catalog->productsAdmin(),
            'variants' => $variants,
            'categories' => $catalog->categories(),
            'currency' => $this->settings()->get('currency', 'تومان'),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'لیست قیمت محصولات');
    }

    public function pricelistSave(Request $request): Response
    {
        $catalog = $this->catalog();
        $prices = $request->input('price', []);
        $sales = $request->input('sale_price', []);
        $stocks = $request->input('stock', []);
        if (is_array($prices)) {
            foreach ($prices as $id => $price) {
                $id = (int) $id;
                if ($catalog->product($id) === null) {
                    continue;
                }
                $catalog->productQuickPrice(
                    $id,
                    max(0, (int) $price),
                    max(0, (int) ($sales[$id] ?? 0)),
                    (int) ($stocks[$id] ?? 0)
                );
            }
        }
        $this->log('ویرایش لیست قیمت', 'محصولات');
        return $this->back('/admin/shop/pricelist', 'قیمت‌ها به‌روز شد.');
    }

    // ---------- تنظیمات ----------

    public function settingsForm(Request $request): Response
    {
        $s = $this->settings();
        return $this->page('settings', [
            'values' => [
                'shop_title' => $s->get('shop_title', 'فروشگاه وصله'),
                'currency' => $s->get('currency', 'تومان'),
                'shipping_cost' => $s->get('shipping_cost', '0'),
                'free_shipping_over' => $s->get('free_shipping_over', '0'),
                'tax_percent' => $s->get('tax_percent', '0'),
                'order_note' => $s->get('order_note', ''),
                'shop_accent' => $s->get('shop_accent', '#4f46e5'),
                'gift_wrap_price' => $s->get('gift_wrap_price', '0'),
                'delivery_slots' => $s->get('delivery_slots', ''),
                'payment_methods' => $s->get('payment_methods', ''),
            ],
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'تنظیمات فروشگاه');
    }

    public function settingsSave(Request $request): Response
    {
        $s = $this->settings();
        $s->set('shop_title', trim((string) $request->input('shop_title', '')));
        $s->set('currency', trim((string) $request->input('currency', 'تومان')));
        $s->set('shipping_cost', (string) max(0, (int) $request->input('shipping_cost', 0)));
        $s->set('free_shipping_over', (string) max(0, (int) $request->input('free_shipping_over', 0)));
        $s->set('tax_percent', (string) max(0, min(100, (int) $request->input('tax_percent', 0))));
        $s->set('order_note', trim((string) $request->input('order_note', '')));
        $accent = trim((string) $request->input('shop_accent', '#4f46e5'));
        $s->set('shop_accent', preg_match('/^#[0-9a-fA-F]{6}$/', $accent) ? $accent : '#4f46e5');
        $s->set('gift_wrap_price', (string) max(0, (int) $request->input('gift_wrap_price', 0)));
        $s->set('delivery_slots', trim((string) $request->input('delivery_slots', '')));
        $s->set('payment_methods', trim((string) $request->input('payment_methods', '')));
        $this->log('ویرایش تنظیمات فروشگاه', 'تنظیمات');
        return $this->back('/admin/shop/settings', 'تنظیمات ذخیره شد.');
    }

    // ---------- کوپن‌های تخفیف ----------

    private function couponService(): Coupons
    {
        return $this->container->get('shop.coupons');
    }

    public function coupons(Request $request): Response
    {
        return $this->page('coupons', [
            'coupons' => $this->couponService()->all(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'کوپن‌های تخفیف');
    }

    public function couponSave(Request $request): Response
    {
        $code = trim((string) $request->input('code', ''));
        $kind = $request->input('kind') === 'fixed' ? 'fixed' : 'percent';
        $value = max(0, (int) $request->input('value', 0));
        if ($kind === 'percent') {
            $value = min(100, $value);
        }
        if ($code === '') {
            return $this->back('/admin/shop/coupons', '', 'کد کوپن الزامی است.');
        }
        $existing = $this->couponService()->findByCode($code);
        $id = (int) $request->input('id', 0);
        if ($existing !== null && (int) $existing['id'] !== $id) {
            return $this->back('/admin/shop/coupons', '', 'این کد قبلاً ثبت شده است.');
        }
        $this->couponService()->save([
            'code' => $code,
            'kind' => $kind,
            'value' => $value,
            'expires_at' => trim((string) $request->input('expires_at', '')),
            'max_uses' => max(0, (int) $request->input('max_uses', 0)),
            'active' => $request->input('active') ? 1 : 0,
        ], $id);
        $this->log('ذخیره کوپن', $code);
        return $this->back('/admin/shop/coupons', 'کوپن ذخیره شد.');
    }

    public function couponDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $this->couponService()->delete($id);
        $this->log('حذف کوپن', (string) $id);
        return $this->back('/admin/shop/coupons', 'کوپن حذف شد.');
    }

    // ---------- قالب‌های فروشگاه ----------

    private function templateService(): ShopTemplates
    {
        return $this->container->get('shop.templates');
    }

    public function templates(Request $request): Response
    {
        return $this->page('templates', [
            'templates' => $this->templateService()->all(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'قالب‌های فروشگاه');
    }

    public function templateForm(Request $request, array $params = []): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $tpl = $id > 0 ? $this->templateService()->find($id) : null;
        if ($id > 0 && $tpl === null) {
            return $this->back('/admin/shop/templates', '', 'قالب پیدا نشد.');
        }
        return $this->page('template_form', [
            'tpl' => $tpl,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], $tpl !== null ? 'ویرایش قالب' : 'قالب تازه');
    }

    public function templateSave(Request $request, array $params = []): Response
    {
        $id = (int) ($params['id'] ?? 0);
        [$tpl, $error] = $this->templateService()->save(
            $id > 0 ? $id : null,
            (string) $request->input('name', ''),
            (string) $request->input('title', ''),
            (string) $request->input('content', '')
        );
        if ($error !== null) {
            return $this->back($id > 0 ? '/admin/shop/templates/edit/' . $id : '/admin/shop/templates/create', '', $error);
        }
        $this->log('ذخیره قالب فروشگاه', (string) ($tpl['name'] ?? ''));
        return $this->back('/admin/shop/templates', 'قالب ذخیره شد.');
    }

    public function templateDefault(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        if (!$this->templateService()->setDefault($id)) {
            return $this->back('/admin/shop/templates', '', 'قالب پیدا نشد.');
        }
        $this->log('قالب پیش‌فرض فروشگاه', (string) $id);
        return $this->back('/admin/shop/templates', 'قالب پیش‌فرض شد.');
    }

    public function templateDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        if (!$this->templateService()->delete($id)) {
            return $this->back('/admin/shop/templates', '', 'قالب پیش‌فرض حذف نمی‌شود.');
        }
        $this->log('حذف قالب فروشگاه', (string) $id);
        return $this->back('/admin/shop/templates', 'قالب حذف شد.');
    }
}
