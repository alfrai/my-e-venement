<?php $totals = array('tip' => 0, 'vat' => array(), 'pet' => 0, 'taxes' => 0) ?>
<table id="lines">
<thead><tr>
  <th class="event"><span><?php echo __('Product', null, 'li_accounting') ?></span></th>
  <th class="date"><span><?php echo __('Date', null, 'li_accounting') ?></span></th>
  <th class="time"><span><?php echo __('Time', null, 'li_accounting') ?></span></th>
  <th class="location"><span><?php echo __('Location', null, 'li_accounting') ?></span></th>
  <th class="postalcode"><span><?php echo __('Postal code', null, 'li_accounting') ?></span></th>
  <th class="city"><span><?php echo __('City', null, 'li_accounting') ?></span></th>
  <th class="price"><span><?php echo __('Price', null, 'li_accounting') ?></span></th>
  <th class="up"><span><?php echo __('UP Net', null, 'li_accounting') ?></span></th>
  <th class="qty"><span><?php echo __('Qty', null, 'li_accounting') ?></span></th>
  <th class="seats"><span><?php echo __('Seats', null, 'li_accounting') ?></span></th>
  <th class="extra-taxes"><span><?php echo __('Taxes', null, 'li_accounting') ?></span></th>
  <th class="pit"><span><?php echo __('TIP', null, 'li_accounting') ?></span></th>
  <th class="vat"><span><?php echo __('VAT', null, 'li_accounting') ?></span></th>
  <th class="tep"><span><?php echo __('PET', null, 'li_accounting') ?></span></th>
</tr></thead>
<tbody>
<?php for ( $i = 0 ; $i < $tickets->count() ; $i++ ): ?>
<?php $ticket = $tickets[$i] ?>
<?php if ( $ticket->id > 0 ): ?>
  <tr>
    <td class="event inline-modifiable"><?php echo $ticket->Manifestation->Event ?></td>
    <td class="date inline-modifiable"><?php echo format_date($ticket->Manifestation->happens_at) ?></td>
    <td class="time inline-modifiable"><?php echo format_date($ticket->Manifestation->happens_at,'HH:mm') ?></td>
    <td class="location inline-modifiable"><?php echo $ticket->Manifestation->Location ?></td>
    <td class="postalcode inline-modifiable"><?php echo $ticket->Manifestation->Location->postalcode ?></td>
    <td class="city inline-modifiable"><?php echo $ticket->Manifestation->Location->city ?></td>
    <td class="price"><?php echo $ticket->Price->description ?></td>
    <td class="up"><?php echo format_currency($ticket->value,'€') ?></td>
    <?php
      $qty = 0;
      $nums = array();
      $total = array('tip' => 0, 'taxes' => 0, 'vat' => 0, 'pet' => 0,);
      while ( $i < $tickets->count()
           && $tickets[$i]->manifestation_id == $ticket->manifestation_id
           && $tickets[$i]->price_id         == $ticket->price_id
           && $tickets[$i]->value            == $ticket->value )
      {
        if ( isset($nocancel) && !$nocancel || $tickets[$i]->Cancelling->count() == 0 )
        {
          $qty++;
          if ( $tickets[$i]->numerotation )
            $nums[] = $tickets[$i]->numerotation;
          $total['taxes'] += $tickets[$i]->taxes;
          $total['tip']   += $val = $tickets[$i]->value + $tickets[$i]->taxes;
          $total['pet']   += $pet = round($val/(1+$tickets[$i]->vat), 2);
          $total['vat']   += $vat = $val - $pet;
          if ( !isset($totals['vat'][$tickets[$i]->vat]) )
            $totals['vat'][$tickets[$i]->vat] = 0;
          $totals['vat'][$tickets[$i]->vat] += $vat;
        }
        $i++;
      }
      $i--; // rollback to process the last ticket that has been ignored
    ?>
    <td class="qty inline-modifiable"><?php echo $qty; ?></td>
    <td class="seats"><?php echo count($nums) > 20 ? '' : implode(', ', $nums) ?></td>
    <td class="extra-taxes"><?php echo $total['taxes'] ? format_currency($total['taxes'],'€') : '-'; $totals['taxes'] += $total['taxes']; ?></td>
    <td class="pit"><?php echo format_currency($total['tip'],'€'); $totals['tip'] += $total['tip']; ?></td>
    <td class="vat">
      <span class="value"><?php echo format_currency($total['vat'],'€') ?></span>
      <span class="percent"><?php echo $ticket->vat * 100 ?></span>
    </td>
    <td class="tep"><?php echo format_currency($total['pet'],'€'); $totals['pet'] += $total['pet'] ?></td>
  </tr>
<?php endif ?>
<?php endfor ?>
<?php for ( $i = 0 ; $i < count($products) ; $i++ ): ?>
<?php $product = $products[$i] ?>
<?php if ( $product->id > 0 ): ?>
  <tr>
    <td class="event inline-modifiable"><?php echo (string)$product ?></td>
    <td class="time inline-modifiable" colspan="2"><?php echo $product->code ?></td>
    <td class="location inline-modifiable" colspan="3"><?php echo $product->declination ?></td>
    <td class="price"><?php echo $product->price_id ? $product->Price->description : $product->price_name ?></td>
    <td class="up"><?php echo format_currency($product->value,'€') ?></td>
    <?php
      $qty = 0;
      $total = array('tip' => 0, 'taxes' => 0, 'vat' => 0, 'pet' => 0,);
      while ( $i < $products->count()
           && $products[$i]->code == $product->code
           && $products[$i]->price_id         == $product->price_id
           && $products[$i]->value            == $product->value )
      {
        $qty++;
        $total['tip']   += $val = $products[$i]->value;
        $total['pet']   += $pet = round($val/(1+$products[$i]->vat), 2);
        $total['vat']   += $vat = $val - $pet;
        if ( !isset($totals['vat'][$products[$i]->vat]) )
          $totals['vat'][$products[$i]->vat] = 0;
        $totals['vat'][$products[$i]->vat] += $vat;
        $i++;
      }
      $i--; // rollback to process the last ticket that has been ignored
    ?>
    <td class="qty inline-modifiable"><?php echo $qty; ?></td>
    <td class="seats"></td>
    <td class="extra-taxes"></td>
    <td class="pit"><?php echo format_currency($total['tip'],'€'); $totals['tip'] += $total['tip']; ?></td>
    <td class="vat">
      <span class="value"><?php echo format_currency($total['vat'],'€') ?></span>
      <span class="percent"><?php echo $product->vat * 100 ?></span>
    </td>
    <td class="tep"><?php echo format_currency($total['pet'],'€'); $totals['pet'] += $total['pet'] ?></td>
  </tr>
<?php endif ?>
<?php endfor ?>
</tbody>
</table>
