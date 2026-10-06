<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $allowedFields = ['title', 'status', 'task_date', 'created_at'];
    protected $returnType = 'array';

    public function today(): array
    {
        return $this->where('task_date', date('Y-m-d'))
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function allOrderedByDate(): array
    {
        return $this->orderBy('task_date', 'ASC')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }
}
