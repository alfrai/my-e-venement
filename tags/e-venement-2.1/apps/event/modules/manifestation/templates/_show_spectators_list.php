<?php use_helper('Number') ?>

<?php include_partial('show_print_part',array('tab' => 'spectators')) ?>

<?php $arr = isset($form) ? array('form' => $form) : array('spectators' => $spectators) ?>

<?php include_partial('show_spectators_list_ordered',$arr); ?>
<?php include_partial('show_spectators_list_printed',$arr); ?>
<?php if (!sfConfig::get('app_ticketting_hide_demands')): ?>
<?php include_partial('show_spectators_list_asked',$arr); ?>
<?php endif ?>

<?php if ( sfConfig::get('app_ticketting_dematerialized') ): ?>
  <?php include_partial('show_spectators_list_tobecontrolled',$arr) ?>
  <?php include_partial('show_spectators_list_controlled',$arr) ?>
  <?php //include_partial('show_tickets_list_batch',$arr) ?>
<?php endif ?>
