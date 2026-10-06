<?php

namespace App\Controllers;

use App\Models\TaskModel;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome', [
            'title' => 'Tasks for Today',
            'tasks' => (new TaskModel())->today(),
            'today' => date('F j, Y'),
        ]);
    }
}
