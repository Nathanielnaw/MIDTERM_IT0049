<?php

namespace Tests\Feature;

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class AuthTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';
    private string $password;

    protected function setUp(): void
    {
        parent::setUp();
        $this->password = bin2hex(random_bytes(24));
        (new UserModel())->insert([
            'username' => 'staff.user',
            'full_name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => password_hash($this->password, PASSWORD_DEFAULT),
            'created_at' => '2026-10-07 00:00:00',
        ]);
    }

    public function testProtectedTaskFormRedirectsWhileLoggedOut(): void
    {
        $result = $this->get('/tasks/new');

        $result->assertRedirect();
        $this->assertStringContainsString('/login?redirect=', (string) $result->response()->getHeaderLine('Location'));
    }

    public function testInvalidCredentialsUseGenericError(): void
    {
        $result = $this->post('/login', [
            'username' => 'staff.user',
            'password' => 'wrong-password',
            csrf_token() => csrf_hash(),
        ]);

        $result->assertStatus(200);
        $result->assertSee('The username or password is incorrect.');
        $result->assertDontSee('$2y$');
    }

    public function testCorrectPasswordLogsInAndCanReachProtectedPage(): void
    {
        $login = $this->post('/login', [
            'username' => 'staff.user',
            'password' => $this->password,
            'redirect' => '/tasks/new',
            csrf_token() => csrf_hash(),
        ]);

        $login->assertRedirectTo('/tasks/new');
        $this->assertSame('staff.user', session()->get('auth_user')['username']);
        $this->assertSame(['id', 'username', 'full_name'], array_keys(session()->get('auth_user')));

        $profile = $this->withSession(['auth_user' => session()->get('auth_user')])->get('/tasks/new');
        $profile->assertStatus(200);
        $profile->assertSee('New Task');
    }

    public function testLogoutClearsSession(): void
    {
        $sessionUser = ['id' => 1, 'username' => 'staff.user', 'full_name' => 'Staff User'];
        $logout = $this->withSession(['auth_user' => $sessionUser])->post('/logout', [
            csrf_token() => csrf_hash(),
        ]);

        $logout->assertRedirectTo('/login');
        $this->assertNull(session()->get('auth_user'));
        $this->assertSame('/login', parse_url((string) $logout->response()->getHeaderLine('Location'), PHP_URL_PATH));
    }

    public function testUnknownUserHasSameErrorAndCannotRedirectOffsite(): void
    {
        $result = $this->post('/login', ['username' => 'missing', 'password' => $this->password, csrf_token() => csrf_hash()]);
        $result->assertSee('The username or password is incorrect.');
        $this->assertNull(session()->get('auth_user'));
        $login = $this->post('/login', ['username' => 'staff.user', 'password' => $this->password, 'redirect' => '//example.org', csrf_token() => csrf_hash()]);
        $login->assertRedirectTo('/');
    }
}
