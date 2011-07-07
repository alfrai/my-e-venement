<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php $manifs[$manif->happens_at] = link_to(format_date($manif->happens_at,'d MMM yyyy HH:mm'),'manifestation/show?id='.$manif->id,array('title' => '@ '.$manif->Location)) ?>
<?php endforeach ?>
<?php sort($manifs) ?>
<?php echo implode(', ',$manifs) ?>
