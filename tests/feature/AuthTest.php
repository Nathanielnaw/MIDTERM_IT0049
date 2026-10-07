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

    protected function setUp(): void
    {
        parent::setUp();
        (new UserModel())->insert([
            'username' => 'staff.user',
            'full_name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => password_hash('correct-password', PASSWORD_DEFAULT),
            'created_at' => '2026-10-07 00:00:00',
        ]);
    }

    public function testProtectedProfileRedirectsWhileLoggedOut(): void
    {
        $result = $this->get('/profile');

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
            'password' => 'correct-password',
            'redirect' => '/profile',
            csrf_token() => csrf_hash(),
        ]);

        $login->assertRedirectTo('/profile');
        $this->assertSame('staff.user', session()->get('auth_user')['username']);

        $profile = $this->withSession(['auth_user' => session()->get('auth_user')])->get('/profile');
        $profile->assertStatus(200);
        $profile->assertSee('Staff User');
    }

    public function testLogoutDestroysSessionAndBlocksProfileAgain(): void
    {
        $sessionUser = ['id' => 1, 'username' => 'staff.user', 'full_name' => 'Staff User'];
        $logout = $this->withSession(['auth_user' => $sessionUser])->post('/logout', [
            csrf_token() => csrf_hash(),
        ]);

        $logout->assertRedirectTo('/login');
        $this->assertSame('/login', parse_url((string) $logout->response()->getHeaderLine('Location'), PHP_URL_PATH));
    }
}
