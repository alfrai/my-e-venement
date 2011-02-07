<?php
  $gMap = new GMap();
  if ( $gMap->getGMapClient()->getAPIKey() ):
    $gMap = Addressable::getGmapFromObject($form, $gMap);
    include_partial('global/gmap',array('gMap' => $gMap, 'width' => $width ? $width : '550px'));
?>
<?php else: ?>
  <p><?php echo __("The geolocalization module is not enabled, you can't access this function.") ?></p>
<?php endif; ?>
