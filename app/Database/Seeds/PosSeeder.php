<?php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;
use RuntimeException;

class PosSeeder extends Seeder
{
    public function run()
    {
        $password = (string) env('POS_DEMO_PASSWORD', '');
        $staff = $this->db->table('users')->where('username', 'pos.admin')->get()->getRowArray();
        if ($staff === null && (strlen($password) < 12 || strlen($password) > 72)) {
            throw new RuntimeException('Set a private POS_DEMO_PASSWORD of 12–72 characters before initial seeding.');
        }
        $created = Time::now(config('App')->appTimezone)->format('Y-m-d H:i:s');
        $this->db->transStart();
        if ($staff === null) {
            $this->db->table('users')->insert(['username' => 'pos.admin', 'full_name' => 'POS Administrator', 'email' => '', 'password' => password_hash($password, PASSWORD_DEFAULT), 'created_at' => $created]);
        }
        if ($this->db->table('products')->countAllResults() === 0) {
            foreach ([['Notebook', '49.50', 50], ['Ballpoint Pen', '12.00', 100], ['Desk Organizer', '199.00', 15]] as [$name, $price, $stock]) {
                $this->db->table('products')->insert(['name' => $name, 'price' => $price, 'stock_quantity' => $stock, 'created_at' => $created]);
            }
        }
        if ($this->db->table('customers')->countAllResults() === 0) {
            $this->db->table('customers')->insert(['full_name' => 'Sample Customer', 'email' => 'customer@example.com', 'phone' => '', 'created_at' => $created]);
        }
        $this->db->transComplete();
        if (! $this->db->transStatus()) {
            throw new RuntimeException('POS seeding failed; changes were rolled back.');
        }
    }
}
