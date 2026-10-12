<?php

namespace App\Controllers;

use App\Models\TaskModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

class Tasks extends BaseController
{
    public function index(): string
    {
        return view('tasks', [
            'title' => 'Task List',
            'tasks' => (new TaskModel())->allOrderedByDate(),
        ]);
    }

    public function new(): string
    {
        return $this->form(['title' => '', 'task_date' => '', 'status' => 'pending']);
    }

    public function create()
    {
        return $this->saveTask();
    }

    public function edit(int $id): string
    {
        return $this->form($this->activeTask($id));
    }

    public function update(int $id)
    {
        return $this->saveTask($this->activeTask($id));
    }

    public function archive(int $id)
    {
        $this->activeTask($id);
        (new TaskModel())->where('is_archived', 0)->update($id, ['is_archived' => 1]);

        return redirect()->to(site_url('tasks'))->with('message', 'Task archived.');
    }

    private function activeTask(int $id): array
    {
        $task = (new TaskModel())->where('is_archived', 0)->find($id);
        if ($task === null) {
            throw PageNotFoundException::forPageNotFound('Task not found or already archived.');
        }

        return $task;
    }

    private function form(array $task, array $errors = []): string
    {
        return view('task_form', ['title' => isset($task['id']) ? 'Edit Task' : 'New Task', 'task' => $task, 'errors' => $errors]);
    }

    private function saveTask(?array $existing = null)
    {
        $data = [
            'title' => $this->request->getPost('title'),
            'task_date' => $this->request->getPost('task_date'),
            'status' => $this->request->getPost('status') ?? 'pending',
        ];
        if (is_string($data['title'])) {
            $data['title'] = trim($data['title']);
        }
        if (! $this->validateData($data, [
            'title' => 'required|max_length[150]',
            'task_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[pending,completed]',
        ])) {
            // Never render arrays supplied in place of form strings.
            $values = array_map(static fn ($value) => is_string($value) ? $value : '', $data);
            return $this->form(array_merge($existing ?? [], $values), $this->validator->getErrors());
        }

        $model = new TaskModel();
        if ($existing === null) {
            $data['created_at'] = Time::now(config('App')->appTimezone)->format('Y-m-d H:i:s');
            $model->insert($data);
        } else {
            $model->where('is_archived', 0)->update($existing['id'], $data);
        }

        return redirect()->to(site_url('tasks'))->with('message', 'Task saved.');
    }
}
