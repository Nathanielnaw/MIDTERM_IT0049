<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the TFA4 credential column to databases created by the TFA3 schema.
 *
 * Existing rows are deliberately not assigned a shared or hard-coded password.
 * Run the seeder after this migration with TFA4_DEMO_PASSWORD set so every
 * account receives a hash generated at runtime.
 */
class AddPasswordToExistingUsers extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('password', 'users')) {
            $this->forge->addColumn('users', [
                'password' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('password', 'users')) {
            $this->forge->dropColumn('users', 'password');
        }
    }
}
