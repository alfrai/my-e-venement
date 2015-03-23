<?php echo $helper->linkToNew(array(  'params' => 'class= fg-button ui-state-default  ',  'class_suffix' => 'new',  'label' => 'New',)) ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ', 'action'=>'index', 'module'=>'calendar',  'extra-icon'=>'show', 'class_suffix' => 'calendar',  'label' => 'Calendar',)) ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ', 'action'=>'templating', 'module'=>'manifestation',  'extra-icon'=>'saveAndAdd', 'class_suffix' => 'templating',  'label' => 'Templating',)) ?>
