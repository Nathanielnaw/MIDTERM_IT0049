<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Tasks for Today') ?></title>
    <style>
        :root { --ink:#172033; --muted:#64748b; --brand:#2563eb; --bg:#f4f7fb; --line:#dbe3ef; }
        * { box-sizing:border-box; } body { margin:0; background:var(--bg); color:var(--ink); font:16px/1.5 Arial,sans-serif; }
        header { background:#172033; color:#fff; } nav { max-width:1000px; margin:auto; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
        nav strong { font-size:20px; } nav a { color:#dbeafe; text-decoration:none; margin-left:18px; } nav a:hover { color:#fff; }
        main { max-width:1000px; margin:42px auto; padding:0 24px; } h1 { margin:0 0 8px; font-size:34px; } h2 { margin-top:30px; } .muted { color:var(--muted); }
        .card { background:#fff; border:1px solid var(--line); border-radius:12px; padding:24px; box-shadow:0 8px 24px #1720330b; margin-top:24px; }
        table { width:100%; border-collapse:collapse; margin-top:16px; } th,td { padding:13px 12px; border-bottom:1px solid var(--line); text-align:left; } th { color:var(--muted); font-size:13px; text-transform:uppercase; letter-spacing:.04em; }
        .badge { display:inline-block; padding:4px 9px; border-radius:99px; background:#e8eef8; color:#334155; font-size:13px; } .completed { background:#dcfce7; color:#166534; }
        .profile dt { color:var(--muted); margin-top:14px; } .profile dd { margin:2px 0 0; font-weight:bold; }
        footer { max-width:1000px; margin:50px auto 30px; padding:0 24px; color:var(--muted); font-size:14px; }
    </style>
</head>
<body>
<header><nav><strong>Tasks for Today</strong><div><a href="<?= site_url('/') ?>">Today</a><a href="<?= site_url('tasks') ?>">All Tasks</a><a href="<?= site_url('profile') ?>">Profile</a><a href="<?= site_url('about') ?>">About</a></div></nav></header>
<main><?= $this->renderSection('content') ?></main>
<footer>Tasks for Today Management System · CodeIgniter 4</footer>
</body>
</html>
