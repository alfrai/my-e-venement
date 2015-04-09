<div class="sf_admin_form_row">
  <label><?php echo __('Groups list') ?>:</label>
  <ul>
    <?php foreach ( $form->getObject()->Groups as $group ): ?>
    <li><?php echo $group ?></li>
    <?php endforeach ?>
  </ul>
</div>

