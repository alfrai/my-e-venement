<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>
<?php use_javascript('helper-csv') ?>
<?php use_stylesheet('tck-ledger-both','',array('media' => 'all')) ?>

<div><div class="ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php if ( $manifestations || $workspaces ): ?>
      <?php echo format_number_choice('[1]Manifestation ledger|(1,+Inf]Manifestations ledger',null,$workspaces ? 2 : $manifestations->count()) ?>
      <?php else: ?>
      <?php echo __('Ticketting Detailed Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array(
        '%%from%%' => format_date(strtotime($options['dates'][0])),
        '%%to%%' => format_date(strtotime($options['dates'][1])),
      )) ?>)
      <?php endif ?>
    </h1>
    <a
      href="#"
      title="<?php echo __('Save','','sf_admin') ?>"
      class="record ui-corner-all csv-js"
      onclick="javascript: $(this).closest('#content').find('table').downloadCSV($(this).closest('.ui-widget-header').find('h2').text());"
    ><span><?php echo __('Save','','sf_admin') ?></span></a>
  </div>
</div></div>

<?php include_partial('criterias',array('form' => $form, 'ledger' => 'both')) ?>

<?php
  $arr = array();
  foreach ( array('manifestations', 'users', 'workspaces', 'dates') as $var )
    $arr[$var] = isset($$var) ? $$var : false;
?>
<?php include_partial('show_criterias',$arr) ?>

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
<!--<div class="clear"></div>-->
<?php include_partial('both_taxes',array('taxes' => $taxes)) ?>
<div class="clear"></div>
<?php include_partial('both_user',array('byUser' => $byUser)) ?>
<?php if ( $manifestations ): ?>
<div class="clear"></div>
<?php include_partial('both_gauges',array('gauges' => $gauges)) ?>
<?php endif ?>
</div>

