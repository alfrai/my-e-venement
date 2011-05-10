<?php include_partial('assets') ?>

<div class="ui-widget-content ui-corner-all">
  <div class="fg-toolbar ui-widget-header ui-corner-all">
    <h1><?php echo __('Sales Ledger') ?></h1>
  </div>
<table class="ui-widget-content ui-corner-all" id="ledger">
  <?php
    $total = array('qty' => 0, 'vat' => array(), 'value' => 0);
    $vat = array();
    foreach ( $events as $event )
    foreach ( $event->Manifestations as $manif )
      $vat[$manif->vat] = 0;
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
            if ( $ticket->Transaction == 'cancellation' )
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
      <td class="see-more"><a href="#<?php echo $event->id ?>">+</a></td>
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
      <td class="see-more"></td>
      <td class="id-qty"><?php echo $manif->Tickets->count() ?></td>
      <td class="value"><?php $value = 0; foreach ( $manif->Tickets as $ticket ) $value += $ticket->value; echo format_currency($value,'€'); ?></td>
      <?php foreach ( $vat as $t ): if ( isset($t[$event->id][$manif->id]) ): ?>
      <td class="vat"><?php $buf += $t[$event->id][$manif->id]; echo format_currency($t[$event->id][$manif->id],'€') ?></td>
      <?php else: ?>
      <td class="vat"></td>
      <?php endif; endforeach ?>
      <td class="vat total"><?php echo format_currency($buf,'€') ?></td>
    </tr>
    <?php endforeach; endforeach; $buf = 0; ?>
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
