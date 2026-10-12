<?php
namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;
use RuntimeException;
use Throwable;

class SaleRecorder
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function record(int $productId, ?int $customerId, int $staffId, int $quantity): void
    {
        if ($quantity < 1 || $quantity > 1000000) {
            throw new RuntimeException('Quantity must be a positive whole number up to 1,000,000.');
        }
        if (! $this->db->transBegin()) {
            throw new RuntimeException('Could not start the sale transaction. Please retry.');
        }
        try {
            $product = $this->db->table('products')->where('id', $productId)->where('is_archived', 0)->get()->getRowArray();
            if ($product === null) {
                throw new RuntimeException('Select an available product.');
            }
            if (! $this->db->table('users')->where('id', $staffId)->where('is_archived', 0)->countAllResults()) {
                throw new RuntimeException('Your staff account is no longer active.');
            }
            if ($customerId !== null && ! $this->db->table('customers')->where('id', $customerId)->where('is_archived', 0)->countAllResults()) {
                throw new RuntimeException('Select an active customer or leave it unassigned.');
            }
            // Integer cents avoid floating point multiplication and rounding drift.
            $price = number_format((float) $product['price'], 2, '.', '');
            $cents = (int) str_replace('.', '', $price);
            $total = $cents * $quantity;
            if ($total > 9999999999) {
                throw new RuntimeException('This sale exceeds the maximum total of 99,999,999.99.');
            }
            // The conditional write serializes competing deductions. A price change
            // since the read also forces retry rather than charging a stale price.
            $updated = $this->db->table('products')->where('id', $productId)->where('is_archived', 0)
                ->where('price', $product['price'])->where('stock_quantity >=', $quantity)
                ->set('stock_quantity', 'stock_quantity - ' . $quantity, false)->update();
            if (! $updated || $this->db->affectedRows() !== 1) {
                throw new RuntimeException('Insufficient stock or the product changed. Refresh and try again.');
            }
            $saved = $this->db->table('sales')->insert([
                'product_id' => $productId, 'customer_id' => $customerId, 'sold_by' => $staffId,
                'quantity' => $quantity, 'total_price' => intdiv($total, 100) . '.' . str_pad((string) ($total % 100), 2, '0', STR_PAD_LEFT),
                'created_at' => Time::now(config('App')->appTimezone)->format('Y-m-d H:i:s'),
            ]);
            if (! $saved || ! $this->db->transStatus() || ! $this->db->transCommit()) {
                throw new RuntimeException('The sale could not be saved. Please try again.');
            }
        } catch (Throwable $error) {
            $this->db->transRollback();
            $this->db->resetTransStatus();
            throw $error;
        }
    }
}
