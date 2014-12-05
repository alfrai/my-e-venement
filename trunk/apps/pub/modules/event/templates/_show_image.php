<?php if ( $event->picture_id ): ?>
  <div class="event-pic">
    <?php echo $event->Picture->getRawValue()->render(array('app' => 'pub')) ?>
  </div>
<?php endif ?>
