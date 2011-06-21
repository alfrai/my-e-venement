<?php use_helper('Date','Number') ?>
<ul class="sf_form_field_events_list">
<?php foreach ($contact->Transactions as $transaction): ?>
  <li>
    #<?php echo link_to($transaction,'tansaction/sell?id='.$transaction->id) ?>,
    <?php echo format_date($transaction->created_at) ?>
    <?php echo __('for %%i%% ticket(s) and',array('%%i%%' => $transaction->Tickets->count())) ?> 
    <?php $sum = 0; foreach ( $transaction->Payments as $pay ) $sum += $pay->value; echo format_currency($sum,'€'); ?> 
  </li>
<?php endforeach ?>
</ul>
