<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<p class="muted">Welcome back</p>
<h1>Today’s tasks</h1>
<p class="muted"><?= esc($today) ?> · <?= count($tasks) ?> task(s) scheduled</p>
<section class="card">
<?php if ($tasks): ?>
<table><thead><tr><th>Task</th><th>Status</th><th>Created</th></tr></thead><tbody>
<?php foreach ($tasks as $task): ?><tr><td><?= esc($task['title']) ?></td><td><span class="badge <?= $task['status'] === 'completed' ? 'completed' : '' ?>"><?= esc(ucfirst($task['status'])) ?></span></td><td><?= esc(date('g:i A', strtotime($task['created_at']))) ?></td></tr><?php endforeach; ?>
</tbody></table>
<?php else: ?><p>No tasks are scheduled for today.</p><?php endif; ?>
</section>
<?= $this->endSection() ?>
