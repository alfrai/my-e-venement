<?php use_helper('Date','Number') ?>
<?php $maxsize = sfConfig::get('app_tickets_max_size') ?>
<div class="ticket">
  <div class="logo"><img src="" alt="" /></div>
  <div class="left">
    <p class="manifid">
      #<?php echo $ticket->Manifestation->id ?><span class="tariftop"><?php echo $ticket->price_name ?></span>
    </p>
    <p class="info <?php echo $ticket->Transaction->type ?> <?php echo $duplicate ? 'duplicate' : '' ?>"><span class="subtype"><?php echo __($ticket->Transaction->type) ?></span><span class="subtype"><?php echo $duplicate ? __('Duplicata') : '' ?></span></p>
    <p class="metaevt"><?php echo $ticket->Manifestation->Event->MetaEvent ?></p>
    <p class="datetime"><?php echo format_date($ticket->Manifestation->happens_at,'dddd dd MMMM yyyy HH:mm') ?></p>
    <p class="placeprice">
      <span class="place"><?php echo $ticket->Manifestation->Location ?></span>
      /
      <span class="price"><?php echo format_currency($ticket->value,'€') ?></span>
    </p>
    <p class="price_name"><span class="description"><?php echo $ticket->Price->description ?></span><span class="name"><?php echo $ticket->price_name ?></span> <span class="price"><?php echo format_currency($ticket->value,'€') ?></span></p>
    <p class="event"><?php echo strlen($buf = $ticket->Manifestation->Event) > 30 ? substr($buf,0,30).'...' : $buf ?></p>
    <p class="event-short"><?php echo strlen($buf = $ticket->Manifestation->Event->short_name) > 40 ? substr($buf,0,40).'...' : $buf ?></p>
    <p class="cie"><?php $creators = array(); $cpt = 0; foreach ( $ticket->Manifestation->Event->Companies as $company ) { if ( $cpt++ > 1 ) break; $creators[] .= $company; } echo implode(', ',$creators); ?></p>
    <p class="org"><?php $orgas = array(); $cpt = 0; foreach ( $ticket->Manifestation->Organizers as $orga ) { if ( $cpt++ > 2 ) break; $orgas[] = $orga; } echo implode(', ',$orgas); ?></p>
    <p class="seat"><?php echo $ticket->numerotation ? __('Seat n°%%s%%',array('%%s%%' => $ticket->numerotation)) : '' ?></p>
    <p class="transaction">
      <span class="date"><?php echo format_date($ticket->updated_at,'dd/MM/yyyy HH:mm') ?></span>
      /
      <span class="num">#<?php echo $ticket->Transaction->id ?>-<?php echo $sf_user->getId() ?></span>
    </p>
    <p class="ticket-bc"><?php
    switch ( sfConfig::get('app_tickets_id') ) {
    case 'qrcode':
      echo image_tag(url_for('ticket/barcode?id='.$ticket->id));
      break;
    default:
      echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$ticket->getIdBarcoded());
      break;
    }
    ?></p>
    <p class="spectator"><?php echo $ticket->Transaction->professional_id > 0 ? $ticket->Transaction->Professional->Organism : $ticket->Transaction->Contact ?></p>
    <p class="mentions">
      <span class="optional"><?php $mentions = sfConfig::get('app_tickets_mentions'); echo $mentions['optional'] ?></span>
      <?php if ( $ticket->cancelling ): ?>
        <span class="cancelled-id">#<?php echo $ticket->cancelling ?></span>
      <?php endif ?>
      <span class="ticket-id">#<?php echo $ticket->id ?></span>
      <span class="keep-it"><?php echo __('Keep it') ?></span>
      <span class="seating"><span><?php echo __('Free seating') ?></span></span>
    </p>
    <p class="workspace <?php echo $ticket->Manifestation->Gauges->count() > 1 ? 'has_many' : 'one' ?>">
      <?php echo $ticket->Gauge->Workspace->getNameForTicket() ?>
    </p>
  </div>
  <div class="right">
    <p class="manifid">
      #<?php echo $ticket->Manifestation->id ?><span class="tariftop"><?php echo $ticket->price_name ?></span>
    </p>
    <p class="info <?php echo $ticket->Transaction->type ?> <?php echo $duplicate ? 'duplicate' : '' ?>"><span class="subtype"><?php echo __($ticket->Transaction->type) ?></span><span class="subtype"><?php echo $duplicate ? __('Duplicata') : '' ?></span></p>
    <p class="metaevt"><?php echo $ticket->Manifestation->Event->MetaEvent ?></p>
    <p class="datetime"><?php echo format_date($ticket->Manifestation->happens_at,'dd/MM/yyyy HH:mm') ?></p>
    <p class="placeprice">
      <span class="place"><?php echo strlen($buf = $ticket->Manifestation->Location) > ($max = $maxsize['place'] ? $maxsize['place'] : 15) ? substr($buf,0,$max-3).'...' : $buf ?></span>
      /
      <span class="price"><?php echo format_currency($ticket->value,'€') ?></span>
    </p>
    <p class="spectator"><?php echo $ticket->Transaction->professional_id > 0 ? $ticket->Transaction->Professional->Organism : $ticket->Transaction->Contact ?></p>
    <p class="event"><?php echo strlen($buf = $ticket->Manifestation->Event) > 21 ? substr($buf,0,18).'...' : $buf ?></p>
    <p class="cie"><?php echo strlen($buf = implode(', ',$creators)) > 20 ? substr($buf,0,17).'...' : $buf; ?></p>
    <p class="org"><?php echo isset($orgas[0]) ? $orgas[0] : '' ?></p>
    <p class="seat"><?php echo $ticket->numerotation ? __('Seat n°%%s%%',array('%%s%%' => $ticket->numerotation)) : '' ?></p>
    <p class="transaction">
      <span class="date"><?php echo format_date($ticket->updated_at,'dd/MM/yyyy HH:mm') ?></span>
      /
      <span class="num">#<?php echo $ticket->Transaction->id ?>-<?php echo $sf_user->getId() ?></span>
    </p>
    <p class="mentions">
      <span class="keep-it"><?php echo __('Control') ?></span>
      <span class="ticket-id">#<?php echo $ticket->id ?></span>
    </p>
    <p class="workspace <?php echo $ticket->Manifestation->Gauges->count() > 1 ? 'has_many' : 'one' ?>">
      <?php echo $ticket->Gauge->Workspace->getNameForTicket() ?>
    </p>
  </div>
</div>
