<?php use_helper('Date','Number') ?>
<ul class="sf_form_field_events_list">
<?php foreach ($contact->Transactions as $transaction): ?>
<?php if ( $transaction->Tickets->count() > 0 ): ?>
  <li>
    #<?php echo cross_app_link_to($transaction,'tck','ticket/sell?id='.$transaction->id) ?>,
    <?php echo format_date($transaction->created_at) ?>
    <?php echo __('for %%i%% ticket(s) and',array('%%i%%' => $transaction->Tickets->count())) ?> 
    <?php $sum = 0; foreach ( $transaction->Payments as $pay ) $sum += $pay->value; echo format_currency($sum,'€'); ?> 
    <br/>
    <?php
      $manifs = array();
      foreach ( $transaction->Tickets as $ticket )
        $manifs[$ticket->manifestation_id] = cross_app_link_to($ticket->Manifestation->getShortName(),'event','manifestation/show?id='.$ticket->manifestation_id);
      echo implode(', ',$manifs);
    ?>
  </li>
<?php endif ?>
<?php endforeach ?>
</ul>
