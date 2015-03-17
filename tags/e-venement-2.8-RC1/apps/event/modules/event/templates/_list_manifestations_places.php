<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php $manifs[$manif->happens_at.'-'.$manif->id] = $manif ?>
<?php endforeach ?>
<?php
  if ( sfConfig::get('app_listing_manif_date','DESC') == 'DESC' )
    krsort($manifs);
  else
    ksort($manifs);
?>
<?php foreach($manifs as $manif): ?>
<?php echo link_to($manif->Location, 'location/show?id='.$manif->location_id, array(
  'title' => $manif->Location,
  'class' => 'place',)
) ?><br/>
<?php endforeach ?>
