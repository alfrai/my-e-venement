  <?php
    $objects = array();
    if ( $object->hasRelation('Transactions') )
      $objects[] = $object;
    
    $config = $sf_data->getRaw('config');
    foreach ( $config['subobjects'] as $subobjects => $conf )
    foreach ( $object->$subobjects as $subobject )
    if ( $subobject->hasRelation('Transactions') )
      $objects[] = $subobject;
    $cpt = 0;
  ?>
  <ul class="events">
    <?php foreach ( $objects as $obj ): ?>
    <?php $total = array('nb' => 0, 'value' => 0); ?>
    <?php $cpt++ ?>
    <?php if ( $obj->Transactions->count() > 0 ): ?>
    <li class="events-<?php echo $cpt == 1 ? 'object' : 'subobject-'.$obj->id ?>">
      <?php if ( count($objects) > 1 ): ?>
      <h3><?php echo $obj ?></h3>
      <?php endif ?>
      <ul class="metaevents">
        <?php
          $events = $sort = array();
          foreach ( $obj->Transactions as $transaction )
          if ( is_null($transaction->professional_id) || $cpt > 1 )
          foreach ( $transaction->Tickets as $ticket )
          if ( is_null($ticket->duplicating) && is_null($ticket->cancelling) && !$ticket->hasBeenCancelled() )
          if ( $ticket->printed_at || $ticket->integrated_at || $transaction->Order->count() > 0 )
          {
            if ( !isset($events[$ticket->Manifestation->Event->meta_event_id]) )
              $events[$ticket->Manifestation->Event->meta_event_id] = array('name' => (string)$ticket->Manifestation->Event->MetaEvent);
            if ( !isset($events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]) )
              $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id] = array(
                'happens_at' => 0,
                'event' => $ticket->Manifestation->Event,
                'title' => (string)$ticket->Manifestation->Event->MetaEvent,
                'nb' => 0,
                'value' => 0,
                'transaction_ids' => array()
              );
            if ( $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['happens_at'] < $ticket->Manifestation->happens_at )
              $sort[$ticket->Manifestation->Event->id] = $ticket->Manifestation->Event->MetaEvent->name.
                ($events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['happens_at'] = $ticket->Manifestation->happens_at);
            $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['nb']++;
            $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['value'] += $ticket->value;
            $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['transaction_links'][(($p = $ticket->printed_at || $ticket->integrated_at || $ticket->cancelling) ? 'p' : 'r').$ticket->transaction_id]
              = '#'.cross_app_link_to($ticket->transaction_id, 'tck', 'ticket/sell?id='.$ticket->transaction_id, false, null, false, $p ? 'title="'.__('All printed').'"' : 'class="not-printed" title="'.__('Ordered').'"');
            $total['nb']++;
            $total['value'] += $ticket->value;
          }
          
          // sorting by manifestation's date
          array_multisort($sort,$events);
          $events = array_reverse($events);
        ?>
        <?php foreach ( $events as $id => $meta_event ): ?>
        <!-- METAEVT -->
        <li class="metaevent <?php echo in_array($id, array_keys($sf_user->getMetaEventsCredentials()->getRawValue())) ? 'hidden' : '' ?>">
        <?php foreach ( $meta_event as $id => $event ): ?>
        <?php if ( $id == 'name' ): ?>
          <?php if ( method_exists($object, 'getStatsSeatRank') ): ?>
            <?php $stats = $object->getStatsSeatRank($id); ?>
            <span class="seat-rank">
              <span class="qty"><?php echo __('Qty: %%qty%%', array('%%qty%%' => $stats['qty'])) ?></span>
              <span class="avg"><?php echo __('Avg: %%avg%%', array('%%avg%%' => format_number(number_format($stats['avg'],1)))) ?></span>
              <span class="std-dev"><?php echo __('Std deviation: %%sd%%', array('%%sd%%' => format_number(number_format($stats['std-dev'],1)))) ?></span>
            </span>
          <?php endif ?>
          <span class="name"><?php echo $event ?></span>
          <ul class="events">
        <?php else: ?>
          <!-- EVENT -->
          <li>
            <?php echo cross_app_link_to($event['event'],'event','event/show?id='.$event['event']->id,false,null,false, 'title="'.$event['title'].'"') ?>:
            <?php if ( $sf_user->hasCredential('tck-transaction') ): ?><span class="transactions ui-widget-content ui-corner-all"><?php echo implode('<br/>', $event['transaction_links']) ?></span><?php endif ?>
            <span class="nb"><?php echo $event['nb'] ?></span>
            <?php if ( $sf_user->hasCredential('tck-ledger-sales') ): ?><span class="value"><?php echo format_currency($event['value'],'€') ?></span><?php endif ?>
          </li>
          <!-- /EVENT -->
        <?php endif ?>
        <?php endforeach ?>
        </ul></li>
        <!-- /METAEVT -->
        <?php endforeach ?>
        <li class="total">
          <?php if ( $total['nb'] > 0 ): ?>
          <span class="event">Total</span>:
          <span class="nb"><?php echo $total['nb'] ?></span>
          <?php if ( $sf_user->hasCredential('tck-ledger-sales') ): ?><span class="value"><?php echo format_currency($total['value'],'€') ?></span><?php endif ?>
          <?php endif ?>
        </li>
      </ul>
    </li>
    <?php endif ?>
    <?php endforeach ?>
    <?php if ( count($objects) == 0 || $total['nb'] == 0 ): ?>
    <li><?php echo __('No result',null,'sf_admin') ?></li>
    <?php endif ?>
  </ul>
