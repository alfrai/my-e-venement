<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php $manifs[$manif->happens_at.'-'.$manif->id] = $manif ?>
<?php endforeach ?>
<?php ksort($manifs) ?>
<?php foreach($manifs as $manif): ?>
<?php include_partial('manifestation/list_gauge',array('manifestation' => $manif)) ?><br/>
<?php endforeach ?>
