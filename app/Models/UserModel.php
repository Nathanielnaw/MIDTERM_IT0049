<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['username', 'full_name', 'email', 'password', 'created_at', 'avatar', 'is_archived'];

    /** @return array<string, mixed>|null */
    public function findByUsername(string $username): ?array
    {
        return $this->where('is_archived', 0)->where('username', $username)->first();
    }

    /** @return array<string, mixed>|null */
    public function publicByUsername(string $username): ?array
    {
        return $this->select('id, username, full_name, email, created_at')
            ->where('username', $username)
            ->first();
    }

    public function demoUser(): ?array
    {
        return $this->select('id, username, full_name, email, created_at')
            ->where('username', 'demo.student')
            ->first();
    }
}
