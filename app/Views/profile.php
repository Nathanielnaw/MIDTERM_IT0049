<?= $this->extend('layout') ?>
<?= $this->section('content') ?>
<div class="page-heading"><div><p class="eyebrow">YOUR CORNER</p><h1>Profile</h1><p class="subtitle">A familiar face behind the to-do list.</p></div><span class="date-chip">Demo account</span></div>
<section class="panel profile-panel" aria-label="Demo user profile">
<?php if ($user !== null): ?>
    <div class="profile-banner"><span class="avatar avatar-large" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($user['full_name'], 0, 1))) ?></span><div><span class="eyebrow">HELLO THERE</span><h2><?= esc($user['full_name']) ?></h2><p>@<?= esc($user['username']) ?></p></div><span class="badge completed">Demo user</span></div>
    <dl class="profile-details"><div><dt>Full name</dt><dd><?= esc($user['full_name']) ?></dd></div><div><dt>Username</dt><dd><?= esc($user['username']) ?></dd></div><div><dt>Email address</dt><dd><?= esc($user['email']) ?></dd></div><div><dt>Member since</dt><dd><?= esc((new DateTimeImmutable($user['created_at']))->format('F j, Y')) ?></dd></div></dl>
<?php else: ?>
    <div class="empty-state"><h2>No demo user found.</h2><p>The demo profile will appear once the database setup is complete.</p></div>
<?php endif; ?>
</section>
<?= $this->endSection() ?>
