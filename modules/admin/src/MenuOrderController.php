<?php
declare(strict_types=1);

namespace Modules\Admin;

use Core\Container;
use Core\Request;
use Core\Response;

final class MenuOrderController
{
    public function __construct(private readonly Container $container) {}

    public function index(): Response
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
        $loader->saveMenuOrder($order);
        return Response::redirect('/admin/menu');
    }
}
