<?php include_partial('assets') ?>

<div class="ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1>
      <?php
        $values = $form->getValues();
        if ( !isset($values['dates']['from']) ) $values['dates']['from'] = date('Y-m-d',strtotime('1 month ago'));
        if ( !isset($values['dates']['to']) ) $values['dates']['to'] = date('Y-m-d',strtotime('tomorrow'));
      ?>
      <?php echo __('Sales Ledger') ?>
      (<?php echo __('from %%from%% to %%to%%',array('%%from%%' => format_date($values['dates']['from']), '%%to%%' => format_date($values['dates']['to']))) ?>)
    </h1>
  </div>
<table class="ui-widget-content ui-corner-all" id="ledger">
  <?php
    $total = array('qty' => 0, 'vat' => array(), 'value' => 0);
    $vat = array();
    
    foreach ( $events as $event )
    foreach ( $event->Manifestations as $manif )
      $vat[$manif->vat] = array();
  ?>
  <tbody><?php foreach ( $events as $event ): ?>
    <tr class="event">
      <?php
        $buf = $qty = $value = 0;
        foreach ( $event->Manifestations as $manif )
        {
          $qty += $manif->Tickets->count();
          
          if ( !in_array($manif->vat, $vat) )
            $vat[$manif->vat] = array($event->id => array(
              'total'    => 0,
              $manif->id => 0,
            ));
          
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
        $total['value'] += $value;
        $total['qty']   += $qty;
        
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
      <td class="id-qty"><?php $nb = $manif->Tickets->count(); foreach ( $manif->Tickets as $t ) if ( !is_null($t->cancelling) ) $nb-=2; echo $nb; ?></td>
      <td class="value"><?php $value = 0; foreach ( $manif->Tickets as $ticket ) $value += $ticket->value; echo format_currency($value,'€'); ?></td>
      <?php foreach ( $vat as $t ): if ( isset($t[$event->id][$manif->id]) ): ?>
      <td class="vat"><?php $buf += $t[$event->id][$manif->id]; echo format_currency($t[$event->id][$manif->id],'€') ?></td>
      <?php else: ?>
      <td class="vat"></td>
      <?php endif; endforeach ?>
      <td class="vat total"><?php echo format_currency($buf,'€') ?></td>
    </tr>
    <?php for ( $i = 0 ; $i < $manif->Tickets->count() ; $i++ ): ?>
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

<?php echo include_partial('criterias',array('form' => $form, 'ledger' => 'sales')) ?>
<div class="clear"></div>
</div>
