<?= $this->extend('pos/layout') ?><?= $this->section('content') ?>
<section class="panel task-form">
<?php if ($errors): ?><div class="form-error" role="alert"><p>Please correct the following:</p><ul><?php foreach ($errors as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" action="<?= esc(site_url('pos/' . $resource . ($id === null ? '/create' : '/' . $id . '/update')), 'attr') ?>">
<?= csrf_field() ?>
<?php foreach ($fields as $field => $label): ?>
<label for="<?= esc($field, 'attr') ?>"><?= esc($label) ?><?= $field === 'password' && $id !== null ? ' (leave blank to keep current)' : ($field === 'phone' ? ' (optional)' : ' (required)') ?></label>
<input id="<?= esc($field, 'attr') ?>" name="<?= esc($field, 'attr') ?>" type="<?= in_array($field, ['price', 'stock_quantity'], true) ? 'number' : ($field === 'password' ? 'password' : ($field === 'email' ? 'email' : 'text')) ?>" value="<?= esc($field === 'password' ? '' : ($values[$field] ?? ''), 'attr') ?>"
<?= $field !== 'phone' && ! ($field === 'password' && $id !== null) ? 'required' : '' ?>
<?= $field === 'price' ? 'min="0.01" max="99999999.99" step="0.01"' : '' ?><?= $field === 'stock_quantity' ? 'min="0" max="1000000" step="1"' : '' ?><?= $field === 'password' ? 'autocomplete="new-password" minlength="12" maxlength="72"' : '' ?>>
<?php endforeach; ?>
<?php if ($imageField): ?><label for="upload"><?= esc(ucfirst($imageField)) ?> (optional)</label><input id="upload" name="<?= esc($imageField, 'attr') ?>" type="file" accept="image/jpeg,image/png"><small>JPEG or PNG, up to 2 MB, 4000 pixels per side and 8 megapixels. Images are resized to 800 pixels. Leave empty to keep the current image.</small><?php endif; ?>
<div class="form-actions"><button>Save</button><a href="<?= esc(site_url('pos/' . $resource), 'attr') ?>">Cancel</a></div>
</form></section><?= $this->endSection() ?>
