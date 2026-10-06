<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<p class="muted">Demo user account</p><h1>Profile</h1>
<section class="card profile">
<?php if ($user): ?><dl><dt>Username</dt><dd><?= esc($user['username']) ?></dd><dt>Full name</dt><dd><?= esc($user['full_name']) ?></dd><dt>Email</dt><dd><?= esc($user['email']) ?></dd><dt>Member since</dt><dd><?= esc(date('F j, Y', strtotime($user['created_at']))) ?></dd></dl>
<?php else: ?><p>No demo user found. Run the database seeder first.</p><?php endif; ?>
</section>
<?= $this->endSection() ?>
