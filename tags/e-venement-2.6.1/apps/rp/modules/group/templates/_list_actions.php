<?php if ( $sf_user->hasCredential('pr-group-perso') || $sf_user->hasCredential('pr-group-common') ): ?>
<?php echo $helper->linkToNew(array(  'params' => 'class= fg-button ui-state-default  ',  'class_suffix' => 'new',  'label' => 'New',)) ?>
<?php endif ?>
