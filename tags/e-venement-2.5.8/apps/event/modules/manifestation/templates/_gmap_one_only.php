<?php
  $gMap = new GMap();
  if ( sfConfig::has('app_google_maps_api_keys') && $gMap->getGMapClient()->getAPIKey() ):
    $gMap = Addressable::getGmapFromObject($form->getObject()->Location, $gMap);
    include_partial('global/gmap',array('gMap' => $gMap, 'width' => isset($width) ? $width : '230px', 'height' => '230px'));
?>
<?php else: ?>
  <p><?php echo __("The geolocalization module is not enabled, you can't access this function.") ?></p>
<?php endif; ?>
