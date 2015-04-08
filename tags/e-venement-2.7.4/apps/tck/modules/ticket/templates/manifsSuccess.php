<?php include_partial('assets') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>Vendre des billets</h1>
  </div>
  <div class="ui-corner-all ui-widget-content action" id="manifestations">
    <?php include_partial('ticket_manifestations',array(
      'transaction' => $transaction,
      'manifestations_add' => $manifestations_add,
      'page' => isset($page) ? $page : 0,
    )); ?>
  </div>
</div>
