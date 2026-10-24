<?php
declare(strict_types=1);

namespace Modules\Priceextract;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/** پنل استخراج قیمت: منابع، اجرای استخراج، فهرست قیمت‌ها و سوابق اجراها. */
final class AdminPriceController
{
    public function __construct(private readonly Container $container) {}

    private function extractor(): Extractor
    {
        return $this->container->get('priceextract.extractor');
    }

    private function actor(): string
    {
        return (string) ($this->container->get('session')->user()['username'] ?? '');
    }

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->container->get('session');
        $content = View::render('priceextract', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
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

    // ---------- منابع ----------

    public function sources(Request $request): Response
    {
        $ex = $this->extractor();
        $edit = null;
        $editId = (int) $request->input('edit', 0);
        if ($editId > 0) {
            $edit = $ex->source($editId);
        }
        return $this->page('sources', [
            'sources' => $ex->sources(),
            'edit' => $edit,
            'adapters' => Extractor::ADAPTERS,
            'stats' => $ex->stats(),
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'منابع استخراج قیمت');
    }

    public function sourceSave(Request $request): Response
    {
        $id = (int) $request->input('id', 0);
        $title = trim((string) $request->input('title', ''));
        $url = trim((string) $request->input('url', ''));
        if ($title === '' || $url === '') {
            return $this->back('/admin/priceextract', '', 'عنوان و آدرس منبع الزامی است.');
        }
        $adapter = (string) $request->input('adapter', 'json');
        if (!isset(Extractor::ADAPTERS[$adapter])) {
            $adapter = 'json';
        }
        $mapping = trim((string) $request->input('mapping', ''));
        if ($mapping !== '') {
            $decoded = json_decode($mapping, true);
            if (!is_array($decoded)) {
                return $this->back('/admin/priceextract', '', 'نگاشت فیلدها باید JSON معتبر باشد.');
            }
            $mapping = (string) json_encode($decoded, JSON_UNESCAPED_UNICODE);
        }
        $this->extractor()->sourceSave([
            'title' => $title,
            'url' => $url,
            'adapter' => $adapter,
            'mapping' => $mapping,
            'active' => $request->input('active') ? 1 : 0,
        ], $id);
        $this->log($id > 0 ? 'ویرایش منبع قیمت' : 'ساخت منبع قیمت', $title);
        return $this->back('/admin/priceextract', 'منبع ذخیره شد.');
    }

    public function sourceDelete(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $source = $this->extractor()->source($id);
        if ($source !== null) {
            $this->extractor()->sourceDelete($id);
            $this->log('حذف منبع قیمت', (string) $source['title']);
        }
        return $this->back('/admin/priceextract', 'منبع و آیتم‌هایش حذف شد.');
    }

    public function sourceRun(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $source = $this->extractor()->source($id);
        if ($source === null) {
            return $this->back('/admin/priceextract', '', 'منبع یافت نشد.');
        }
        $result = $this->extractor()->run($id);
        if (!$result['ok']) {
            return $this->back('/admin/priceextract', '', 'استخراج ناموفق بود: ' . $result['error']);
        }
        $this->log('اجرای استخراج قیمت', (string) $source['title'] . ' — ' . $result['count'] . ' آیتم');
        return $this->back('/admin/priceextract/items?source=' . $id, $result['count'] . ' آیتم استخراج شد.');
    }

    /** وارد کردن CSV برای یک منبع (وقتی سایت API عمومی ندارد). */
    public function importCsv(Request $request): Response
    {
        $sourceId = (int) $request->input('source_id', 0);
        $source = $this->extractor()->source($sourceId);
        if ($source === null) {
            return $this->back('/admin/priceextract', '', 'منبع یافت نشد.');
        }
        $file = $_FILES['csv_file'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_file((string) ($file['tmp_name'] ?? ''))) {
            return $this->back('/admin/priceextract', '', 'فایل CSV انتخاب نشده است.');
        }
        if (filesize((string) $file['tmp_name']) > 5 * 1024 * 1024) {
            return $this->back('/admin/priceextract', '', 'فایل بزرگ‌تر از ۵ مگابایت است.');
        }
        $csv = (string) file_get_contents((string) $file['tmp_name']);
        if (str_starts_with($csv, "\xEF\xBB\xBF")) {
            $csv = substr($csv, 3); // حذف BOM
        }
        $count = $this->extractor()->importCsv($csv, $sourceId);
        $this->log('وارد کردن CSV قیمت', (string) $source['title'] . ' — ' . $count . ' آیتم');
        return $this->back('/admin/priceextract/items?source=' . $sourceId, $count . ' آیتم از CSV وارد شد.');
    }

    // ---------- فهرست قیمت‌ها ----------

    public function items(Request $request): Response
    {
        $ex = $this->extractor();
        $sourceId = (int) $request->input('source', 0);
        $filters = [
            'source_id' => $sourceId,
            'category' => trim((string) $request->input('category', '')),
            'q' => trim((string) $request->input('q', '')),
        ];
        return $this->page('items', [
            'rows' => $ex->items($filters),
            'sources' => $ex->sources(),
            'categories' => $sourceId > 0 ? $ex->categories($sourceId) : [],
            'filters' => $filters,
            'flash' => $request->input('msg'),
            'error' => $request->input('err'),
        ], 'فهرست قیمت‌های استخراج‌شده');
    }

    // ---------- سوابق اجراها ----------

    public function runs(Request $request, array $params): Response
    {
        $id = (int) ($params['id'] ?? 0);
        $source = $this->extractor()->source($id);
        if ($source === null) {
            return $this->back('/admin/priceextract', '', 'منبع یافت نشد.');
        }
        return $this->page('runs', [
            'source' => $source,
            'runs' => $this->extractor()->runs($id),
        ], 'سوابق استخراج — ' . $source['title']);
    }
}
