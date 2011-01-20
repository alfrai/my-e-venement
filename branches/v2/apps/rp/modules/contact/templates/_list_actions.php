<?php if ( $sf_user->hasCredential('pr-contact-new') ): ?>
<?php echo $helper->linkToNew  (array(  'params' => 'class= fg-button ui-state-default  ',  'class_suffix' => 'new',  'label' => 'New',)) ?>
<?php endif ?>
<?php if ( $sf_user->hasCredential('pr-contact-csv') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'csv',  'extra-icon' => 'show', 'label' => 'Extract to CSV',)) ?>
<?php endif ?>
<?php if ( $sf_user->hasCredential('pr-group-perso') || $sf_user->hasCredential('pr-group-common') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'group',  'extra-icon' => 'saveAndAdd', 'label' => 'Export to group',)) ?>
<?php endif ?>
<?php if ( $sf_user->hasCredential('pr-contact-labels') ): ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'labels',  'extra-icon' => 'show', 'label' => 'Get labels',)) ?>
<?php endif ?>
