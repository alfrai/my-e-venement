<?php include_partial('global/assets') ?>

<div id="sf_admin_container" class="sf_admin_edit ui-widget ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Change language', null, 'menu') ?></h1>
  </div>
  <?php include_partial('global/flashes') ?>
  <?php include_partial('form', array('form' => $form)) ?>
</div>
