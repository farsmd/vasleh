<?php
declare(strict_types=1);

namespace Modules\Account;

use Core\Container;
use Core\Request;
use Core\Response;
use Core\View;
use Modules\Session\Auth;
use Modules\Session\SessionManager;

/** بخش کاربری: پروفایل + تغییر کلمه عبور (گارد ورود در هوک ماژول است). */
final class AccountController
{
    public function __construct(private readonly Container $container) {}

    private function session(): SessionManager
    {
        return $this->container->get('session');
    }

    private function page(string $view, string $title, array $data = []): Response
    {
        $content = View::render('account', 'front', $view, $data);
        return Response::html(View::render('front', 'front', 'layout', [
            'title'   => $title,
            'content' => $content,
        ]));
    }

    public function profile(Request $request): Response
    {
        $sessionUser = $this->session()->user();
        /** @var Auth $auth */
        $auth = $this->container->get('auth');
        $user = $auth->userById((int) ($sessionUser['id'] ?? 0)) ?? $sessionUser;

        return $this->page('profile', 'حساب کاربری', [
            'user' => $user,
            'csrf' => $this->session()->csrfToken(),
        ]);
    }

    public function passwordForm(Request $request): Response
    {
        return $this->page('password', 'تغییر کلمه عبور', [
            'csrf'  => $this->session()->csrfToken(),
            'ok'    => $request->input('ok') === '1',
            'error' => (string) $request->input('err', ''),
        ]);
    }

    public function passwordUpdate(Request $request): Response
    {
        $sessionUser = $this->session()->user();
        $current = (string) $request->input('current_password', '');
        $new     = (string) $request->input('new_password', '');
        $confirm = (string) $request->input('confirm_password', '');

        if ($new !== $confirm) {
            return Response::redirect('/account/password?err=mismatch');
        }
        if ($new === $current) {
            return Response::redirect('/account/password?err=same');
        }

        /** @var Auth $auth */
        $auth = $this->container->get('auth');
        $result = $auth->changePassword((int) ($sessionUser['id'] ?? 0), $current, $new);
        if ($result !== 'ok') {
            return Response::redirect('/account/password?err=' . $result);
        }

        if ($this->container->has('logger')) {
            $this->container->get('logger')->user((string) ($sessionUser['username'] ?? ''), 'تغییر کلمه عبور');
        }

        // بعد از تغییر رمز، شناسه سشن می‌چرخد تا سشن فعلی هم تازه شود.
        $fresh = $auth->userById((int) $sessionUser['id']);
        if ($fresh !== null) {
            $this->session()->login([
                'id'       => (int) $fresh['id'],
                'username' => (string) $fresh['username'],
                'role'     => (string) $fresh['role'],
            ]);
        }
        return Response::redirect('/account/password?ok=1');
    }
}
