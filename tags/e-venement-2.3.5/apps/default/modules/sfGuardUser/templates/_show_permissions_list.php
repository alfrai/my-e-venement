<div class="sf_admin_form_row">
  <label><?php echo __('Permissions list') ?>:</label>
  <ul>
    <?php foreach ( $form->getObject()->Permissions as $perm ): ?>
    <li><?php echo $perm ?></li>
    <?php endforeach ?>
  </ul>
</div>

