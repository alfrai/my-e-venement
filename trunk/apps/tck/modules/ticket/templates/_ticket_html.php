<?php use_helper('Date','Number') ?>
<div class="ticket">
  <div class="logo"><img src="" alt="" /></div>
  <div class="left">
    <p class="manifid">
      #<?php echo $ticket->Manifestation->id ?><span class="tariftop"><?php echo $ticket->price_name ?></span>
    </p>
    <p class="info <?php echo $ticket->Transaction->type ?> <?php echo $duplicate ? 'duplicate' : '' ?>"><span class="subtype"><?php echo __($ticket->Transaction->type) ?></span><span class="subtype"><?php echo $duplicate ? __('Duplicata') : '' ?></span></p>
    <p class="metaevt"><?php echo $ticket->Manifestation->Event->MetaEvent ?></p>
    <p class="datetime"><?php echo format_date($ticket->Manifestation->happens_at,'dd MMMM yyyy HH:mm') ?></p>
    <p class="placeprice">
      <span class="place"><?php echo $ticket->Manifestation->Location ?></span>
      /
      <span class="price"><?php echo format_currency($ticket->value,'€') ?></span>
    </p>
    <p class="event"><?php echo strlen($buf = $ticket->Manifestation->Event) > 30 ? substr($buf,0,30).'...' : $buf ?></p>
    <p class="cie"><?php $creators = array(); $cpt = 0; foreach ( $ticket->Manifestation->Event->Companies as $company ) { if ( $cpt++ > 1 ) break; $creators[] .= $company; } echo implode(', ',$creators); ?></p>
    <p class="org"><?php $orgas = array(); $cpt = 0; foreach ( $ticket->Manifestation->Organizers as $orga ) { if ( $cpt++ > 2 ) break; $orgas[] = $orga; } echo implode(', ',$orgas); ?></p>
    <p class="seat"><?php echo $ticket->numerotation ? __('Seat n°%%s%%',array('%%s%%' => $ticket->numerotation)) : '' ?></p>
    <p class="transaction">
      <span class="date"><?php echo format_date($ticket->updated_at,'dd/MM/yyyy HH:mm') ?></span>
      /
      <span class="num">#<?php echo $ticket->Transaction->id ?>-<?php echo $sf_user->getId() ?></span>
    </p>
    <p class="ticket-bc"><img src="<?php echo url_for('ticket/barcode?id='.$ticket->id) ?>" alt=""></p>
    <p class="mentions">
      <span class="optional"><?php echo sfConfig::get('app_tickets_mentions_optional') ?></span>
      <?php if ( $ticket->cancelling ): ?>
        <span class="cancelled-id">#<?php echo $ticket->cancelling ?></span>
      <?php endif ?>
      <span class="ticket-id">#<?php echo $ticket->id ?></span>
      <span class="keep-it"><?php echo __('Keep it') ?></span>
    </p>
  </div>
  <div class="right">
    <p class="manifid">
      #<?php echo $ticket->Manifestation->id ?><span class="tariftop"><?php echo $ticket->price_name ?></span>
    </p>
    <p class="info <?php echo $ticket->Transaction->type ?> <?php echo $duplicate ? 'duplicate' : '' ?>"><span class="subtype"><?php echo __($ticket->Transaction->type) ?></span><span class="subtype"><?php echo $duplicate ? __('Duplicata') : '' ?></span></p>
    <p class="metaevt"><?php echo $ticket->Manifestation->Event->MetaEvent ?></p>
    <p class="datetime"><?php echo format_date($ticket->Manifestation->happens_at,'dd MMMM yyyy HH:mm') ?></p>
    <p class="placeprice">
      <span class="place"><?php echo strlen($buf = $ticket->Manifestation->Location) > 15 ? substr($buf,0,12).'...' : $buf ?></span>
      /
      <span class="price"><?php echo format_currency($ticket->value,'€') ?></span>
    </p>
    <p class="event"><?php echo strlen($buf = $ticket->Manifestation->Event) > 18 ? substr($buf,0,15).'...' : $buf ?></p>
    <p class="cie"><?php echo strlen($buf = implode(', ',$creators)) > 20 ? substr($buf,0,17).'...' : $buf; ?></p>
    <p class="org"><?php echo isset($orgas[0]) ? $orgas[0] : '' ?></p>
    <p class="seat"><?php echo $ticket->numerotation ? __('Seat n°%%s%%',array('%%s%%' => $ticket->numerotation)) : '' ?></p>
    <p class="transaction">
      <span class="date"><?php echo format_date($ticket->updated_at,'dd/MM/yyyy HH:mm') ?></span>
      /
      <span class="num">#<?php echo $ticket->Transaction->id ?>-<?php echo $sf_user->getId() ?></span>
    </p>
    <p class="mentions">
      <span class="keep-it"><?php echo __('Keep it') ?></span>
      <span class="ticket-id">#<?php echo $ticket->id ?></span>
    </p>
  </div>
