<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePos extends Migration
{
    public function up()
    {
        $id = ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true];
        $created = ['type' => 'DATETIME'];
        $archived = ['type' => 'BOOLEAN', 'default' => 0];
        $this->forge->addColumn('users', [
            'avatar' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'is_archived' => $archived,
        ]);
        $this->forge->addField([
            'id' => $id, 'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'price' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'stock_quantity' => ['type' => 'INT', 'default' => 0],
            'image' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => $created, 'is_archived' => $archived,
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('products', false, $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : []);
        $this->forge->addField([
            'id' => $id, 'full_name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'email' => ['type' => 'VARCHAR', 'constraint' => 100],
            'phone' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at' => $created, 'is_archived' => $archived,
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('customers', false, $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : []);
        $fk = ['type' => 'INT', 'unsigned' => true];
        $this->forge->addField([
            'id' => $id, 'product_id' => $fk, 'customer_id' => $fk + ['null' => true],
            'sold_by' => $fk, 'quantity' => ['type' => 'INT'],
            'total_price' => ['type' => 'DECIMAL', 'constraint' => '10,2'], 'created_at' => $created,
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('product_id', 'products', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('sold_by', 'users', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('sales', false, $this->db->DBDriver === 'MySQLi' ? ['ENGINE' => 'InnoDB'] : []);
    }

    public function down()
    {
        $this->forge->dropTable('sales');
        $this->forge->dropTable('customers');
        $this->forge->dropTable('products');
        foreach (['avatar', 'is_archived'] as $column) {
            if ($this->db->DBDriver === 'SQLite3' && version_compare($this->db->getVersion(), '3.35', '>=')) {
                $this->db->query('ALTER TABLE ' . $this->db->protectIdentifiers($this->db->prefixTable('users')) . ' DROP COLUMN ' . $column);
                $this->db->resetDataCache();
            } else {
                $this->forge->dropColumn('users', $column);
            }
        }
    }
}
