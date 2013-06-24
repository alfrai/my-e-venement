<?php
  $gMap = new GMap();
  if ( sfConfig::has('app_google_maps_api_keys') && $gMap->getGMapClient()->getAPIKey() ):
    $gMap = Addressable::getGmapFromObject($form->getObject(), $gMap);
    include_partial('global/gmap',array('gMap' => $gMap, 'width' => $width ? $width : '550px', 'height' => $height));
?>
<?php else: ?>
  <p><?php echo __("The geolocalization module is not enabled, you can't access this function.") ?></p>
<?php endif; ?>
