<?php

namespace App\Controllers;

use App\Models\TaskModel;
use CodeIgniter\I18n\Time;

class Home extends BaseController
{
    public function index(): string
    {
        $today = Time::now(config('App')->appTimezone);

        return view('welcome', [
            'title' => 'Tasks for Today',
            'tasks' => (new TaskModel())->forDate($today->format('Y-m-d')),
            'today' => $today,
        ]);
    }
}
