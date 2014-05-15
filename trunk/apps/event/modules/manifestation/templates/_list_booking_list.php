  <ul>
    <?php if ( $manifestation->Booking->count() == 0 ): ?>
      <li class="empty"></li>
    <?php else: ?>
    <?php foreach ( $manifestation->Booking as $location ): ?>
    <?php $h2t = new HtmlToText($location->description); ?>
    <li class="<?php if ( trim($h2t->get_text()) ): ?>with-comment<?php endif ?>" title="<?php echo $h2t->get_text(); ?>">
      <a href="<?php echo url_for('location/show?id='.$location->id) ?>"><?php echo $location ?></a>
    </li>
    <?php endforeach ?>
    <?php endif ?>
  </ul>
