<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>
<?php use_stylesheet('tck-ledger-both','',array('media' => 'all')) ?>

<div><div class="ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php if ( $manifestations || $workspaces ): ?>
      <?php echo format_number_choice('[1]Manifestation ledger|(1,+Inf]Manifestations ledger',null,$workspaces ? 2 : $manifestations->count()) ?>
      <?php else: ?>
      <?php echo __('Detailed Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array(
        '%%from%%' => format_date(strtotime($options['dates'][0])),
        '%%to%%' => format_date(strtotime($options['dates'][1])),
      )) ?>)
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

<?php if ( $users ): ?>
<?php include_partial('users',array('users' => $users)) ?>
<?php endif ?>

<?php if ( $workspaces ): ?>
<?php include_partial('workspaces',array('workspaces' => $workspaces, 'options' => $options)) ?>
<?php endif ?>

<div class="ledger-both">
<?php include_partial('both_payment',array('byPaymentMethod' => $byPaymentMethod,'form' => $form)) ?>
<?php include_partial('both_price',array('byPrice' => $byPrice)) ?>
<div class="clear"></div>
<?php include_partial('both_value',array('byValue' => $byValue)) ?>
<div class="clear"></div>
<?php include_partial('both_user',array('byUser' => $byUser)) ?>
<?php if ( $manifestations ): ?>
<div class="clear"></div>
<?php include_partial('both_gauges',array('gauges' => $gauges)) ?>
<?php endif ?>
</div>

