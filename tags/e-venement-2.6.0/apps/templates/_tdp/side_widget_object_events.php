  <?php
    $events = $sort = array();
    $total = array('nb' => 0, 'value' => 0);
    
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
    <?php $cpt++ ?>
    <?php if ( $obj->Transactions->count() > 0 ): ?>
    <li class="events-<?php echo $cpt == 1 ? 'object' : 'subobject-'.$obj->id ?>">
      <?php if ( count($objects) > 1 ): ?>
      <h3><?php echo $obj ?></h3>
      <?php endif ?>
      <ul>
        <?php
          foreach ( $obj->Transactions as $transaction )
          if ( is_null($transaction->professional_id) || $cpt > 1 )
          foreach ( $transaction->Tickets as $ticket )
          if ( is_null($ticket->duplicating) && is_null($ticket->cancelling) && !$ticket->hasBeenCancelled() )
          if ( $ticket->printed_at || $ticket->integrated_at || $transaction->Order->count() > 0 )
          {
            if ( !isset($events[$ticket->Manifestation->Event->id]) )
              $events[$ticket->Manifestation->Event->id] = array(
                'happens_at' => 0,
                'event' => $ticket->Manifestation->Event,
                'title' => (string)$ticket->Manifestation->Event->MetaEvent,
                'nb' => 0,
                'value' => 0,
              );
            if ( $events[$ticket->Manifestation->Event->id]['happens_at'] < $ticket->Manifestation->happens_at )
              $sort[$ticket->Manifestation->Event->id] =
              $events[$ticket->Manifestation->Event->id]['happens_at'] = $ticket->Manifestation->happens_at;
            $events[$ticket->Manifestation->Event->id]['nb']++;
            $events[$ticket->Manifestation->Event->id]['value'] += $ticket->value;
            $total['nb']++;
            $total['value'] += $ticket->value;
          }
          
          // sorting by manifestation's date
          array_multisort($sort,$events);
          $events = array_reverse($events);
        ?>
        <?php foreach ( $events as $event ): ?>
        <li>
          <?php echo cross_app_link_to($event['event'],'event','event/show?id='.$event['event']->id,false,null,false, 'title="'.$event['title'].'"') ?>:
          <span class="nb"><?php echo $event['nb'] ?></span>
          <?php if ( $sf_user->hasCredential('tck-ledger-sales') ): ?><span class="value"><?php echo format_currency($event['value'],'€') ?></span><?php endif ?>
        </li>
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
