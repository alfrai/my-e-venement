<div class="sf_admin_form_row sf_admin_form_field_participants_list">
  <label><?php echo __('Participants list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $manifestation->Participants->count() == 0 ): ?>
      <li><?php echo __('No registered participant') ?></li>
    <?php else: ?>
    <?php foreach ( $manifestation->Participants as $participant ): ?>
    <li><a href="<?php echo cross_app_url_for('rp', 'contact/show?id='.$participant->id) ?>"><?php echo $participant ?></a></li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
</div>
