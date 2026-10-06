<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<?php $completed = count(array_filter($tasks, static fn ($task) => $task['status'] === 'completed')); ?>
<div class="page-heading"><div><p class="eyebrow">WELCOME BACK</p><h1>Let’s make today count.</h1><p class="subtitle">A fresh day. A clear plan. Here’s what’s on your list.</p></div><span class="date-chip"><?= esc($today->format('D, M j, Y')) ?></span></div>
<section class="stats" aria-label="Today's task summary">
    <div class="stat"><span>Scheduled today</span><strong><?= count($tasks) ?><small>tasks on your list</small></strong></div>
    <div class="stat"><span>Completed</span><strong><?= $completed ?><small>one step closer</small></strong></div>
    <div class="stat"><span>Remaining</span><strong><?= count($tasks) - $completed ?><small>let’s keep going</small></strong></div>
</section>
<section class="panel" aria-labelledby="today-heading">
    <div class="panel-heading"><div><h2 id="today-heading">Today’s tasks <span class="count-pill"><?= count($tasks) ?></span></h2><p>Just what’s scheduled for today.</p></div><a class="text-link" href="<?= esc(site_url('tasks'), 'attr') ?>">View all tasks <span aria-hidden="true">↗</span></a></div>
    <?= $this->include('partials/task_table') ?>
    <div class="panel-foot">Your schedule follows <?= esc($today->getTimezone()->getName()) ?> time.</div>
</section>
<div class="gentle-note"><span aria-hidden="true">✳</span><p>Small steps, steady progress.<small>You don’t have to do it all at once.</small></p></div>
<?= $this->endSection() ?>
