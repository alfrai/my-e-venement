  <ul>
    <?php if ( $manifestation->Booking->count() == 0 ): ?>
      <li class="empty"><?php echo __('No extra location booked') ?></li>
    <?php else: ?>
    <?php foreach ( $manifestation->Booking as $location ): ?>
    <li><a href="<?php echo url_for('location/show?id='.$location->id) ?>"><?php echo $location ?></a></li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
