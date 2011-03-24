<?php
  $gMap = new GMap();
  if ( $gMap->getGMapClient()->getAPIKey() ):
    $gMap = Addressable::getGmapFromObject($form->getObject()->Location, $gMap);
    include_partial('global/gmap',array('gMap' => $gMap, 'width' => isset($width) ? $width : '420px', 'height' => '300px'));
?>
<?php else: ?>
  <p><?php echo __("The geolocalization module is not enabled, you can't access this function.") ?></p>
<?php endif; ?>
