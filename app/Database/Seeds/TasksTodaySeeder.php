<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use RuntimeException;

class TasksTodaySeeder extends Seeder
{
    public function run()
    {
        $now = Time::now(config('App')->appTimezone);
        $createdAt = $now->format('Y-m-d H:i:s');
        $this->db->transStart();

        // Preserve other users and any edits to the demo account.
        if ($this->db->table('users')->where('username', 'demo.student')->countAllResults() === 0) {
            $this->db->table('users')->insert([
                'username' => 'demo.student',
                'full_name' => 'Demo Student',
                'email' => 'student@example.com',
                'created_at' => $createdAt,
            ]);
        }

        // Populate only an empty task table: reruns never duplicate or erase work.
        if ($this->db->table('tasks')->countAllResults() === 0) {
            $samples = [
                ['Review project requirements', 'completed', 0],
                ['Implement TaskModel filtering', 'pending', 0],
                ['Create dashboard views', 'pending', 0],
                ['Test database connection', 'pending', 0],
                ['Prepare presentation notes', 'pending', 1],
                ['Check validation rules', 'completed', 1],
                ['Update README instructions', 'pending', 2],
                ['Publish the hosted version', 'pending', 2],
            ];
            $tasks = [];
            foreach ($samples as [$title, $status, $offset]) {
                $tasks[] = [
                    'title' => $title,
                    'status' => $status,
                    'task_date' => $now->addDays($offset)->format('Y-m-d'),
                    'created_at' => $createdAt,
                ];
            }
            $this->db->table('tasks')->insertBatch($tasks);
        }

        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            throw new RuntimeException('Demo data could not be seeded. No partial changes were saved.');
        }
    }
}
