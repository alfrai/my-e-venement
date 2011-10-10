<?php use_helper('Date','Number') ?>
<ul class="sf_form_field_events_list">
<?php foreach ($form->getObject()->Transactions as $transaction): ?>
<?php if ( $transaction->Tickets->count() > 0 ): ?>
  <?php
    $manifs = array();
    $prints = 0;
    foreach ( $transaction->Tickets as $ticket )
    {
      if ( $ticket->printed )
      {
        $prints++;
      }
      $manifs[$ticket->manifestation_id] = cross_app_link_to($ticket->Manifestation->getShortName(),'event','manifestation/show?id='.$ticket->manifestation_id);
    }
  ?>
  <li>
    <p class="infos">
      #<?php echo cross_app_link_to($transaction,'tck','ticket/sell?id='.$transaction->id) ?>,
      <?php echo format_date($transaction->created_at) ?>
      <?php $sum = 0; foreach ( $transaction->Payments as $pay ) $sum += $pay->value; ?>
      <?php echo __('for %%i%% ticket(s) and %%p%% paid',array(
        '%%i%%' => $transaction->Tickets->count(),
        '%%p%%' => format_currency($sum,'€'),
      )) ?>
     (<?php
        if ( $prints < $transaction->Tickets->count() )
        {
          if ( !$transaction->Order[0]->isNew() )
            echo __('Ordered').' ';
          else if ( $prints == 0 )
            echo __('All demanded').' ';
          
          if ( $prints > 0 )
            echo __('Partially printed').' ';
        }
        else
          echo __('All printed');
      ?>)
    </p>
    <p class="manifs"><?php
      echo implode('<br/>',$manifs);
    ?></p>
  </li>
<?php endif ?>
<?php endforeach ?>
</ul>
