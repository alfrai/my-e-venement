<?php if ( $sf_user->hasCredential('pr-organism-new') ): ?>
<?php echo $helper->linkToNew  (array(  'params' => 'class= fg-button ui-state-default  ',  'class_suffix' => 'new',  'label' => 'New',)) ?>
<?php endif ?>
<?php if ( sfConfig::has('app_google_maps_api_keys_default') && $sf_user->hasCredential('pr-organism') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'map',  'extra-icon' => 'show', 'label' => 'Geolocalize',)) ?>
<?php endif ?>
<?php //echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'csv',  'extra-icon' => 'list', 'label' => 'Extract',)) ?>
