<?php if ( $sf_user->hasCredential('pr-contact-new') ): ?>
<?php echo $helper->linkToNew  (array(  'params' => 'class= fg-button ui-state-default  ',  'class_suffix' => 'new',  'label' => 'New',)) ?>
<?php endif ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'duplicates',  'extra-icon' => 'show', 'label' => 'Duplicates',)) ?>
<?php if ( $sf_user->hasCredential('pr-contact-csv') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'csv',  'extra-icon' => 'show', 'label' => 'Extract to CSV',)) ?>
<?php endif ?>
<?php if ( $sf_user->hasCredential('pr-group-perso') || $sf_user->hasCredential('pr-group-common') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'group',  'extra-icon' => 'saveAndAdd', 'label' => 'Export to group',)) ?>
<?php endif ?>
<?php if ( $sf_user->hasCredential('pr-contact-labels') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'labels',  'extra-icon' => 'show', 'label' => 'Get labels',)) ?>
<?php endif ?>
<?php if ( $sf_user->hasCredential('pr-emailing') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'emailing',  'extra-icon' => 'show', 'label' => 'Create emailing',)) ?>
<?php endif ?>
<?php $gkeys = sfConfig::get('app_google_maps_api_keys') ?>
<?php if ( sfConfig::has('app_google_maps_api_keys') && $gkeys['default'] && $sf_user->hasCredential('pr-contact') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'map',  'extra-icon' => 'show', 'label' => 'Geolocalize',)) ?>
<?php endif ?>
