<?php $totals = array('tip' => 0, 'vat' => array(), 'pet' => 0) ?>
<table id="lines">
<tbody>
<?php for ( $i = 0 ; $i < $tickets->count() ; $i++ ): ?>
<?php $ticket = $tickets[$i] ?>
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
      if ( $i+1 < $tickets->count() )
      while ( $tickets[$i+1]['manifestation_id'] == $ticket->manifestation_id
           && $tickets[$i+1]['price_id']         == $ticket->price_id
           && $tickets[$i+1]['value']            == $ticket->value )
      {
        $qty++;
        $i++;
      }
      echo $qty;
    ?></td>
    <td class="pit"><?php echo format_currency($tip = $ticket->value * $qty,'€'); $totals['tip'] += $tip ?></td>
    <td class="vat"><?php echo format_currency(round($vat = $ticket->Manifestation->vat/100 * $tip,2),'€'); if ( !isset($totals['vat'][$ticket->Manifestation->vat]) ) $totals['vat'][$ticket->Manifestation->vat] = 0; $totals['vat'][$ticket->Manifestation->vat] += $vat ?></td>
    <td class="tep"><?php echo format_currency(round($pet = $ticket->value * $qty - $vat,2),'€'); $totals['pet'] += $pet ?></td>
  </tr>
<?php endfor ?>
</tbody>
<thead><tr>
  <th class="event"><span><?php echo __('Event') ?></span></th>
  <th class="date"><span><?php echo __('Date') ?></span></th>
  <th class="time"><span><?php echo __('Time') ?></span></th>
  <th class="location"><span><?php echo __('Location') ?></span></th>
  <th class="postalcode"><span><?php echo __('Postal code') ?></span></th>
  <th class="city"><span><?php echo __('City') ?></span></th>
  <th class="price"><span><?php echo __('Price') ?></span></th>
  <th class="up"><span><?php echo __('Unit TIP') ?></span></th>
  <th class="qty"><span><?php echo __('Qty') ?></span></th>
  <th class="pit"><span><?php echo __('TIP') ?></span></th>
  <th class="vat"><span><?php echo __('VAT') ?></span></th>
  <th class="tep"><span><?php echo __('PET') ?></span></th>
</tr></thead>
</thead>
</table>
