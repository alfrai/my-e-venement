<?php use_helper('CrossAppLink') ?>
<?php include_partial('assets') ?>
<?php include_partial('global/flashes') ?>

<div class="ui-widget-content ui-corner-all sf_admin_edit" id="sf_admin_container">
  <div class="fg-toolbar ui-widget-header ui-corner-all action">
    <h1><?php echo __('Transaction closed') ?></h1>
  </div>
  <div class="ui-corner-all ui-widget-content action closed">
    <p class="transaction_id"><?php echo __('Transaction #<a href="%%url%%">%%tid%%</a>',array(
      '%%tid%%' => $transaction->id,
      '%%url%%' => url_for('ticket/sell?id='.$transaction),
    )) ?></p>
    <p class="thanks">
      <?php echo __('Thanks <a href="%%url%%">%%t%% %%f%% %%n%%</a>',array(
        '%%t%%' => $transaction->Contact->title,
        '%%f%%' => $transaction->Contact->firstname,
        '%%n%%' => $transaction->Contact->name,
        '%%url%%' => cross_app_url_for('rp','contact/show?id='.$transaction->Contact->id),
      )) ?>
    </p>
    <div class="transaction_new">
      <p><?php
        echo __('&lt;&lt; <a href="%%url%%">Back</a> to a new transaction',array(
          '%%url%%' => url_for('ticket/sell'),
        ))
      ?></p>
      <?php
        $manifs = array();
        foreach ( $transaction->Tickets as $ticket )
          $manifs[$ticket->manifestation_id] = $ticket->manifestation_id;
      ?>
      <p><?php echo __('&lt;&lt; <a href="%%url%%">Back</a> to a new transaction',array(
        '%%url%%' => url_for('ticket/sell#manif-'.implode(',#manif-',$manifs)),
        )) ?><br/><?php echo __('with the same initial selections')
      ?></p>
    </div>
  </div>
</div>
