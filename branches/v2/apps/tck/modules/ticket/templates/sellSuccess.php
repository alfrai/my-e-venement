<?php
  use_stylesheet('sell');
  use_stylesheet('/sfFormExtraPlugin/css/jquery.autocompleter.css');
?>
<?php
  use_javascript('/sfFormExtraPlugin/js/jquery.autocompleter.js');
  use_javascript('ticket');
?>
<?php use_helper('CrossAppLink') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>Vendre des billets</h1>
  </div>
  <?php include_partial('ticket_contact',array('form' => $form, 'transaction' => $transaction)) ?>
  <div class="ui-corner-all ui-widget-content action">
    <p>Manifestations:</p>
  </div>
  <div class="ui-corner-all ui-widget-content action">
    <p>Tarifs</p>
  </div>
  <div class="ui-corner-all ui-widget-content action">
    <p>Paiement</p>
  </div>
  <div class="ui-corner-all ui-widget-content action">
    <p>Validation</p>
  </div>
</div>
