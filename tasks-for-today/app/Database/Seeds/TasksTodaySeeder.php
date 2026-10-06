<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TasksTodaySeeder extends Seeder
{
    public function run()
    {
        $this->db->table('tasks')->insertBatch([
            ['title' => 'Review project requirements', 'status' => 'completed', 'task_date' => '2026-09-30', 'created_at' => '2026-09-30 08:00:00'],
            ['title' => 'Implement TaskModel filtering', 'status' => 'pending', 'task_date' => '2026-09-30', 'created_at' => '2026-09-30 09:00:00'],
            ['title' => 'Create dashboard views', 'status' => 'pending', 'task_date' => '2026-09-30', 'created_at' => '2026-09-30 10:00:00'],
            ['title' => 'Test database connection', 'status' => 'pending', 'task_date' => '2026-09-30', 'created_at' => '2026-09-30 11:00:00'],
            ['title' => 'Prepare presentation notes', 'status' => 'pending', 'task_date' => '2026-10-01', 'created_at' => '2026-09-29 14:00:00'],
            ['title' => 'Check validation rules', 'status' => 'completed', 'task_date' => '2026-10-01', 'created_at' => '2026-09-29 15:00:00'],
            ['title' => 'Update README instructions', 'status' => 'pending', 'task_date' => '2026-10-02', 'created_at' => '2026-09-29 16:00:00'],
            ['title' => 'Publish the hosted version', 'status' => 'pending', 'task_date' => '2026-10-02', 'created_at' => '2026-09-29 17:00:00'],
        ]);

        $this->db->table('users')->insert([
            'username' => 'alfred',
            'full_name' => 'Alfred Nathaniel Embuscado',
            'email' => 'alfred@example.com',
            'created_at' => '2026-09-30 08:00:00',
        ]);
    }
}
