<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTaskArchive extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('is_archived', 'tasks')) {
            $this->forge->addColumn('tasks', [
                'is_archived' => ['type' => 'BOOLEAN', 'default' => 0],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('tasks', 'is_archived');
    }
}
