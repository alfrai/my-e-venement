<ul class="sf_admin_actions_form">
  <?php echo $helper->linkToList(array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'class_suffix' => 'list',  'label' => __('Event'),  'ui-icon' => '',),$manifestation) ?>
  <?php if ( $manifestation->current_version ): ?>
    
    <li class="sf_admin_action_version_by fg-button ui-state-default fg-button-icon-left">
      <span class="ui-icon ui-icon-person"></span>
      <?php echo $manifestation->current_version->sf_guard_user_id
        ? ( ($u = Doctrine::getTable('sfGuardUser')->findOneById($manifestation->current_version->sf_guard_user_id)) ? $u : $manifestation->version->sf_guard_user_id ) 
        : 'n/a' ?>
    </li>
    <li class="sf_admin_action_updated_at fg-button ui-state-default fg-button-icon-left">
      <span class="ui-icon ui-icon-calendar"></span>
      <?php echo format_datetime($manifestation->current_version->updated_at) ?>
    </li>
    
    <li class="sf_admin_action_version fg-button ui-state-default fg-button-icon-left">
    <form action="<?php echo url_for('manifestation/versions?id='.$manifestation->id) ?>" method="get">
      <p>
        <span class="ui-icon ui-icon-flag"></span>
        <label><?php echo __('Version') ?></label>
        <input maxlength="4" size="3" type="text" name="version" value="<?php echo $manifestation->current_version ? $manifestation->current_version->version : $manifestation->current_version ?>" />
        <span class="max">/&nbsp;<?php echo $manifestation->version ?></span>
      </p>
    </form>
    </li>
  
    <li class="sf_admin_action_versions">
    <?php echo link_to(
      '<span class="ui-icon ui-icon-document"></span>'.__('Current', array(), 'sf_admin'),
      'manifestation/show?id='.$manifestation->id,
      array('class' => 'fg-button ui-state-default fg-button-icon-left')
    ) ?>
    </li>
    
    <script type="text/javascript">
      $(document).ready(function(){
        // remove information linked to foreign data
        $('#sf_fieldset_spectators, #sf_fieldset_unbalanced, #sf_fieldset_tickets, #sf_fieldset_workspaces, #more').remove()
        $('[href="#sf_fieldset_spectators"], [href="#sf_fieldset_unbalanced"], [href="#sf_fieldset_tickets"], [href="#sf_fieldset_workspaces"]').parent().remove()
        $('.sf_admin_form_row.sf_admin_form_field_gauge_txt, .sf_admin_form_row.sf_admin_form_field_organizers_list, .sf_admin_form_row.sf_admin_form_field_prices_list, .sf_admin_form_row.sf_admin_form_field_booking_list').remove();
      });
    </script>
    
  <?php else: // don't display_versions ?>
  
  <li class="sf_admin_action_versions">
  <?php echo link_to(
    '<span class="ui-icon ui-icon-calendar"></span>'.__('Versions', array(), 'sf_admin'),
    'manifestation/versions?id='.$manifestation->id,
    array('class' => 'fg-button ui-state-default fg-button-icon-left')
  ) ?>
  </li>
  <?php if ( $sf_user->hasCredential('tck-ledger-cash') && $sf_user->hasCredential('tck-ledger-sales') ): ?>
  <li class="sf_admin_action_ledger"><a href="<?php echo cross_app_url_for('tck','ledger/both') ?>?criterias[manifestations][]=<?php echo $manifestation->id ?>" class="fg-button ui-state-default fg-button-icon-left">
    <span class="ui-icon ui-icon-note"></span><?php echo __('Ledger', array(), 'sf_admin') ?>
  </a></li>
  <?php endif ?>
  <?php if ( $manifestation->reservation_confirmed ): ?>
  <?php if ( $sf_user->hasCredential('tck-integrate-foreign') ): ?>
  <li class="sf_admin_action_integrate"><a href="<?php echo cross_app_url_for('tck','ticket/batchIntegrate').'?manifestation_id='.$manifestation->id ?>" class="fg-button ui-state-default fg-button-icon-left">
    <span class="ui-icon ui-icon-arrowthickstop-1-s"></span><?php echo __('Integrate', array(), 'sf_admin') ?>
  </a></li>
  <?php endif ?>
  <?php echo $helper->linkToExtraAction(array(  'params' => 'class= fg-button ui-state-default  ', 'app' => 'event', 'action'=> 'sell?id='.$manifestation->id, 'module'=>'manifestation',  'extra-icon'=>'show', 'class_suffix' => 'sell',  'label' => 'Sell',)) ?>
  <?php endif ?>
  <?php echo $helper->linkToEdit($manifestation, array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'class_suffix' => 'edit',  'label' => 'Edit',  'ui-icon' => '',)) ?>
  <?php echo $helper->linkToDelete($form->getObject(), array(  'params' => 'class= fg-button ui-state-default fg-button-icon-left ',  'confirm' => 'Are you sure?',  'class_suffix' => 'delete',  'label' => 'Delete',  'ui-icon' => '',)) ?>

  <?php endif ?>
</ul>
