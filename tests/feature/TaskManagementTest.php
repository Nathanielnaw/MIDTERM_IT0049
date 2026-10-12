<?php

namespace Tests\Feature;

use App\Models\TaskModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class TaskManagementTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    private function assertMissing(callable $request): void
    {
        try {
            $request();
            $this->fail('Expected a missing task or route.');
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $exception) {
            $this->assertSame(404, $exception->getCode());
        }
    }

    private function login(): void
    {
        $this->withSession(['auth_user' => ['id' => 1, 'username' => 'demo.student', 'full_name' => 'Demo Student']]);
    }

    private function submit(string $route, array $data = [])
    {
        return $this->post($route, $data + [csrf_token() => csrf_hash()]);
    }

    public function testPublicPagesAndProtectedActions(): void
    {
        foreach (['/', '/tasks', '/profile', '/about'] as $route) {
            $result = $this->withSession([])->get($route);
            $result->assertStatus(200);
            $result->assertDontSee('+ New Task');
        }
        foreach (['/tasks/new', '/tasks/1/edit'] as $route) {
            $this->withSession([])->get($route)->assertRedirect();
        }
        foreach (['/tasks/create', '/tasks/1/update', '/tasks/1/archive'] as $route) {
            $this->withSession([]);
            $result = $this->submit($route);
            $result->assertRedirect();
            $this->assertStringContainsString('/login', $result->response()->getHeaderLine('Location'));
        }
        $this->assertSame(0, (new TaskModel())->countAllResults());
    }

    public function testCreateEditAndArchiveKeepsRowButHidesTask(): void
    {
        $this->login();
        $date = Time::now(config('App')->appTimezone)->format('Y-m-d');
        $this->submit('/tasks/create', ['title' => 'Original title', 'task_date' => $date])->assertRedirectTo('/tasks');
        $task = (new TaskModel())->first();
        $id = $task['id'];
        $this->assertSame(0, (int) $task['is_archived']);
        $this->get('/tasks/' . $id . '/edit')->assertSee('Original title');
        $this->submit('/tasks/' . $id . '/update', ['title' => 'Updated title', 'task_date' => $date, 'status' => 'completed'])->assertRedirectTo('/tasks');
        $this->get('/')->assertSee('Updated title');
        $this->submit('/tasks/' . $id . '/archive')->assertRedirectTo('/tasks');
        $stored = (new TaskModel())->find($id);
        $this->assertSame(1, (int) $stored['is_archived']);
        $this->assertSame('Updated title', $stored['title']);
        $this->assertSame('completed', $stored['status']);
        $this->assertSame(1, (new TaskModel())->countAllResults());
        $this->get('/')->assertDontSee('Updated title');
        $this->get('/tasks')->assertDontSee('Updated title');
        $this->assertMissing(fn () => $this->get('/tasks/' . $id . '/edit'));
        $this->assertMissing(fn () => $this->submit('/tasks/' . $id . '/archive'));
        $this->assertMissing(fn () => $this->submit('/tasks/' . $id . '/update', ['title' => 'Changed', 'task_date' => $date]));
        $this->assertSame('Updated title', (new TaskModel())->find($id)['title']);
    }

    public function testValidationPreservesInputAndRejectsInvalidUpdates(): void
    {
        $this->login();
        $invalid = $this->submit('/tasks/create', ['title' => 'Keep this title', 'task_date' => '']);
        $invalid->assertSee('Keep this title');
        $invalid->assertSee('Please correct');
        foreach ([['title' => ' ', 'task_date' => '2026-10-13'], ['title' => 'Bad date', 'task_date' => '2026-02-30'], ['title' => 'Bad status', 'task_date' => '2026-10-13', 'status' => 'invalid']] as $data) {
            $this->submit('/tasks/create', $data)->assertSee('Please correct');
        }
        $this->assertSame(0, (new TaskModel())->countAllResults());
        $this->submit('/tasks/create', ['title' => 'Keep original', 'task_date' => '2026-10-13']);
        $task = (new TaskModel())->first();
        $this->submit('/tasks/' . $task['id'] . '/update', ['title' => '', 'task_date' => ''])->assertSee('Please correct');
        $this->assertSame('Keep original', (new TaskModel())->find($task['id'])['title']);
        $this->assertMissing(fn () => $this->get('/tasks/999999/edit'));
        $this->assertMissing(fn () => $this->submit('/tasks/999999/update'));
        $this->assertMissing(fn () => $this->submit('/tasks/999999/archive'));
    }

    public function testCsrfAndGetCannotMutateTasks(): void
    {
        $this->login();
        try {
            $this->post('/tasks/create', ['title' => 'Rejected', 'task_date' => '2026-10-13']);
            $this->fail('Missing CSRF token was accepted.');
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $exception) {
            $this->assertSame(403, $exception->getCode());
        }
        foreach (['/tasks/create', '/tasks/1/update', '/tasks/1/archive', '/logout'] as $route) {
            $this->assertMissing(fn () => $this->get($route));
        }
        $this->assertSame(0, (new TaskModel())->countAllResults());
    }
}
