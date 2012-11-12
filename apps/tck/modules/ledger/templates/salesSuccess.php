<?php include_partial('global/flashes') ?>
<?php include_partial('assets') ?>

<div class="ui-widget-content ui-corner-all" id="sales-ledger">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php echo __('Sales Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array('%%from%%' => format_date($dates[0]), '%%to%%' => format_date($dates[1]))) ?>)
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
    $total = array('qty' => 0, 'vat' => array(), 'value' => 0);
    $vat = array();
    
    foreach ( $events as $event )
    foreach ( $event->Manifestations as $manif )
    {
      $vat[$manif->vat] = array();
      if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) )
        $total['qty'] += $manif->Tickets->count();
    }
    
    if ( $nb_tickets > sfConfig::get('app_ledger_max_tickets',5000) )
      $total['qty'] = $nb_tickets;
  ?>
  <tbody><?php foreach ( $events as $event ): ?>
    <tr class="event">
      <?php
        $buf = $qty = $value = 0;
        foreach ( $event->Manifestations as $manif )
        {
          if ( !isset($vat[$manif->vat]) )
            $vat[$manif->vat] = array($event->id => array(
              'total'    => 0,
              $manif->id => 0,
            ));
          
          if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) )
          {
            $qty += $manif->Tickets->count();
            foreach ( $manif->Tickets as $ticket )
            {
              if ( !is_null($ticket->cancelling) )
                $qty -= 2;
              
              $value += $ticket->value;
              $vat[$manif->vat][$event->id][$manif->id]
                += $ticket->value - $ticket->value / (1+$manif->vat/100);
              $vat[$manif->vat][$event->id]['total']
                += $ticket->value - $ticket->value / (1+$manif->vat/100);
            }
          }
          else
          {
            $infos = $manif->getInfosTickets();
            
            $value = $infos['value'];
            $qty = $infos['qty'];
            
            $vat[$manif->vat][$event->id][$manif->id]
              = $infos['value'] - $infos['value'] / (1+$manif->vat/100);
            $vat[$manif->vat][$event->id]['total']
              += $vat[$manif->vat][$event->id][$manif->id];
          }
        }
        
        $total['value'] += $value;
        //$total['qty'] += $qty;
        
        foreach ( $vat as $name => $arr )
        {
          if ( !isset($total['vat'][$name]) )
            $total['vat'][$name] = 0;
          $total['vat'][$name] += $arr[$event->id]['total'];
        }
      ?>
      <td class="event"><?php echo cross_app_link_to($event,'event','event/show?id='.$event->id) ?></td>
      <td class="see-more"><a href="#event-<?php echo $event->id ?>">-</a></td>
      <td class="id-qty"><?php echo $qty ?></td>
      <td class="value"><?php echo format_currency($value,'€'); $value ?></td>
      <?php foreach ( $vat as $name => $v ): ?>
      <td class="vat"><?php $buf += $v[$event->id]['total']; echo format_currency($v[$event->id]['total'],'€') ?></td>
      <?php endforeach ?>
      <td class="vat total"><?php echo format_currency($buf,'€') ?></td>
    </tr>
    <?php foreach ( $event->Manifestations as $manif ): $buf = 0; ?>
    <tr class="manif event-<?php echo $event->id ?>">
      <td class="event"><?php echo cross_app_link_to(format_date($manif->happens_at).' @ '.$manif->Location,'event','manifestation/show?id='.$manif->id) ?></td>
      <td class="see-more"><a href="#manif-<?php echo $manif->id ?>">-</a></td>
      <td class="id-qty">
        <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ): ?>
        <?php $nb = $manif->Tickets->count(); foreach ( $manif->Tickets as $t ) if ( !is_null($t->cancelling) ) $nb-=2; echo $nb; ?>
        <?php else: ?>
        <?php $infos = $manif->getInfosTickets(); echo $infos['qty']; ?>
        <?php endif ?>
      </td>
      <td class="value">
        <?php if ( $nb_tickets <= sfConfig::get('app_ledger_max_tickets',5000) ): ?>
        <?php $value = 0; foreach ( $manif->Tickets as $ticket ) $value += $ticket->value; echo format_currency($value,'€'); ?>
        <?php else: ?>
        <?php echo format_currency($infos['value'],'€'); ?>
        <?php endif ?>
      </td>
      <?php foreach ( $vat as $t ): if ( isset($t[$event->id][$manif->id]) ): ?>
      <td class="vat"><?php $buf += $t[$event->id][$manif->id]; echo format_currency($t[$event->id][$manif->id],'€') ?></td>
      <?php else: ?>
      <td class="vat"></td>
      <?php endif; endforeach ?>
      <td class="vat total"><?php echo format_currency($buf,'€') ?></td>
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
      <?php foreach ( $total['vat'] as $v ): ?>
      <td class="vat"></td>
      <?php endforeach ?>
      <td class="vat total"></td>
    </tr>
    <?php endfor; endforeach; endforeach; $buf = 0; ?>
  </tbody>
  <tfoot><tr class="total">
    <td class="event"><?php echo __('Total') ?></td>
    <td class="see-more"></td>
    <td class="id-qty"><?php echo $total['qty'] ?></td>
    <td class="value"><?php echo format_currency($total['value'],'€'); ?></td>
    <?php foreach ( $total['vat'] as $v ): ?>
    <td class="vat"><?php echo format_currency($v,'€'); $buf += $v; ?></td>
    <?php endforeach ?>
    <td class="vat total"><?php echo format_currency($buf,'€') ?></td>
  </tr></tfoot>
  <thead><tr>
    <td class="event"><?php echo __('Event') ?></td>
    <td class="see-more"></td>
    <td class="id-qty"><?php echo __('Qty') ?></td>
    <td class="value"><?php echo __('Value') ?></td>
    <?php foreach ( $vat as $name => $arr ): ?>
    <td class="vat"><?php echo format_number(round($name,2)).'%'; ?></td>
    <?php endforeach ?>
    <td class="vat total"><?php echo __('Total VAT') ?></td>
  </tr></thead>
</table>

<div class="clear"></div>
</div>
