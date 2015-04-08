<div class="sf_admin_form_row sf_admin_form_field_booking_list show">
  <label for="manifestation_booking_list"><?php echo __('Extra locations') ?></label>
  <ul class="ui-corner-all ui-widget-content">
    <?php if ( $manifestation->Booking->count() == 0 ): ?>
      <li><?php echo __('No extra location booked') ?></li>
    <?php else: ?>
    <?php foreach ( $manifestation->Booking as $location ): ?>
    <li><a href="<?php echo url_for(($location->place?'location':'resource').'/show?id='.$location->id) ?>"><?php echo $location ?></a></li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
</div>
