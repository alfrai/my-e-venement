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
  <form class="ui-corner-all ui-widget-content action" id="contact" action="<?php echo url_for('ticket/addContact') ?>" method="post">
    <p>
      <span class="title"><?php echo __('Contact') ?>:</span>
      <span class="contact">
        <input type="hidden" value="<?php echo $form->getCSRFToken ?>" name="_csrf_token" />
        <input type="hidden" value="<?php echo $transaction->id ?>" name="transaction[id]" />
        <?php
          $w = new sfWidgetFormDoctrineJQueryAutocompleter(array(
            'model' => 'Contact',
            'url'   => cross_app_url_for('rp','contact/ajax'),
          ));
          echo $w->render('contact_id');
        ?>
      </span>
      <span class="professional">
      </span>
    </p>
  </form>
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
