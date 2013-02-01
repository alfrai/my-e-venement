  <?php
    $events = $sort = array();
    $total = array('nb' => 0, 'value' => 0);
    
    $objects = array($object);
    $config = $sf_data->getRaw('config');
    foreach ( $config['subobjects'] as $subobjects => $conf )
    foreach ( $object->$subobjects as $subobject )
      $objects[] = $subobject;
  ?>
  <ul class="events">
    <?php foreach ( $objects as $obj ): ?>
    <li>
      <?php if ( count($objects) > 1 ) echo $obj ?>
      <ul>
        <?php
          foreach ( $obj->Transactions as $transaction )
          foreach ( $transaction->Tickets as $ticket )
          if ( is_null($ticket->duplicating) && is_null($ticket->cancelling) && !$ticket->hasBeenCancelled() )
          if ( $ticket->printed || $ticket->integrated || $transaction->Order->count() > 0 )
          {
            if ( !isset($events[$ticket->Manifestation->Event->id]) )
              $events[$ticket->Manifestation->Event->id] = array(
                'happens_at' => 0,
                'event' => $ticket->Manifestation->Event,
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
          <?php echo cross_app_link_to($event['event'],'event','event/show?id='.$event['event']->id) ?>:
          <span title="chouette" class="nb"><?php echo $event['nb'] ?></span>
          <span class="value"><?php echo format_currency($event['value'],'€') ?></span>
        </li>
        <?php endforeach ?>
        <li class="total">
          <?php if ( $total['nb'] > 0 ): ?>
          <span class="event">Total</span>:
          <span class="nb"><?php echo $total['nb'] ?></span>
          <span class="value"><?php echo format_currency($total['value'],'€') ?></span>
          <?php else: ?>
          <?php echo __('No result',null,'sf_admin') ?>
          <?php endif ?>
        </li>
      </ul>
    </li>
  <?php endforeach ?>
  </ul>
