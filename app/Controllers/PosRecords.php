<?php
namespace App\Controllers;

use App\Libraries\PosImages;
use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;
use RuntimeException;

class PosRecords extends BaseController
{
    private function definition(string $resource): array
    {
        return match ($resource) {
            'products' => [new ProductModel(), ['name' => 'Name', 'price' => 'Price', 'stock_quantity' => 'Stock quantity'], 'image'],
            'customers' => [new CustomerModel(), ['full_name' => 'Full name', 'email' => 'Email', 'phone' => 'Phone'], null],
            'staff' => [new UserModel(), ['username' => 'Username', 'full_name' => 'Full name', 'password' => 'Password'], 'avatar'],
            default => throw PageNotFoundException::forPageNotFound(),
        };
    }

    private function record(string $resource, int $id): array
    {
        [$model] = $this->definition($resource);
        if ($resource === 'staff') {
            $model->select('id, username, full_name, avatar, created_at, is_archived');
        }
        return $model->where('is_archived', 0)->find($id) ?? throw PageNotFoundException::forPageNotFound('Record not found or archived.');
    }

    public function index(string $resource): string
    {
        [$model, $fields, $image] = $this->definition($resource);
        unset($fields['password']);
        if ($resource === 'staff') {
            $model->select('id, username, full_name, avatar, created_at');
        }
        return view('pos/records', ['title' => ucfirst($resource), 'resource' => $resource, 'fields' => $fields, 'imageField' => $image,
            'records' => $model->where('is_archived', 0)->orderBy('id', 'DESC')->findAll()]);
    }

    public function form(string $resource, ?int $id = null, array $values = [], array $errors = []): string
    {
        [, $fields, $image] = $this->definition($resource);
        $record = $id === null ? [] : $this->record($resource, $id);
        unset($values['password']);
        return view('pos/form', ['title' => ($id === null ? 'New ' : 'Edit ') . rtrim(ucfirst($resource), 's'),
            'resource' => $resource, 'fields' => $fields, 'imageField' => $image, 'id' => $id,
            'values' => array_merge($record, $values), 'errors' => $errors]);
    }

    public function save(string $resource, ?int $id = null)
    {
        [$model, $fields, $image] = $this->definition($resource);
        if ($id !== null) {
            $this->record($resource, $id);
        }
        $data = [];
        foreach ($fields as $field => $label) {
            $value = $this->request->getPost($field);
            $data[$field] = is_string($value) ? ($field === 'password' ? $value : trim($value)) : '';
        }
        $rules = match ($resource) {
            'products' => ['name' => 'required|max_length[100]', 'price' => 'required|regex_match[/^\d{1,8}(\.\d{1,2})?$/]|greater_than[0]|less_than_equal_to[99999999.99]', 'stock_quantity' => 'required|is_natural|less_than_equal_to[1000000]'],
            'customers' => ['full_name' => 'required|max_length[100]', 'email' => 'required|valid_email|max_length[100]', 'phone' => 'permit_empty|max_length[20]|regex_match[/^[0-9+() .-]+$/]'],
            'staff' => ['username' => 'required|regex_match[/^[a-zA-Z0-9_.-]+$/]|max_length[50]|is_unique[users.username,id,' . ($id ?? 0) . ']', 'full_name' => 'required|max_length[100]', 'password' => ($id === null ? 'required' : 'permit_empty') . '|min_length[12]|max_length[72]'],
        };
        if (! $this->validateData($data, $rules)) {
            return $this->form($resource, $id, $data, $this->validator->getErrors());
        }
        if ($resource === 'staff') {
            if (strlen($data['password']) > 72 || str_contains($data['password'], "\0")) {
                return $this->form($resource, $id, $data, ['Passwords must be at most 72 bytes and cannot contain null characters.']);
            }
            if ($data['password'] === '') {
                unset($data['password']);
            } else {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            if ($id === null) {
                // Retain the earlier task-profile schema's required email field.
                $data['email'] = '';
            }
        }
        if ($image !== null) {
            try {
                $filename = (new PosImages())->store($this->request->getFile($image));
                if ($filename !== null) {
                    $data[$image] = $filename;
                }
            } catch (RuntimeException $error) {
                return $this->form($resource, $id, $data, [$error->getMessage()]);
            }
        }
        if ($id === null) {
            $data['created_at'] = Time::now(config('App')->appTimezone)->format('Y-m-d H:i:s');
            $model->insert($data);
        } else {
            $model->update($id, $data);
            if ($resource === 'staff' && $id === (int) session()->get('auth_user')['id']) {
                session()->set('auth_user', ['id' => $id, 'username' => $data['username'], 'full_name' => $data['full_name']]);
            }
        }
        return redirect()->to(site_url('pos/' . $resource))->with('message', 'Record saved.');
    }

    public function archive(string $resource, int $id)
    {
        [$model] = $this->definition($resource);
        $this->record($resource, $id);
        if ($resource === 'staff' && $id === (int) session()->get('auth_user')['id']) {
            return redirect()->to(site_url('pos/staff'))->with('message', 'You cannot archive your own signed-in account.');
        }
        $model->update($id, ['is_archived' => 1]);
        return redirect()->to(site_url('pos/' . $resource))->with('message', 'Record archived. Sales history has been preserved.');
    }
}
