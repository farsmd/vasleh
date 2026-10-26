<?php
declare(strict_types=1);

namespace Modules\Front;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;

/**
 * صفحه اصلی (لندینگ فروش وصله) + سرنخ‌های درخواست تست + مدیریت آن‌ها.
 * جمع فاکتور همیشه سمت سرور محاسبه می‌شود؛ فرم فقط اسلاگ ماژول‌ها را
 * می‌فرستد، نه قیمت را.
 */
final class FrontController
{
    public function __construct(private readonly Container $container) {}

    private function landing(): Landing
    {
        return $this->container->get('front.landing');
    }

    private function session(): \Modules\Session\SessionManager
    {
        return $this->container->get('session');
    }

    /** نسخه نمایشی از version.json ریشه برنامه. */
    private function versionFa(): string
    {
        $file = dirname(__DIR__, 3) . '/version.json';
        $raw = is_file($file) ? (string) file_get_contents($file) : '';
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['core'], $data['system'], $data['other'])) {
            return '';
        }
        return Landing::faVersion($data['core'] . '.' . $data['system'] . '.' . $data['other']);
    }

    public function home(Request $request): Response
    {
        return Response::html(View::render('front', 'front', 'landing', [
            'prices' => $this->landing()->prices(),
            'modules' => Landing::MODULES,
            'csrf' => $this->session()->csrfToken(),
            'version' => $this->versionFa(),
            'leadOk' => $request->input('lead') === 'ok',
            'err' => (string) $request->input('err', ''),
        ]));
    }

    public function leadSubmit(Request $request): Response
    {
        if (!$this->session()->validCsrf($request->input('_csrf'))) {
            return Response::forbidden('توکن امنیتی نامعتبر است');
        }
        $name = trim((string) $request->input('name', ''));
        if ($name === '' || strlen($name) > 200) {
            return Response::redirect('/?err=name');
        }
        $digits = (string) preg_replace('/[^0-9]/', '', (string) $request->input('phone', ''));
        if (strlen($digits) === 12 && str_starts_with($digits, '98')) {
            $digits = '0' . substr($digits, 2);
        }
        if (!preg_match('/^09[0-9]{9}$/', $digits)) {
            return Response::redirect('/?err=phone');
        }
        $wanted = array_values(array_intersect((array) ($request->input('modules', []) ?? []), array_keys(Landing::MODULES)));
        $prices = $this->landing()->prices();
        $total = (int) $prices['base'];
        foreach ($wanted as $slug) {
            $total += (int) $prices[$slug];
        }
        $this->landing()->leadCreate($name, $digits, $wanted, $total);
        return Response::redirect('/?lead=ok');
    }

    // ---------- مدیریت (فقط مالک) ----------

    private function requireOwner(): ?Response
    {
        $user = $this->session()->user();
        if ($user === null) {
            return Response::redirect('/admin/login');
        }
        if (($user['role'] ?? '') !== 'owner') {
            return Response::forbidden('این بخش فقط برای مالک است');
        }
        return null;
    }

    private function page(string $view, array $data, string $title): Response
    {
        $session = $this->session();
        $content = View::render('front', 'admin', $view, $data + ['csrf' => $session->csrfToken()]);
        return Response::html(View::render('admin', 'admin', 'layout', [
            'title' => $title,
            'content' => $content,
            'menu' => $this->container->get('loader')->adminMenu(),
            'user' => $session->user(),
            'csrf' => $session->csrfToken(),
        ]));
    }

    private function back(string $path, string $msg = ''): Response
    {
        return Response::redirect($path . ($msg === '' ? '' : '?msg=' . urlencode($msg)));
    }

    public function leadsAdmin(Request $request): Response
    {
        if (($r = $this->requireOwner()) !== null) {
            return $r;
        }
        return $this->page('leads', [
            'leads' => $this->landing()->leads(),
            'modules' => Landing::MODULES,
            'prices' => $this->landing()->prices(),
            'flash' => (string) $request->input('msg', ''),
        ], 'درخواست‌های آزمایشی');
    }

    public function leadDelete(Request $request, array $params): Response
    {
        if (($r = $this->requireOwner()) !== null) {
            return $r;
        }
        $this->landing()->leadDelete((int) ($params['id'] ?? 0));
        return $this->back('/admin/leads', 'درخواست حذف شد.');
    }

    public function landingSettings(Request $request): Response
    {
        if (($r = $this->requireOwner()) !== null) {
            return $r;
        }
        return $this->page('landing_settings', [
            'prices' => $this->landing()->prices(),
            'modules' => Landing::MODULES,
            'flash' => (string) $request->input('msg', ''),
        ], 'تنظیمات لندینگ');
    }

    public function landingSave(Request $request): Response
    {
        if (($r = $this->requireOwner()) !== null) {
            return $r;
        }
        $input = [];
        foreach (array_keys(Landing::DEFAULTS) as $key) {
            $raw = $request->input('price_' . $key);
            if ($raw !== null && $raw !== '') {
                $input[$key] = (int) $raw;
            }
        }
        $this->landing()->setPrices($input);
        return $this->back('/admin/landing', 'قیمت‌های لندینگ ذخیره شد.');
    }
}
