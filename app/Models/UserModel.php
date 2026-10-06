<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['username', 'full_name', 'email', 'created_at'];

    public function demoUser(): ?array
    {
        return $this->where('username', 'demo.student')->first();
    }
}
