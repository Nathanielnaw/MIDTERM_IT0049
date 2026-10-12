<?= $this->extend('pos/layout') ?><?= $this->section('content') ?>
<p><a class="pos-button" href="<?= esc(site_url('pos/' . $resource . '/new'), 'attr') ?>">+ Add <?= esc($resource === 'staff' ? 'staff member' : rtrim($resource, 's')) ?></a></p>
<section class="panel table-scroll"><table><caption class="sr-only">Active <?= esc($resource) ?></caption><thead><tr>
<?php if ($imageField): ?><th>Image</th><?php endif; ?>
<?php foreach ($fields as $label): ?><th><?= esc($label) ?></th><?php endforeach; ?><th>Created</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($records as $record): ?><tr>
<?php if ($imageField): ?><?php $filename = $record[$imageField] ?? ''; $imagePath = preg_match('/^[a-f0-9]{32}\.jpg$/D', $filename) && is_file(FCPATH . 'uploads/pos/' . $filename) ? 'uploads/pos/' . $filename : 'assets/pos-placeholder.svg'; ?><td><img class="pos-image" src="<?= esc(base_url($imagePath), 'attr') ?>" alt="<?= esc($record['name'] ?? $record['full_name'], 'attr') ?>"></td><?php endif; ?>
<?php foreach ($fields as $field => $label): ?><td><?= esc($field === 'price' ? number_format((float) $record[$field], 2) : $record[$field]) ?></td><?php endforeach; ?>
<td><?= esc($record['created_at']) ?></td><td><div class="task-actions"><a class="text-link" href="<?= esc(site_url('pos/' . $resource . '/' . $record['id'] . '/edit'), 'attr') ?>">Edit</a><form method="post" action="<?= esc(site_url('pos/' . $resource . '/' . $record['id'] . '/archive'), 'attr') ?>"><?= csrf_field() ?><button>Archive</button></form></div></td></tr><?php endforeach; ?>
<?php if ($records === []): ?><tr><td colspan="6">No active records yet. Add your first record to get started.</td></tr><?php endif; ?>
</tbody></table></section><?= $this->endSection() ?>
