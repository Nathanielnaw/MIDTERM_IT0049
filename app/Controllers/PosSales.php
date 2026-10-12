<?php
namespace App\Controllers;

use App\Libraries\SaleRecorder;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use RuntimeException;
use Throwable;

class PosSales extends BaseController
{
    public function index(array $values = [], array $errors = []): string
    {
        return view('pos/sale', [
            'title' => 'Record Sale', 'values' => $values, 'errors' => $errors,
            'products' => (new ProductModel())->where('is_archived', 0)->orderBy('name')->findAll(),
            'customers' => (new CustomerModel())->where('is_archived', 0)->orderBy('full_name')->findAll(),
        ]);
    }

    public function history(): string
    {
        return view('pos/history', ['title' => 'Sales History', 'sales' => (new SaleModel())->history()]);
    }

    public function create()
    {
        $data = [];
        foreach (['product_id', 'customer_id', 'quantity'] as $field) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? $value : '';
        }
        if (! $this->validateData($data, [
            'product_id' => 'required|is_natural_no_zero',
            'customer_id' => 'permit_empty|is_natural_no_zero',
            'quantity' => 'required|is_natural_no_zero|less_than_equal_to[1000000]',
        ])) {
            return $this->index($data, $this->validator->getErrors());
        }
        try {
            (new SaleRecorder(db_connect()))->record((int) $data['product_id'], $data['customer_id'] === '' ? null : (int) $data['customer_id'], (int) session()->get('auth_user')['id'], (int) $data['quantity']);
        } catch (Throwable $error) {
            $message = $error instanceof RuntimeException && get_class($error) === RuntimeException::class
                ? $error->getMessage() : 'The sale could not be saved. No stock was deducted; please retry.';
            return $this->index($data, [$message]);
        }
        return redirect()->to(site_url('pos/sales'))->with('message', 'Sale recorded and stock updated.');
    }
}
