<?php if ($tasks === []): ?>
    <div class="empty-state"><span aria-hidden="true">✓</span><h3><?= $title === 'Tasks for Today' ? 'A little breathing room.' : 'Your list is clear.' ?></h3><p><?= $title === 'Tasks for Today' ? 'No tasks are scheduled for today.' : 'No tasks have been added yet.' ?></p></div>
<?php else: ?>
    <div class="table-scroll">
        <table>
            <caption class="sr-only"><?= $title === 'Tasks for Today' ? 'Tasks scheduled for today' : 'All tasks ordered by task date and ID' ?></caption>
            <thead><tr><th scope="col">Task</th><th scope="col">Scheduled date</th><th scope="col">Status</th></tr></thead>
            <tbody>
            <?php foreach ($tasks as $task): ?>
                <tr>
                    <td><div class="task-name"><span class="task-indicator <?= $task['status'] === 'completed' ? 'is-complete' : '' ?>" aria-hidden="true"><?= $task['status'] === 'completed' ? '✓' : '' ?></span><span><?= esc($task['title']) ?><small>Task #<?= esc($task['id']) ?></small></span></div></td>
                    <td class="task-date"><time datetime="<?= esc($task['task_date'], 'attr') ?>"><?= esc((new DateTimeImmutable($task['task_date']))->format('M j, Y')) ?></time></td>
                    <td><span class="badge <?= $task['status'] === 'completed' ? 'completed' : 'pending' ?>"><span aria-hidden="true">•</span> <?= esc(ucfirst($task['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
