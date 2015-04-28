<?php echo $importForm->renderHiddenFields() ?>
<?php foreach ( array('location_id', 'book_all', 'file') as $field ): ?>
<div class="sf_admin_form_row sf_admin_text sf_admin_form_field_<?php echo $field ?>">
  <?php echo $importForm[$field]->renderLabel() ?>
  <div class="label ui-helper-clearfix"><?php echo $importForm[$field]->renderHelp() ?></div>
  <div class="widget"><?php echo $importForm[$field] ?></div>
</div>
<?php endforeach ?>
