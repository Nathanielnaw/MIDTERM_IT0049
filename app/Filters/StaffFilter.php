<?php
namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class StaffFilter extends AuthFilter
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session()->get('auth_user');
        if (is_array($user) && isset($user['id'])) {
            $active = (new UserModel())->select('id')->where('is_archived', 0)->find($user['id']);
            if ($active !== null) {
                return null;
            }
        }
        session()->remove('auth_user');
        return parent::before($request, $arguments);
    }
}
