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
<span class="gauge"><?php include_partial('manifestation/list_gauge',array('manifestation' => $manif)) ?></span><br/>
<?php endforeach ?>
