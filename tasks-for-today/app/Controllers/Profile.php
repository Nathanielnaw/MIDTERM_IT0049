<?php

namespace App\Controllers;

use App\Models\UserModel;

class Profile extends BaseController
{
    public function index()
    {
        return view('profile', [
            'title' => 'Profile',
            'user' => (new UserModel())->first(),
        ]);
    }
}
