<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= esc($title) ?> | Complete POS</title><link rel="stylesheet" href="<?= esc(base_url('assets/app.css'), 'attr') ?>"><link rel="stylesheet" href="<?= esc(base_url('assets/pos.css'), 'attr') ?>"></head>
<body class="pos-body"><a class="skip-link" href="#main">Skip to content</a>
<header class="pos-header"><a class="pos-brand" href="<?= esc(site_url('pos'), 'attr') ?>">Complete POS <small>IT0049 · Midterm Project</small></a><span><?= esc(session()->get('auth_user')['full_name']) ?></span><form method="post" action="<?= esc(site_url('logout'), 'attr') ?>"><?= csrf_field() ?><button>Log out</button></form></header>
<nav class="pos-nav" aria-label="POS navigation">
<?php foreach (['pos' => 'Record Sale', 'pos/products' => 'Products', 'pos/customers' => 'Customers', 'pos/staff' => 'Staff', 'pos/sales' => 'Sales History', '/' => 'Task workspace'] as $path => $label): ?>
<a href="<?= esc(site_url($path), 'attr') ?>" <?= $title === $label ? 'aria-current="page"' : '' ?>><?= esc($label) ?></a>
<?php endforeach; ?></nav>
<main id="main"><div class="page-heading"><div><p class="eyebrow">YOUR STORE, ORGANIZED</p><h1><?= esc($title) ?></h1></div></div>
<?php if ($message = session()->getFlashdata('message')): ?><p class="notice" role="status"><?= esc($message) ?></p><?php endif; ?>
<?= $this->renderSection('content') ?></main><footer class="pos-footer">Complete Point-of-Sale System · Alfred Nathaniel Embuscado</footer></body></html>
