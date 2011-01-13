<?php echo $helper->linkToNew  (array(  'params' => 'class= fg-button ui-state-default  ',  'class_suffix' => 'new',  'label' => 'New',)) ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'csv',  'extra-icon' => 'show', 'label' => 'Extract to CSV',)) ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'group',  'extra-icon' => 'saveAndAdd', 'label' => 'Export to group',)) ?>
<?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ',  'action' => 'labels',  'extra-icon' => 'show', 'label' => 'Get labels',)) ?>
