<?php $totals = array('tip' => 0, 'vat' => array(), 'pet' => 0) ?>
<table id="lines">
<tbody>
<?php for ( $i = 0 ; $i < count($transaction->Tickets) ; $i++ ): ?>
<?php
  $ticket = $transaction->Tickets[$i];
  if ( !$ticket->duplicate ):
?>
  <tr>
    <td class="event"><?php echo $ticket->Manifestation->Event ?></td>
    <td class="date"><?php echo format_date($ticket->Manifestation->happens_at) ?></td>
    <td class="time"><?php echo format_date($ticket->Manifestation->happens_at,'HH:mm') ?></td>
    <td class="location"><?php echo $ticket->Manifestation->Location ?></td>
    <td class="postalcode"><?php echo $ticket->Manifestation->Location->postalcode ?></td>
    <td class="city"><?php echo $ticket->Manifestation->Location->city ?></td>
    <td class="price"><?php echo $ticket->Price->description ?></td>
    <td class="up"><?php echo format_currency($ticket->value,'€') ?></td>
    <td class="qty"><?php
      $qty = 1;
      while (
        ++$i < count($transaction->Tickets)
        && $transaction->Tickets[$i]->manifestation_id == $ticket->manifestation_id
        && $transaction->Tickets[$i]->price_name == $ticket->price_name
        && $transaction->Tickets[$i]->value == $ticket->value )
      if ( !$transaction->Tickets[$i]->duplicate )
        $qty++;
      
      if ( $i < count($transaction->Tickets) )
        $i--;
      
      echo $qty;
    ?></td>
    <td class="tip"><?php echo format_currency($tip = $ticket->value * $qty,'€'); $totals['tip'] += $tip ?></td>
    <td class="vat"><?php echo format_currency(round($vat = $ticket->Manifestation->vat/100 * $tip,2),'€'); if ( !isset($totals['vat'][$ticket->Manifestation->vat]) ) $totals['vat'][$ticket->Manifestation->vat] = 0; $totals['vat'][$ticket->Manifestation->vat] += $vat ?></td>
    <td class="pet"><?php echo format_currency(round($pet = $ticket->value * $qty - $vat,2),'€'); $totals['pet'] += $pet ?></td>
  </tr>
<?php endif; endfor ?>
</tbody>
<thead><tr>
  <th class="event"><?php echo __('Event') ?></th>
  <th class="date"><?php echo __('Date') ?></th>
  <th class="time"><?php echo __('Time') ?></th>
  <th class="location"><?php echo __('Location') ?></th>
  <th class="postalcode"><?php echo __('Postal code') ?></th>
  <th class="city"><?php echo __('City') ?></th>
  <th class="price"><?php echo __('Price') ?></th>
  <th class="up"><?php echo __('Unit TIP') ?></th>
  <th class="qty"><?php echo __('Qty') ?></th>
  <th class="pit"><?php echo __('TIP') ?></th>
  <th class="vat"><?php echo __('VAT') ?></th>
  <th class="tep"><?php echo __('PET') ?></th>
</tr></thead>
</thead>
</table>
