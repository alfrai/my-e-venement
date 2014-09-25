<?php include_partial('check_assets') ?>
<div class="ui-widget-content ui-corner-all <?php echo $type ?>" id="bip-card">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Member card check',null,'menu') ?></h1>
  </div>

  <div class="sf_admin_actions_block ui-widget">
    <ul class="sf_admin_actions_form">
      <li class="sf_admin_action_list">
        <?php echo link_to('<span class="ui-icon ui-icon-arrowreturnthick-1-w"></span>'.__('List',null,'sf_admin'),'@member_card',array('class' => 'fg-button ui-state-default fg-button-icon-left')) ?>
      </li>
    </ul>
  </div>
  
  <?php include_partial('check_form') ?>
  <?php if ( isset($nb_valid) ): ?>
  <?php include_partial('check_result',array(
    'member_card' => $member_card,
    'member_cards' => $member_cards,
    'nb_valid' => $nb_valid,
  )) ?>
  <?php endif ?>
</div>
