<?php include_partial('flashes') ?>

<div class="ui-widget-content ui-corner-all" id="cancel-tickets">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Cancelling tickets') ?></h1>
  </div>
  <?php include_partial('cancel_boot_ticket_id') ?>
  <?php include_partial('cancel_boot_pay',array('pay' => $pay)) ?>
  <?php if ( !$sf_user->hasCredential('tck-control') || $sf_user->isSuperAdmin() ): ?>
  <?php include_partial('cancel_boot_partial_by_price') ?>
  <?php endif ?>
  <?php include_partial('cancel_boot_complete_simplified') ?>
</div>
<?php include_partial('cancel_boot_js') ?>
