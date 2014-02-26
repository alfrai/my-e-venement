<?php include_partial('global/flashes') ?>
<?php include_partial('assets') ?>

<div class="ui-widget-content ui-corner-all" id="sales-ledger">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php echo __('Sales Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array('%%from%%' => format_date(strtotime($dates[0])), '%%to%%' => format_date(strtotime($dates[1])))) ?>)
    </h1>
  </div>

<?php echo include_partial('criterias',array('form' => $form, 'ledger' => 'sales')) ?>

<?php if ( $users ): ?>
<?php include_partial('users',array('users' => $users)) ?>
<?php endif ?>

<?php $criterias = $form->getValues() ?>
<?php if ( $criterias['not-yet-printed'] || $criterias['tck_value_date_payment'] ): ?>
<div class="ui-widget-content ui-corner-all criterias" id="extra-criterias">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h2><?php echo __("Extra criterias") ?></h2>
  </div>
  <ul>
    <?php if ( $criterias['not-yet-printed'] ): ?>
    <li><?php echo __('Display not-yet-printed tickets') ?></li>
    <?php endif ?>
    <?php if ( $criterias['tck_value_date_payment'] ): ?>
    <li><?php echo __('Display tickets from payment date') ?></li>
    <?php endif ?>
  </ul>
</div>
<?php endif ?>


<table class="ui-widget-content ui-corner-all" id="ledger">
  <?php
    $vat = array();
    $total = $sf_data->getRaw('total');
    
    // total qty
    foreach ( $events as $event )
    foreach ( $event->Manifestations as $manif )
    {
      // taxes initialization
      foreach ( $total['vat'] as $key => $value )
      {
        if ( !isset($vat[$key]) )
          $vat[$key] = array('total' => 0);
        if ( !isset($vat[$key][$event->id]) )
          $vat[$key][$event->id] = array();
        $vat[$key][$event->id][$manif->id] = 0;
      }
      
      if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) )
      foreach ( $manif->Tickets as $ticket )
        $total['qty'] += is_null($ticket->cancelling)*2-1;
    }
    
    $arr = array();
  ?>
  <tbody><?php foreach ( $events as $event ): ?>
    <tr class="event">
      <?php
        $local_vat = $qty = $value = 0;
        $infos = array();
        
        foreach ( $event->Manifestations as $manif )
        if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) )
        {
          $tmp = 0;
          $qty += $manif->Tickets->count();
          foreach ( $manif->Tickets as $ticket )
          {
            if ( !is_null($ticket->cancelling) )
              $qty -= 2;
            
            // extremely weird behaviour, only for specific cases... it's about an early error in the VAT calculation in e-venement
            $time = strtotime($ticket->cancelling ? $ticket->created_at : ($ticket->printed_at ? $ticket->printed_at : $ticket->integrated_at));
            $tmp = sfConfig::get('app_ledger_sum_rounding_before',false) && $time < strtotime(sfConfig::get('app_ledger_sum_rounding_before'))
              ? $ticket->value - $ticket->value / (1+$ticket->vat) // exception
              : round($ticket->value - $ticket->value / (1+$ticket->vat),2); // regular
            
            // taxes feeding
            $vat[$ticket->vat][$event->id][$manif->id]
              += $tmp;
            
            // total feeding
            $total['vat'][$ticket->vat] += $tmp;
            $total['value'] += $ticket->value;
            $value += $ticket->value;
          }
        }
        else // more tickets than the limit
        {
          $infos[$manif->id] = $manif->getInfosTickets($sf_data->getRaw('options'));
          
          $total['value'] += $infos[$manif->id]['value'];
          $value += $infos[$manif->id]['value'];
          $qty += $infos[$manif->id]['qty'];
          
          foreach ( $infos[$manif->id]['vat'] as $rate => $amount )
          {
            $vat[$rate][$event->id][$manif->id] = $amount; // taxes feeding
            $total['vat'][$rate] += $amount; // total feeding
          }
        } // endif; endforeach;
        
        // extremely weird behaviour, only for specific cases... it's about an early mysanalysis in the VAT calculation in e-venement
        if ( sfConfig::get('app_ledger_sum_rounding_before',false) && strtotime(sfConfig::get('app_ledger_sum_rounding_before',false)) > strtotime($dates[0]) )
        {
          // initialization
          foreach ( $total['vat'] as $rate => $amount )
            $total['vat'][$rate] = 0;
          
          // completions
          foreach ( $vat as $rate => $content )
          foreach ( $content as $event_id => $manifs )
          if ( $event_id !== 'total' )
          foreach ( $manifs as $manif_id => $manif )
          {
            $vat[$rate][$event_id][$manif_id] = round($manif,2);
            $total['vat'][$rate] += round($manif,2);
          }
        }
      ?>
      <td class="event"><?php echo cross_app_link_to($event,'event','event/show?id='.$event->id) ?></td>
      <td class="see-more"><a href="#event-<?php echo $event->id ?>">-</a></td>
      <td class="id-qty"><?php echo $qty ?></td>
      <td class="value"><?php echo format_currency($value,'€') ?></td>
      <?php foreach ( $vat as $name => $v ): ?>
      <td class="vat">
        <?php
          $tmp = 0;
          if ( isset($v[$event->id]) )
          foreach ( $v[$event->id] as $m )
            $tmp += round($m,2);
          $local_vat += $tmp;
        ?>
        <?php echo format_currency($tmp,'€') ?>
      </td>
      <?php endforeach ?>
      <td class="vat total"><?php echo format_currency($local_vat,'€'); ?></td>
      <td class="tep"><?php echo format_currency($value - round($local_vat,2),'€') ?></td>
    </tr>
    <?php foreach ( $event->Manifestations as $manif ): $local_vat = 0; ?>
    <tr class="manif event-<?php echo $event->id ?>">
      <td class="event"><?php echo cross_app_link_to(format_date($manif->happens_at).' @ '.$manif->Location,'event','manifestation/show?id='.$manif->id) ?></td>
      <td class="see-more"><a href="#manif-<?php echo $manif->id ?>">-</a></td>
      <td class="id-qty">
        <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ): ?>
        <?php $nb = $manif->Tickets->count(); foreach ( $manif->Tickets as $t ) if ( !is_null($t->cancelling) ) $nb-=2; echo $nb; ?>
        <?php else: ?>
        <?php echo $infos[$manif->id]['qty']; $total['qty'] += $infos[$manif->id]['qty']; ?>
        <?php endif ?>
      </td>
      <td class="value">
        <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ): ?>
        <?php $value = 0; foreach ( $manif->Tickets as $ticket ) $value += $ticket->value; echo format_currency($value,'€'); ?>
        <?php else: ?>
        <?php echo format_currency($value = $infos[$manif->id]['value'],'€'); ?>
        <?php endif ?>
      </td>
      <?php foreach ( $vat as $t ): if ( isset($t[$event->id][$manif->id]) ): ?>
      <td class="vat"><?php $local_vat += round($t[$event->id][$manif->id],2); echo format_currency(round($t[$event->id][$manif->id],2),'€') ?></td>
      <?php else: ?>
      <td class="vat"></td>
      <?php endif; endforeach ?>
      <td class="vat total"><?php echo format_currency($local_vat,'€') ?></td>
      <td class="tep"><?php echo format_currency($value - $local_vat,'€') ?></td>
    </tr>
    <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ) for ( $i = 0 ; $i < $manif->Tickets->count() ; $i++ ): ?>
    <tr class="prices manif-<?php echo $manif->id ?>">
      <?php $ticket = $manif->Tickets[$i]; ?>
      <td class="event price"><?php echo __('%%price%% (by %%user%%)',array('%%price%%' => $ticket->price_name, '%%annul%%' => is_null($ticket->cancelling) ? __('cancel') : '', '%%user%%' => $ticket->User->name)) ?></td>
      <td class="see-more"></td>
      <td class="id-qty"><?php
        $qty = $k = $value = 0;
        for ( $j = $i ; $j < $manif->Tickets->count() ; $j++ )
        if ( $manif->Tickets->get($i)->price_name == $manif->Tickets->get($j)->price_name
          && $manif->Tickets->get($i)->sf_guard_user_id == $manif->Tickets->get($j)->sf_guard_user_id
          && is_null($manif->Tickets->get($i)->cancelling) == is_null($manif->Tickets->get($j)->cancelling) )
        {
          $qty = is_null($manif->Tickets->get($j)->cancelling)
            ? $qty + 1
            : $qty - 1;
          $k++;
          $value += $manif->Tickets->get($j)->value;
        }
        $i += $k-1;
        echo $qty;
      ?></td>
      <td class="value"><?php echo format_currency($value,'€') ?></td>
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
          echo format_currency($x,'€');
        }
      ?></td>
      <?php endforeach ?>
      <td class="vat total"></td>
      <td class="value"></td>
    </tr>
    <?php endfor; endforeach; endforeach; $local_vat = 0; ?>
  </tbody>
  <tfoot><tr class="total">
    <td class="event"><?php echo __('Total') ?></td>
    <td class="see-more"></td>
    <td class="id-qty"><?php echo $total['qty'] ?></td>
    <td class="value"><?php echo format_currency($total['value'],'€'); ?></td>
    <?php foreach ( $total['vat'] as $v ): ?>
    <td class="vat"><?php echo format_currency(round($v,2),'€'); $local_vat += round($v,2); ?></td>
    <?php endforeach ?>
    <td class="vat total"><?php echo format_currency($local_vat,'€') ?></td>
    <td class="value"><?php echo format_currency(round($total['value'],2)-$local_vat,'€'); ?></td>
  </tr></tfoot>
  <thead><tr>
    <td class="event"><?php echo __('Event') ?></td>
    <td class="see-more"></td>
    <td class="id-qty"><?php echo __('Qty') ?></td>
    <td class="value"><?php echo __('PIT') ?></td>
    <?php foreach ( $vat as $name => $arr ): ?>
    <td class="vat"><?php echo format_number(round($name*100,2)).'%'; ?></td>
    <?php endforeach ?>
    <td class="vat total"><?php echo __('Tot.VAT') ?></td>
    <td class="tep"><?php echo __('TEP') ?></td>
  </tr></thead>
</table>

<div class="clear"></div>
</div>
