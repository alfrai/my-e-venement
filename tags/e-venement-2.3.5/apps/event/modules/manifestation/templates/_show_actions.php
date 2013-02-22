<ul class="sf_admin_actions_form">
  <?php echo $helper->linkToList(array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'class_suffix' => 'list',  'label' => 'Back to list',  'ui-icon' => '',),$manifestation) ?>
  <?php if ( $sf_user->hasCredential('tck-ledger-cash') && $sf_user->hasCredential('tck-ledger-sales') ): ?>
  <li class="sf_admin_action_ledger"><a href="<?php echo cross_app_url_for('tck','ledger/both') ?>?criterias[manifestations][]=<?php echo $manifestation->id ?>" class="fg-button ui-state-default fg-button-icon-left">
    <span class="ui-icon ui-icon-note"></span><?php echo __('Ledger', array(), 'sf_admin') ?>
  </a></li>
  <?php endif ?>
  <?php if ( $sf_user->hasCredential('tck-integrate-foreign') ): ?>
  <li class="sf_admin_action_integrate"><a href="<?php echo cross_app_url_for('tck','ticket/batchIntegrate').'?manifestation_id='.$manifestation->id ?>" class="fg-button ui-state-default fg-button-icon-left">
    <span class="ui-icon ui-icon-arrowthickstop-1-s"></span><?php echo __('Integrate', array(), 'sf_admin') ?>
  </a></li>
  <?php endif ?>
  <?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ', 'app' => 'tck', 'action'=>'sell#manif-'.$manifestation->id, 'module'=>'ticket',  'extra-icon'=>'show', 'class_suffix' => 'sell',  'label' => 'Sell',)) ?>
  <?php echo $helper->linkToEdit($manifestation, array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'class_suffix' => 'edit',  'label' => 'Edit',  'ui-icon' => '',)) ?>
  <?php echo $helper->linkToDelete($form->getObject(), array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',  'ui-icon' => '',)) ?>
</ul>
