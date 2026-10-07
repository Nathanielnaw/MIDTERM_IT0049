<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('auth_user') !== null) {
            return null;
        }

        $target = $request->getUri()->getPath();
        if ($request->getUri()->getQuery() !== '') {
            $target .= '?' . $request->getUri()->getQuery();
        }

        return redirect()->to(site_url('login') . '?redirect=' . rawurlencode($target));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
