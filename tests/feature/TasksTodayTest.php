<?php

namespace Tests\Feature;

use App\Database\Seeds\TasksTodaySeeder;
use App\Models\TaskModel;
use App\Models\UserModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

final class TasksTodayTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    protected function tearDown(): void
    {
        Time::setTestNow();
        parent::tearDown();
    }

    private function addTask(string $title, string $date, string $status = 'pending'): int
    {
        return (int) (new TaskModel())->insert([
            'title' => $title,
            'status' => $status,
            'task_date' => $date,
            'created_at' => '2026-10-06 08:00:00',
        ]);
    }

    public function testTodayChangesAtMidnightInConfiguredTimezone(): void
    {
        config('App')->appTimezone = 'Asia/Manila';
        $this->addTask('Previous day task', '2026-10-06');
        $this->addTask('Today pending task', '2026-10-07');
        $this->addTask('Today completed task', '2026-10-07', 'completed');
        $this->addTask('Tomorrow task', '2026-10-08');

        // UTC is still October 6, but Manila has just crossed midnight.
        Time::setTestNow('2026-10-06 16:00:00', 'UTC');
        $result = $this->get('/');
        $result->assertStatus(200);
        $result->assertSee('Today pending task');
        $result->assertSee('Today completed task');
        $result->assertDontSee('Previous day task');
        $result->assertDontSee('Tomorrow task');
        $result->assertSee('Oct 7, 2026');

        Time::setTestNow('2026-10-06 15:59:59', 'UTC');
        $beforeMidnight = $this->get('/');
        $beforeMidnight->assertSee('Previous day task');
        $beforeMidnight->assertDontSee('Today pending task');
    }

    public function testTaskListIncludesAllDatesWithStableOrder(): void
    {
        $later = $this->addTask('Later task', '2026-10-09');
        $first = $this->addTask('Earlier first task', '2026-10-05');
        $second = $this->addTask('Earlier second task', '2026-10-05');
        $this->assertSame([$first, $second, $later], array_map('intval', array_column((new TaskModel())->allOrderedByDate(), 'id')));

        $result = $this->get('/tasks');
        $result->assertStatus(200);
        $html = $result->response()->getBody();
        $this->assertMatchesRegularExpression('/Earlier first task.*Earlier second task.*Later task/s', $html);
    }

    public function testProfileSelectsOnlyTheDemoUserEvenIfAnotherUserIsFirst(): void
    {
        (new UserModel())->insert(['username' => 'someone.else', 'full_name' => 'Another Person', 'email' => 'other@example.com', 'created_at' => '2026-01-01 00:00:00']);
        $this->seed(TasksTodaySeeder::class);
        $result = $this->get('/profile');
        $result->assertStatus(200);
        $result->assertSee('Demo Student');
        $result->assertSee('demo.student');
        $result->assertSee('student@example.com');
        $result->assertDontSee('Another Person');
        $this->assertSame('demo.student', (new UserModel())->demoUser()['username']);
    }

    public function testSeederCanBeRepeatedWithoutDuplicatesOrOverwritingEdits(): void
    {
        Time::setTestNow('2026-10-06 16:00:00', 'UTC');
        $this->seed(TasksTodaySeeder::class);
        $task = (new TaskModel())->first();
        (new TaskModel())->update($task['id'], ['title' => 'Keep my edit']);
        $this->seed(TasksTodaySeeder::class);
        $this->assertSame(8, (new TaskModel())->countAllResults());
        $this->assertSame(1, (new UserModel())->countAllResults());
        $this->assertSame('Keep my edit', (new TaskModel())->find($task['id'])['title']);
        $this->assertCount(4, (new TaskModel())->forDate('2026-10-07'));
        $this->assertCount(2, (new TaskModel())->forDate('2026-10-08'));
        $this->assertCount(2, (new TaskModel())->forDate('2026-10-09'));
    }

    public function testSeederRepairsMissingUserWithoutChangingTasks(): void
    {
        $this->addTask('Existing task', '2026-09-01');
        $this->seed(TasksTodaySeeder::class);
        $this->assertSame(1, (new TaskModel())->countAllResults());
        $this->assertSame(1, (new UserModel())->countAllResults());
    }

    public function testSeederFillsTasksWhenDemoUserAlreadyExists(): void
    {
        (new UserModel())->insert(['username' => 'demo.student', 'full_name' => 'Updated Demo', 'email' => 'demo@example.com', 'created_at' => '2026-01-01 00:00:00']);
        $this->seed(TasksTodaySeeder::class);
        $this->assertSame(8, (new TaskModel())->countAllResults());
        $this->assertSame(1, (new UserModel())->countAllResults());
        $this->assertSame('Updated Demo', (new UserModel())->demoUser()['full_name']);
    }

    public function testEmptyStatesRenderSuccessfully(): void
    {
        $today = $this->get('/');
        $today->assertStatus(200);
        $today->assertSee('No tasks are scheduled for today.');
        $tasks = $this->get('/tasks');
        $tasks->assertStatus(200);
        $tasks->assertSee('No tasks have been added yet.');
        $profile = $this->get('/profile');
        $profile->assertStatus(200);
        $profile->assertSee('No demo user found.');
    }

    public function testDatabaseTextIsEscapedInViews(): void
    {
        $this->addTask('<script>alert("task")</script>', '2026-10-07', '<b>pending</b>');
        $result = $this->get('/tasks');
        $result->assertStatus(200);
        $html = $result->response()->getBody();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('<b>pending</b>', $html);
    }

    public function testAboutIdentifiesDeveloperWithoutDatabaseRows(): void
    {
        $result = $this->get('/about');
        $result->assertStatus(200);
        $result->assertSee('Alfred Nathaniel Embuscado');
        $result->assertSee('IT0049 Technical Summative Assessment 1');
        $html = html_entity_decode($result->response()->getBody(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $this->assertMatchesRegularExpression('/href="[^"]*\/about" aria-current="page"/', $html);
    }
}
