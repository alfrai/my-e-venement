<div class="sf_admin_form_row">
  <label><?php echo __('Users list') ?>:</label>
  <ul>
    <?php foreach ( $form->getObject()->Users as $user ): ?>
    <li><?php echo $user ?></li>
    <?php endforeach ?>
  </ul>
</div>

