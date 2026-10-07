<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="A little clarity for your day. View your scheduled tasks and keep track of what matters.">
    <title><?= esc($title) ?> | Tasks for Today</title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/app.css'), 'attr') ?>">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<aside class="sidebar">
    <a class="brand" href="<?= esc(site_url('/'), 'attr') ?>"><span class="brand-mark" aria-hidden="true">✓</span><span>Tasks for Today<small>A little clarity for your day.</small></span></a>
    <p class="nav-label">YOUR WORKSPACE</p>
    <nav aria-label="Main navigation">
        <?php foreach ([['/', 'Tasks for Today', 'Today', '◷'], ['tasks', 'Task List', 'Task List', '≡'], ['profile', 'Profile', 'Profile', '○'], ['about', 'About', 'About', 'i']] as [$path, $pageTitle, $label, $icon]): ?>
            <a href="<?= esc(site_url($path), 'attr') ?>" <?= $title === $pageTitle ? 'aria-current="page"' : '' ?>><span class="nav-icon" aria-hidden="true"><?= esc($icon) ?></span><?= esc($label) ?><?php if ($title === $pageTitle): ?><span class="nav-dot" aria-hidden="true"></span><?php endif; ?></a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-note"><span class="note-star" aria-hidden="true">✳</span><p>One task at a time.</p><small>Make room for what matters.<br>A focused day starts here.</small></div>
    <div class="sidebar-footer"><span class="tiny-dot" aria-hidden="true"></span> IT0049 · Assessment 1</div>
</aside>
<div class="workspace">
    <header class="topbar"><span>My workspace <span class="breadcrumb">/</span> <strong><?= esc($title === 'Tasks for Today' ? 'Today' : $title) ?></strong></span><?php $authUser = session()->get('auth_user'); ?><?php if ($authUser !== null): ?><span class="account-link"><?= esc($authUser['full_name']) ?> <span class="avatar avatar-small" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($authUser['full_name'], 0, 1))) ?></span></span><form method="post" action="<?= esc(site_url('logout'), 'attr') ?>" class="logout-form"><?= csrf_field() ?><button type="submit">Log out</button></form><?php endif; ?></header>
    <main id="main" tabindex="-1"><?= $this->renderSection('content') ?></main>
    <footer class="page-footer"><span>Tasks for Today Management System</span><span>Built with CodeIgniter 4</span></footer>
</div>
</body>
</html>
