<?php include_partial('assets') ?>
<?php use_stylesheet('ledger-both','',array('media' => 'all')) ?>

<div><div class="ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php
        $values = $form->getValues();
        if ( !isset($values['dates']['from']) ) $values['dates']['from'] = date('Y-m-d',strtotime('1 month ago'));
        if ( !isset($values['dates']['to']) ) $values['dates']['to'] = date('Y-m-d',strtotime('tomorrow'));
      ?>
      <?php if ( $manifestations ): ?>
      <?php echo __('Manifestation ledger') ?>
      <?php else: ?>
      <?php echo __('Detailed Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array('%%from%%' => format_date($values['dates']['from']), '%%to%%' => format_date($values['dates']['to']))) ?>)
      <?php endif ?>
    </h1>
  </div>
</div></div>

<?php include_partial('criterias',array('form' => $form, 'ledger' => 'both')) ?>

<?php if ( $manifestations ): ?>
<div class="ui-widget-content ui-corner-all" id="manifestations">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __("Concerned manifestations") ?></h2>
  </div>
  <ul><?php foreach ( $manifestations as $manif ): ?>
    <li><?php echo cross_app_link_to($manif,'event','manifestation/show?id='.$manif->id) ?></li>
  <?php endforeach ?></ul>
</div>
<?php endif ?>

<div class="ledger-both">
<?php include_partial('both_payment',array('byPaymentMethod' => $byPaymentMethod,'form' => $form)) ?>
<?php include_partial('both_price',array('byPrice' => $byPrice)) ?>
<div class="clear"></div>
<?php include_partial('both_value',array('byValue' => $byValue)) ?>
<div class="clear"></div>
<?php include_partial('both_user',array('byUser' => $byUser)) ?>
</div>
