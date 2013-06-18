<div class="sf_admin_form_row sf_admin_form_field_organizers_list">
  <label><?php echo __('Organizers list') ?>:</label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $manifestation->Organizers->count() == 0 ): ?>
      <li><?php echo __('No registered organizer') ?></li>
    <?php else: ?>
    <?php foreach ( $manifestation->Organizers as $organizer ): ?>
    <li><a href="<?php echo url_for('organism/show?id='.$organizer->id) ?>"><?php echo $organizer ?></a></li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
</div>
