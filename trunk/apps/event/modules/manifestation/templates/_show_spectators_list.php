<?php use_helper('Number') ?>
<?php include_partial('show_spectators_list_printed',array('form' => $form)); ?>
<?php include_partial('show_spectators_list_ordered',array('form' => $form)); ?>
<?php if (!sfConfig::has('app_ticketting_hide_demands')): ?>
<?php include_partial('show_spectators_list_asked',array('form' => $form)); ?>
<?php endif ?>

<?php if ( sfConfig::get('app_ticketting_dematerialized') ): ?>
  <?php include_partial('show_spectators_list_controlled',array('form' => $form)) ?>
  <?php include_partial('show_spectators_list_tobecontrolled',array('form' => $form)) ?>
  <?php include_partial('show_tickets_list_batch',array('form' => $form)) ?>
<?php endif ?>
