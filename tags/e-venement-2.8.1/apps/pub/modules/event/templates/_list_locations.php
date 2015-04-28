<?php
  $locations = new Doctrine_Collection('Manifestation');
  foreach ( $event->Manifestations as $manif )
    $locations[] = $manif->Location;
?>
<ul>
  <?php foreach ( $locations as $location ): ?>
  <li><?php echo $location ?></li>
  <?php endforeach ?>
</ul>
