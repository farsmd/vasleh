<?php
declare(strict_types=1);

namespace Modules\Admin;

use Core\Container;
use Core\Request;
use Core\Response;

final class MenuOrderController
{
    public function __construct(private readonly Container $container) {}

    public function index(Request $request): Response
    {
        $loader = $this->container->get('loader');
        $groups = [];
        foreach ($loader->adminMenu() as $item) {
            // داشبورد لینک ثابت بالای سایدبار است، نه آیتم گروه‌ها.
            if ((string) ($item['path'] ?? '') === '/admin') {
                continue;
            }
            $slug = (string) ($item['module'] ?? '');
            if (!isset($groups[$slug])) {
                $groups[$slug] = ['title' => (string) ($item['module_title'] ?? $slug), 'items' => []];
            }
            $groups[$slug]['items'][] = $item;
        }
        $session = $this->container->has('session') ? $this->container->get('session') : null;
        $content = \Core\View::render('admin', 'admin', 'menu-order', [
            'groups' => $groups,
            'csrf' => $session?->csrfToken() ?? '',
            'flash' => $request->input('msg'),
        ]);
        return Response::html(\Core\View::render('admin', 'admin', 'layout', [
            'title' => 'ترتیب منو',
            'content' => $content,
            'menu' => $loader->adminMenu(),
            'user' => $session?->user(),
            'csrf' => $session?->csrfToken() ?? '',
        ]));
    }

    /** جابه‌جایی یک آیتم با همسایه بالایی/پایینی همان گروه و ذخیره ترتیب کامل. */
    public function move(Request $request): Response
    {
        $loader = $this->container->get('loader');
        $path = (string) $request->input('path', '');
        $dir = (string) $request->input('dir', '');
        $items = $loader->adminMenu();
        $target = null;
        $targetModule = '';
        foreach ($items as $i => $item) {
            if ((string) ($item['path'] ?? '') === $path) {
                $target = $i;
                $targetModule = (string) ($item['module'] ?? '');
                break;
            }
        }
        if ($target !== null) {
            $siblings = [];
            foreach ($items as $i => $item) {
                if ((string) ($item['module'] ?? '') === $targetModule) {
                    $siblings[] = $i;
                }
            }
            $pos = array_search($target, $siblings, true);
            $swap = ($dir === 'up') ? $pos - 1 : $pos + 1;
            if ($pos !== false && $swap >= 0 && $swap < count($siblings)) {
                $other = $siblings[$swap];
                [$items[$target], $items[$other]] = [$items[$other], $items[$target]];
            }
        }
        $order = [];
        foreach ($items as $item) {
            $order[] = (string) ($item['path'] ?? '');
        }
        // ترتیب گروه‌ها دست نمی‌خورد؛ فقط آیتم‌ها جابه‌جا شده‌اند.
        $loader->saveMenuOrder($order, $loader->menuOrderData()['groups']);
        return Response::redirect('/admin/menu');
    }

    /**
     * ذخیره ترتیب درگ‌انددراپ: groups[] نامک ماژول‌ها و order[] مسیر آیتم‌ها.
     * فقط مقادیر شناخته‌شده پذیرفته می‌شوند؛ بقیه در انتها با ترتیب فعلی می‌مانند.
     */
    public function save(Request $request): Response
    {
        $loader = $this->container->get('loader');
        $knownGroups = [];
        $knownPaths = [];
        foreach ($loader->adminMenu() as $item) {
            $knownGroups[(string) ($item['module'] ?? '')] = true;
            $knownPaths[(string) ($item['path'] ?? '')] = true;
        }
        $groups = [];
        foreach ((array) $request->input('groups', []) as $g) {
            $g = (string) $g;
            if (isset($knownGroups[$g]) && !in_array($g, $groups, true)) {
                $groups[] = $g;
            }
        }
        $order = [];
        foreach ((array) $request->input('order', []) as $p) {
            $p = (string) $p;
            if (isset($knownPaths[$p]) && !in_array($p, $order, true)) {
                $order[] = $p;
            }
        }
        // جاافتاده‌ها در انتها با ترتیب فعلی حفظ می‌شوند تا چیزی گم نشود.
        foreach ($loader->adminMenu() as $item) {
            $p = (string) ($item['path'] ?? '');
            if (!in_array($p, $order, true)) {
                $order[] = $p;
            }
            $g = (string) ($item['module'] ?? '');
            if (!in_array($g, $groups, true)) {
                $groups[] = $g;
            }
        }
        $loader->saveMenuOrder($order, $groups);
        return Response::redirect('/admin/menu?msg=' . urlencode('ترتیب منو ذخیره شد'));
    }
}
