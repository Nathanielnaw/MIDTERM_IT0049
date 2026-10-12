<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><h1><?= esc($title) ?></h1></div>
<section class="panel task-form">
    <?php if ($errors !== []): ?>
        <div class="form-error" role="alert"><p>Please correct the following:</p><ul>
        <?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?>
        </ul></div>
    <?php endif; ?>
    <form method="post" action="<?= esc(site_url(isset($task['id']) ? 'tasks/' . $task['id'] . '/update' : 'tasks/create'), 'attr') ?>">
        <?= csrf_field() ?>
        <label for="title">Title (required)</label>
        <input id="title" name="title" required maxlength="150" value="<?= esc($task['title'], 'attr') ?>">
        <label for="task_date">Scheduled date (required)</label>
        <input id="task_date" name="task_date" type="date" required value="<?= esc($task['task_date'], 'attr') ?>">
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach (['pending', 'completed'] as $status): ?>
            <option value="<?= esc($status, 'attr') ?>" <?= $task['status'] === $status ? 'selected' : '' ?>><?= esc(ucfirst($status)) ?></option>
            <?php endforeach; ?>
        </select>
        <div class="form-actions"><button type="submit">Save task</button><a href="<?= esc(site_url('tasks'), 'attr') ?>">Cancel</a></div>
    </form>
</section>
<?= $this->endSection() ?>
