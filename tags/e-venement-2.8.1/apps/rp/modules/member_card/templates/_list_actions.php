<?php if ($sf_user->hasCredential(array(  0 => 'pr-card-mod',))): ?>
<?php echo $helper->linkToNew(array(  'credentials' =>   array(    0 => 'pr-card-mod',  ),  'params' => 'class= fg-button ui-state-default  ',  'class_suffix' => 'new',  'label' => 'New',)) ?>
<?php endif ?>
<?php if ($sf_user->hasCredential(array(  0 => 'pr-card-view',))): ?>
<?php echo $helper->linkToExtraAction(array( 'params' => 'class= fg-button ui-state-default  ', 'action' => 'csv', 'extra-icon' => 'show', 'label' => 'Extract to CSV',)) ?>
<?php endif ?>
