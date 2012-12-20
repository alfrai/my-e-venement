<?php use_helper('Number') ?>

<?php $arr = isset($form) && !is_null($form) ? array('form' => $form) : array('prices' => $prices) ?>

<?php include_partial('show_print_part',array('tab' => 'tickets')) ?>
<?php include_partial('show_tickets_list_ordered',$arr); ?>
<?php include_partial('show_tickets_list_printed',$arr); ?>
<?php if (sfConfig::get('project_tickets_count_demands',false)): ?>
<?php include_partial('show_tickets_list_asked',$arr); ?>
<?php endif ?>

<?php if ( sfConfig::get('app_ticketting_dematerialized') ): ?>
  <?php //include_partial('show_tickets_list_controlled',$arr) ?>
<?php endif ?>
