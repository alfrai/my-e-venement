<div class="sf_admin_form_row">
  <label><?php echo __('Workspaces list') ?>:</label>
  <ul>
    <?php foreach ( $form->getObject()->Workspaces as $ws ): ?>
    <li><?php echo $ws ?></li>
    <?php endforeach ?>
  </ul>
</div>

