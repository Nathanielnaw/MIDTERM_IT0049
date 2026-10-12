<?php
namespace Tests\Feature;

use App\Libraries\SaleRecorder;
use App\Models\ProductModel;
use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use RuntimeException;

final class PosTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;
    protected $namespace = 'App';
    private int $staffId;
    private string $password;

    protected function setUp(): void
    {
        parent::setUp();
        $this->password = bin2hex(random_bytes(16));
        $this->staffId = (int) (new UserModel())->insert(['username' => 'pos.test', 'full_name' => 'Test Staff', 'email' => '', 'password' => password_hash($this->password, PASSWORD_DEFAULT), 'created_at' => '2026-10-13 00:00:00']);
    }

    private function login(): void
    {
        $this->withSession(['auth_user' => ['id' => $this->staffId, 'username' => 'pos.test', 'full_name' => 'Test Staff']]);
    }

    private function submit(string $path, array $data = [])
    {
        return $this->post($path, $data + [csrf_token() => csrf_hash()]);
    }

    private function product(): int
    {
        return (int) (new ProductModel())->insert(['name' => 'Test Product', 'price' => '19.95', 'stock_quantity' => 5, 'created_at' => '2026-10-13 00:00:00']);
    }

    public function testEveryPosRouteRequiresActiveStaff(): void
    {
        $paths = ['/pos', '/pos/sales'];
        foreach (['products', 'customers', 'staff'] as $resource) {
            foreach (['', '/new', '/1/edit'] as $suffix) {
                $paths[] = '/pos/' . $resource . $suffix;
            }
        }
        foreach ($paths as $path) {
            $this->withSession([])->get($path)->assertRedirect();
        }
        foreach (['products', 'customers', 'staff'] as $resource) {
            foreach (['/create', '/1/update', '/1/archive'] as $suffix) {
                $this->withSession([]);
                $this->submit('/pos/' . $resource . $suffix)->assertRedirect();
            }
        }
        $this->submit('/pos/sales')->assertRedirect();
        $this->get('/login')->assertStatus(200);
        $this->login();
        (new UserModel())->update($this->staffId, ['is_archived' => 1]);
        $this->get('/pos')->assertRedirect();
        $this->submit('/login', ['username' => 'pos.test', 'password' => $this->password])->assertSee('The username or password is incorrect.');
    }

    public function testProductCustomerAndStaffCrud(): void
    {
        $this->login();
        $this->submit('/pos/products/create', ['name' => 'Notebook', 'price' => '25.50', 'stock_quantity' => '10'])->assertRedirectTo('/pos/products');
        $id = (new ProductModel())->first()['id'];
        $this->get('/pos/products/' . $id . '/edit')->assertSee('Notebook');
        $this->submit('/pos/products/' . $id . '/update', ['name' => 'Updated Notebook', 'price' => '30.25', 'stock_quantity' => '7'])->assertRedirect();
        $this->get('/pos/products')->assertSee('Updated Notebook');
        $this->submit('/pos/products/' . $id . '/archive')->assertRedirect();
        $this->assertSame(1, (int) (new ProductModel())->find($id)['is_archived']);
        $this->get('/pos/products')->assertDontSee('Updated Notebook');

        $this->submit('/pos/customers/create', ['full_name' => 'Buyer', 'email' => 'buyer@example.com', 'phone' => '+63 912 345 6789'])->assertRedirect();
        $customer = $this->db->table('customers')->get()->getRowArray();
        $this->submit('/pos/customers/' . $customer['id'] . '/update', ['full_name' => 'Updated Buyer', 'email' => 'buyer@example.com', 'phone' => ''])->assertRedirect();
        $this->get('/pos/customers')->assertSee('Updated Buyer');
        $this->submit('/pos/customers/' . $customer['id'] . '/archive')->assertRedirect();
        $this->get('/pos/customers')->assertDontSee('Updated Buyer');

        $this->submit('/pos/staff/create', ['username' => 'new.staff', 'full_name' => 'New Staff', 'password' => $this->password])->assertRedirect();
        $staff = (new UserModel())->findByUsername('new.staff');
        $this->assertTrue(password_verify($this->password, $staff['password']));
        $this->get('/pos/staff')->assertDontSee($staff['password']);
        $this->get('/pos/staff/' . $staff['id'] . '/edit')->assertDontSee($staff['password']);
        $this->submit('/pos/staff/' . $staff['id'] . '/update', ['username' => 'new.staff', 'full_name' => 'Edited Staff', 'password' => ''])->assertRedirect();
        $this->assertSame($staff['password'], (new UserModel())->find($staff['id'])['password']);
        $this->submit('/pos/staff/' . $this->staffId . '/archive')->assertRedirect();
        $this->assertSame(0, (int) (new UserModel())->find($this->staffId)['is_archived']);
        $this->submit('/pos/staff/' . $staff['id'] . '/archive')->assertRedirect();
        $this->assertNull((new UserModel())->findByUsername('new.staff'));
    }

    public function testValidationRejectsBadValuesAndDuplicateUsernames(): void
    {
        $this->login();
        foreach (['-1', '1.999', '1e2', '0'] as $price) {
            $this->submit('/pos/products/create', ['name' => 'Keep name', 'price' => $price, 'stock_quantity' => '1'])->assertSee('Please correct');
        }
        $this->submit('/pos/products/create', ['name' => '', 'price' => '1', 'stock_quantity' => '-1'])->assertSee('Please correct');
        $this->assertSame(0, (new ProductModel())->countAllResults());
        $this->submit('/pos/customers/create', ['full_name' => 'Keep name', 'email' => 'invalid'])->assertSee('Keep name');
        $this->assertSame(0, $this->db->table('customers')->countAllResults());
        $this->submit('/pos/staff/create', ['username' => 'pos.test', 'full_name' => 'Duplicate', 'password' => $this->password])->assertSee('Please correct');
        $this->assertSame(1, (new UserModel())->countAllResults());
    }

    public function testSaleCalculatesTotalAndPreservesHistoryAfterArchiving(): void
    {
        $this->login();
        $id = $this->product();
        $this->submit('/pos/sales', ['product_id' => (string) $id, 'quantity' => '2', 'total_price' => '0.01', 'sold_by' => '999'])->assertRedirectTo('/pos/sales');
        $sale = $this->db->table('sales')->get()->getRowArray();
        $this->assertEquals(39.90, $sale['total_price']);
        $this->assertSame($this->staffId, (int) $sale['sold_by']);
        $this->assertNull($sale['customer_id']);
        $this->assertSame(3, (int) (new ProductModel())->find($id)['stock_quantity']);
        $this->submit('/pos/products/' . $id . '/archive');
        $history = $this->get('/pos/sales');
        $history->assertSee('Test Product');
        $history->assertSee('Test Staff');
        $history->assertSee('Walk-in / unassigned');
        $history->assertSee('39.90');
    }

    public function testOversellingAndInvalidSaleDoNotChangeStock(): void
    {
        $this->login();
        $id = $this->product();
        $this->submit('/pos/sales', ['product_id' => (string) $id, 'quantity' => '6'])->assertSee('Insufficient stock');
        foreach (['0', '-1', '1.5'] as $quantity) {
            $this->submit('/pos/sales', ['product_id' => (string) $id, 'quantity' => $quantity])->assertStatus(200);
        }
        $this->submit('/pos/sales', ['product_id' => (string) $id, 'quantity' => '1', 'customer_id' => '999'])->assertSee('Select an active customer');
        $this->assertSame(5, (int) (new ProductModel())->find($id)['stock_quantity']);
        $this->assertSame(0, $this->db->table('sales')->countAllResults());
        $this->submit('/pos/sales', ['product_id' => (string) $id, 'quantity' => '5'])->assertRedirect();
        $this->submit('/pos/sales', ['product_id' => (string) $id, 'quantity' => '1'])->assertSee('Insufficient stock');
        $this->assertSame(0, (int) (new ProductModel())->find($id)['stock_quantity']);
        $this->assertSame(1, $this->db->table('sales')->countAllResults());
    }

    public function testFailedSaleInsertRollsBackStock(): void
    {
        $id = $this->product();
        $table = $this->db->prefixTable('sales');
        $this->db->query("CREATE TRIGGER fail_sale BEFORE INSERT ON $table BEGIN SELECT RAISE(ABORT, 'test failure'); END");
        try {
            (new SaleRecorder($this->db))->record($id, null, $this->staffId, 2);
            $this->fail('Insert should have failed.');
        } catch (\Throwable $error) {
            $this->assertNotInstanceOf(\PHPUnit\Framework\AssertionFailedError::class, $error);
        } finally {
            $this->db->query('DROP TRIGGER fail_sale');
        }
        $this->assertSame(5, (int) (new ProductModel())->find($id)['stock_quantity']);
        $this->assertSame(0, $this->db->table('sales')->countAllResults());
    }

    public function testMissingRecordAndCsrfRejection(): void
    {
        $this->login();
        foreach (['products', 'customers', 'staff'] as $resource) {
            try {
                $this->get('/pos/' . $resource . '/99999/edit');
                $this->fail('Expected missing record.');
            } catch (\CodeIgniter\Exceptions\PageNotFoundException $error) {
                $this->assertSame(404, $error->getCode());
            }
        }
        $this->expectException(\CodeIgniter\Security\Exceptions\SecurityException::class);
        $this->post('/pos/sales', []);
    }

    public function testSeederIsRepeatableAndPreservesCredentials(): void
    {
        $_ENV['POS_DEMO_PASSWORD'] = $this->password;
        try {
            $this->seed(\App\Database\Seeds\PosSeeder::class);
            $staff = (new UserModel())->findByUsername('pos.admin');
            $this->assertTrue(password_verify($this->password, $staff['password']));
            $this->seed(\App\Database\Seeds\PosSeeder::class);
            $this->assertSame($staff['password'], (new UserModel())->findByUsername('pos.admin')['password']);
            $this->assertSame(3, (new ProductModel())->countAllResults());
            $this->assertSame(1, $this->db->table('customers')->countAllResults());
        } finally {
            unset($_ENV['POS_DEMO_PASSWORD']);
        }
    }

    public function testAssignedCustomerAndChangedStaffPassword(): void
    {
        $this->login();
        $this->submit('/pos/customers/create', ['full_name' => 'Assigned Buyer', 'email' => 'assigned@example.com']);
        $customer = $this->db->table('customers')->get()->getRowArray();
        $id = $this->product();
        $this->submit('/pos/sales', ['product_id' => (string) $id, 'quantity' => '1', 'customer_id' => (string) $customer['id']])->assertRedirect();
        $this->get('/pos/sales')->assertSee('Assigned Buyer');
        $newPassword = bin2hex(random_bytes(16));
        $this->submit('/pos/staff/' . $this->staffId . '/update', ['username' => 'pos.test', 'full_name' => 'Updated Staff', 'password' => $newPassword])->assertRedirect();
        $this->assertTrue(password_verify($newPassword, (new UserModel())->find($this->staffId)['password']));
        $this->submit('/pos/customers/' . $customer['id'] . '/archive');
        $this->get('/pos/sales')->assertSee('Assigned Buyer');
    }
}
