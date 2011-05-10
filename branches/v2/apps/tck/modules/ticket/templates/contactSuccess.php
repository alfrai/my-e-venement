<?php include_partial('assets') ?>
<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Selling tickets') ?></h1>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="contact">
    <?php include_partial('ticket_contact',array('transaction' => $transaction, 'form' => $form)); ?>
  </div>
</div>
