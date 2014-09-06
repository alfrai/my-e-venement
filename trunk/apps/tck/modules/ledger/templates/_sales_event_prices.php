      <?php $ticket = $manif->Tickets[$i]; ?>
      <td class="event price"><?php echo __('%%price%% (by %%user%%)',array('%%price%%' => $ticket->price_name, '%%annul%%' => is_null($ticket->cancelling) ? __('cancel') : '', '%%user%%' => $ticket->User->name)) ?></td>
      <td class="see-more"></td>
      <td class="id-qty"><?php
        $qty = $k = $value = $taxes = 0;
        for ( $j = $i ; $j < $manif->Tickets->count() ; $j++ )
        if ( $manif->Tickets[$i]->price_name == $manif->Tickets[$j]->price_name
          && $manif->Tickets[$i]->sf_guard_user_id == $manif->Tickets[$j]->sf_guard_user_id
          && is_null($manif->Tickets[$i]->cancelling) == is_null($manif->Tickets[$j]->cancelling) )
        {
          $qty = is_null($manif->Tickets[$j]->cancelling)
            ? $qty + 1
            : $qty - 1;
          $k++;
          $value += $manif->Tickets[$j]->value;
          $taxes += $manif->Tickets[$j]->taxes;
        }
        $i += $k-1;
        echo $qty;
      ?></td>
      <td class="value"><?php echo format_currency($value,'€') ?></td>
      <td class="extra-taxes"><?php echo format_currency($taxes,'€') ?></td>
      <?php foreach ( $total['vat'] as $t => $v ): ?>
      <td class="vat"><?php
        if ( !sfConfig::get('app_ledger_sum_rounding_before',false)
          && strtotime($ticket->cancelling ? $ticket->created_at : ($ticket->printed_at ? $ticket->printed_at : $ticket->integrated_at)) >= strtotime(sfConfig::get('app_ledger_sum_rounding_before')) )
        if ( $manif->Tickets->count() < 25 )
        {
          $x = 0;
          foreach ( $manif->Tickets as $ticket )
          if ( $ticket->vat == $t )
            $x += round($ticket->value - $ticket->value/(1+$ticket->vat),2);
          echo $x ? format_currency($x,'€') : '';
        }
      ?></td>
      <?php endforeach ?>
      <td class="vat total"></td>
      <td class="value"></td>
