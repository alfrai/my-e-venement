<div class="sf_admin_form_row">
  <label><?php echo __('Meta events list') ?>:</label>
  <ul>
    <?php foreach ( $form->getObject()->MetaEvents as $me ): ?>
    <li><?php echo $me ?></li>
    <?php endforeach ?>
  </ul>
</div>

