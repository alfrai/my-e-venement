<?php if ( $sf_user->hasCredential('event-seated-plan') ): ?>
<div class="sf_admin_form_row">
  <label><?php echo __('Seated') ?>:</label>
  <?php
    echo $workspace->seated
      ? image_tag('/sfDoctrinePlugin/images/tick.png')
      : image_tag('/sfDoctrinePlugin/images/delete.png')
  ?>
</div>
<?php endif ?>
