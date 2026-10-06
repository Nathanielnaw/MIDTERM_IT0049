<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<p class="muted">Complete task history</p><h1>Task List</h1><p class="muted">All tasks ordered by scheduled date.</p>
<section class="card"><table><thead><tr><th>Task</th><th>Status</th><th>Task date</th></tr></thead><tbody>
<?php foreach ($tasks as $task): ?><tr><td><?= esc($task['title']) ?></td><td><span class="badge <?= $task['status'] === 'completed' ? 'completed' : '' ?>"><?= esc(ucfirst($task['status'])) ?></span></td><td><?= esc(date('M j, Y', strtotime($task['task_date']))) ?></td></tr><?php endforeach; ?>
</tbody></table></section>
<?= $this->endSection() ?>
