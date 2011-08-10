<?php $manifs = array() ?>
<?php foreach ( $event->Manifestations as $manif ): ?>
  <?php $manifs[$manif->happens_at] = $manif ?>
<?php endforeach ?>
<?php sort($manifs) ?>
<?php foreach($manifs as $manif): ?>
<?php include_partial('manifestation/list_gauge',array('manifestation' => $manif)) ?><br/>
<?php endforeach ?>
