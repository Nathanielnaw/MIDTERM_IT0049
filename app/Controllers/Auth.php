<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        $redirect = $this->safeRedirect((string) $this->request->getGet('redirect'));

        if (strtolower($this->request->getMethod()) === 'post') {
            $redirect = $this->safeRedirect((string) $this->request->getPost('redirect'));

            if (! $this->validate([
                'username' => 'required|max_length[50]',
                'password' => 'required',
            ])) {
                return view('auth/login', [
                    'title' => 'Log in',
                    'redirect' => $redirect,
                    'error' => 'Enter your username and password.',
                ]);
            }

            $user = (new UserModel())->findByUsername((string) $this->request->getPost('username'));
            $password = (string) $this->request->getPost('password');

            if ($user === null || ! password_verify($password, (string) ($user['password'] ?? ''))) {
                return view('auth/login', [
                    'title' => 'Log in',
                    'redirect' => $redirect,
                    'error' => 'The username or password is incorrect.',
                ]);
            }

            session()->regenerate(true);
            session()->set('auth_user', [
                'id' => (int) $user['id'],
                'username' => (string) $user['username'],
                'full_name' => (string) $user['full_name'],
            ]);

            return redirect()->to($redirect);
        }

        return view('auth/login', ['title' => 'Log in', 'redirect' => $redirect, 'error' => null]);
    }

    public function logout()
    {
        session()->remove(array_keys(session()->get()));
        session()->destroy();

        return redirect()->to(site_url('login'));
    }

    private function safeRedirect(string $redirect): string
    {
        return preg_match('~^/(?:tasks(?:/new|/[0-9]+/edit)?|profile)?$~D', $redirect) === 1
            ? $redirect
            : site_url('/');
    }
}
