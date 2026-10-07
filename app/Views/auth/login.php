<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title) ?> | Tasks for Today</title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/app.css'), 'attr') ?>">
</head>
<body>
<main class="login-page" id="main">
    <section class="panel login-panel" aria-labelledby="login-heading">
        <p class="eyebrow">STAFF ACCESS</p>
        <h1 id="login-heading">Who’s allowed in?</h1>
        <p class="subtitle">Log in to view your workspace.</p>
        <?php if ($error !== null): ?><p class="form-error" role="alert"><?= esc($error) ?></p><?php endif; ?>
        <form method="post" action="<?= esc(site_url('login'), 'attr') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="redirect" value="<?= esc($redirect, 'attr') ?>">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" autocomplete="username" required maxlength="50" value="<?= esc((string) old('username'), 'attr') ?>">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button type="submit">Log in</button>
        </form>
        <p><a href="<?= esc(site_url('about'), 'attr') ?>">About this project</a></p>
    </section>
</main>
</body>
</html>
