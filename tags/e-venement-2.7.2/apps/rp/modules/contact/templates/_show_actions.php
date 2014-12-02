<ul class="sf_admin_actions_form">
  <?php echo $helper->linkToList(array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'class_suffix' => 'list',  'label' => 'Back to list',  'ui-icon' => '',)) ?>
  <?php if ( sfConfig::get('app_cards_enable') ): ?>
    <?php echo $helper->linkToExtraAction(array(  'action' => 'card', 'params' => 'class= fg-button ui-state-default fg-button-icon-left',  'class_suffix' => 'card',  'label' => 'Card', 'more-icon' => 'print',),$contact) ?>
  <?php endif ?>
  <?php echo $helper->linkToEdit($contact, array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'class_suffix' => 'edit',  'label' => 'Edit',  'ui-icon' => '',)) ?>
  <?php echo $helper->linkToDelete($form->getObject(), array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',  'ui-icon' => '',)) ?>
</ul>
