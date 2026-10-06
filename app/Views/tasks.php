<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><p class="eyebrow">THE BIG PICTURE</p><h1>Task List</h1><p class="subtitle">Everything on your schedule, all in one place.</p></div><span class="date-chip"><?= count($tasks) ?> total tasks</span></div>
<section class="panel" aria-labelledby="all-heading">
    <div class="panel-heading"><div><h2 id="all-heading">All tasks <span class="count-pill"><?= count($tasks) ?></span></h2><p>Ordered by scheduled date, earliest first.</p></div><span class="sort-label">Date ↑</span></div>
    <?= $this->include('partials/task_table') ?>
    <div class="panel-foot">A little planning goes a long way.</div>
</section>
<?= $this->endSection() ?>
