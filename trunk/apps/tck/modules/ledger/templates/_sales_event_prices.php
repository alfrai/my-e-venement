      <?php use_helper('Slug') ?>
      <?php $ticket = $manif->Tickets[$i]; ?>
      <td class="event price" data-price-id="<?php echo $ticket->price_id ? $ticket->price_id : slugify($ticket->price_name)?>" data-user-id="<?php echo $ticket->sf_guard_user_id ?>">
        <span class="with-user"><?php echo __('%%price%% (by %%user%%)',array('%%price%%' => $ticket->price_name, '%%annul%%' => is_null($ticket->cancelling) ? __('cancel') : '', '%%user%%' => $ticket->User)) ?></span>
        <span class="without-user"><?php echo $ticket->price_name ?></span>
      </td>
      <td class="see-more"></td>
      <td class="id-qty"><?php
        $qty = $k = $value = $taxes = 0;
        $x = array();
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
          if ( !isset($x[$manif->Tickets[$j]->vat]) )
            $x[$manif->Tickets[$j]->vat] = 0;
          $x[$manif->Tickets[$j]->vat] += round($manif->Tickets[$j]->value+$manif->Tickets[$j]->taxes - ($manif->Tickets[$j]->value+$manif->Tickets[$j]->taxes)/(1+$manif->Tickets->get($j)->vat),2);
        }
        $i += $k-1;
        echo $qty;
      ?></td>
      <td class="value" title="<?php echo __('PIT').' = '.__('TEP').' + '.__('VAT') ?>"><?php echo format_currency($value,'€') ?></td>
      <td class="extra-taxes" title="<?php echo __('Incl. VAT') ?>"><?php echo format_currency($taxes,'€') ?></td>
      <?php foreach ( $total['vat'] as $t => $v ): ?>
      <td class="vat"><?php
        if ( !sfConfig::get('app_ledger_sum_rounding_before',false) )
        if ( isset($x[$t]) )
          echo format_currency($x[$t],'€');
        /*
          && strtotime($ticket->cancelling ? $ticket->created_at : ($ticket->printed_at ? $ticket->printed_at : $ticket->integrated_at)) >= strtotime(sfConfig::get('app_ledger_sum_rounding_before')) )
        if ( $manif->Tickets->count() < 25 )
        {
          $x = 0;
          foreach ( $manif->Tickets as $ticket )
          if ( $ticket->vat == $t )
            $x += round($ticket->value - $ticket->value/(1+$ticket->vat),2);
          echo $x ? format_currency($x,'€') : '';
        }
        */
      ?></td>
      <?php endforeach ?>
      <td class="vat total"></td>
      <td class="tep"></td>
