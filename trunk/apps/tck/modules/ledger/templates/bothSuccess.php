<?php include_partial('assets') ?>
<?php use_stylesheet('ledger-both','',array('media' => 'all')) ?>

<div>
<div class="ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php
        $values = $form->getValues();
        if ( !isset($values['dates']['from']) ) $values['dates']['from'] = date('Y-m-d',strtotime('1 month ago'));
        if ( !isset($values['dates']['to']) ) $values['dates']['to'] = date('Y-m-d',strtotime('tomorrow'));
      ?>
      <?php echo __('Detailed Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array('%%from%%' => format_date($values['dates']['from']), '%%to%%' => format_date($values['dates']['to']))) ?>)
    </h1>
  </div>
</div>
</div>

<div class="ledger-both">
<?php include_partial('criterias',array('form' => $form, 'ledger' => 'both')) ?>
<?php include_partial('both_payment',array('byPaymentMethod' => $byPaymentMethod)) ?>
<?php include_partial('both_price',array('byPrice' => $byPrice)) ?>
<div class="clear"></div>
<?php include_partial('both_value',array('byValue' => $byValue)) ?>
<?php include_partial('both_user',array('byUser' => $byUser)) ?>
</div>
<p style="margin: 10px;"><?php echo date('Y-m-d H:i:s') ?></p>
