<?php use_helper('Number') ?>

<?php include_partial('show_print_part',array('tab' => 'spectators', 'manifestation_id' => $manifestation_id)) ?>
<?php include_partial('show_export_part',array('manifestation_id' => $manifestation_id)) ?>

<?php
  $arr = isset($form) ? array('form' => $form) : array('spectators' => $spectators);
  $arr = array_merge($arr,array('show_workspaces' => $show_workspaces));
?>

<?php include_partial('show_spectators_list_ordered',$arr); ?>
<?php include_partial('show_spectators_list_printed',$arr); ?>
<?php if (sfConfig::get('project_tickets_count_demands',false)): ?>
<?php include_partial('show_spectators_list_asked',$arr); ?>
<?php endif ?>

<?php if ( sfConfig::get('app_ticketting_dematerialized') ): ?>
  <?php include_partial('show_spectators_list_tobecontrolled',$arr) ?>
  <?php include_partial('show_spectators_list_controlled',$arr) ?>
  <?php //include_partial('show_tickets_list_batch',$arr) ?>
<?php endif ?>
