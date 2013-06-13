<?php if ( $event->Manifestations->count() > 0 ): ?>
<ul>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <li><?php echo link_to($manif->getFormattedDate(),'manifestation/edit?id='.$manif->id) ?></li>
<?php endforeach ?>
</ul>
<?php endif ?>

