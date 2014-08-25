<?php foreach ( $event->Manifestations as $manif ): ?>
<?php
  $seats = array();
  foreach ( $manif->getBestFreeSeat(4) as $seat )
    $seats[] = "{$seat->rank}:&nbsp;$seat";
  echo '<span>'.implode('</span>, <span>',$seats).'</span>';
?>
<br/>
<?php endforeach ?>
