<?php include_partial('show_tickets_list_printed',array('form' => $form)); ?>
<?php include_partial('show_tickets_list_ordered',array('form' => $form)); ?>
<?php include_partial('show_tickets_list_asked',array('form' => $form)); ?>

<?php if ( sfConfig::get('app_ticketting_dematerialized') ): ?>
  <?php include_partial('show_tickets_list_controlled',array('form' => $form)) ?>
<?php endif ?>
