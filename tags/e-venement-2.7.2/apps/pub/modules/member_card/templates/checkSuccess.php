<?php include_partial('check_assets') ?>
<div class="ui-widget-content ui-corner-all <?php echo $type ?>" id="bip-card">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Member card check',null,'menu') ?></h1>
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
