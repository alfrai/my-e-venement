<?php use_helper('Date','Number') ?>
<?php
	$maxsize = sfConfig::get('app_tickets_max_size');
  $maxsize['event_name'] = isset($maxsize['event_name']) && intval($maxsize['event_name']) != 0 ? intval($maxsize['event_name']) : 30;
  $maxsize['event_shortname'] = isset($maxsize['event_shortname']) && intval($maxsize['event_shortname']) != 0 ? intval($maxsize['event_shortname']) : 40;
  $maxsize['event_name_right'] = isset($maxsize['event_name_right']) && intval($maxsize['event_name_right']) != 0 ? intval($maxsize['event_name_right']) : 21;
  $maxsize['place'] = isset($maxsize['place']) && intval($maxsize['place']) != 0 ? intval($maxsize['place']) : 30;
?>
<div class="ticket">
  <div class="logo"><?php echo image_tag(sfConfig::get('app_tickets_logo')) ?></div>
  <div class="left">
    <p class="manifid">
      <?php echo $ticket->Manifestation->id ?><span class="tariftop"><?php echo $ticket->price_name ?></span>
    </p>
    <p class="info <?php echo $ticket->Transaction->type ?> <?php echo $duplicate ? 'duplicate' : '' ?>"><span class="subtype"><?php echo __($ticket->Transaction->type) ?></span><span class="subtype"><?php echo $duplicate ? __('Duplicata') : '' ?></span></p>
    <p class="metaevt"><?php echo $ticket->Manifestation->Event->MetaEvent ?></p>
    <p class="datetime"><?php echo format_date($ticket->Manifestation->happens_at,'dddd dd MMMM yyyy HH:mm') ?></p>
    <p class="placeprice">
      <span class="place"><?php echo $ticket->Manifestation->Location ?></span>
      <span class="address"><?php echo $ticket->Manifestation->Location->address.' - '.$ticket->Manifestation->Location->city ?></span>
      /
      <span class="price"><?php echo format_currency($ticket->value,'€') ?></span>
    </p>
    <p class="price_name"><span class="description"><?php echo $ticket->Price->description ?></span><span class="name"><?php echo $ticket->price_name ?></span> <span class="price"><?php echo format_currency($ticket->value,'€') ?></span></p>
    <p class="price_vat"><span class="description"><?php echo $ticket->Manifestation->Vat->value*100 ?>&nbsp;%</span> - <span class="value"><?php echo format_currency($ticket->value*$ticket->Manifestation->Vat->value,'€') ?></span></p>
    <p class="event"><?php echo strlen($buf = (string)$ticket->Manifestation->Event) > $maxsize['event_name'] ? mb_substr(nl2br($buf),0,$maxsize['event_name']).'...' : nl2br($buf) ?></p>
    <p class="event-short"><?php echo strlen($buf = $ticket->Manifestation->Event->short_name) > $maxsize['event_shortname'] ? mb_substr($buf,0,$maxsize['event_shortname']).'...' : $buf ?></p>
    <p class="cie"><?php $creators = array(); $cpt = 0; foreach ( $ticket->Manifestation->Event->Companies as $company ) { if ( $cpt++ > 1 ) break; $creators[] .= $company->name; } echo implode(', ',$creators); ?></p>
    <p class="org"><span class="orgas"><?php $orgas = array(sfConfig::get('app_seller_name')); $cpt = 0; foreach ( $ticket->Manifestation->Organizers as $orga ) { if ( $cpt++ > 2 ) break; if ( strpos($orgas[0],$orga->name) !== false ) $orgas[] = $orga->name; else $cpt--; } echo implode('</span>, <span class="orgas">',$orgas); ?></span></p>
    <p class="seat"><?php echo $ticket->numerotation ? __('Seat n°%%s%%',array('%%s%%' => $ticket->numerotation)) : '' ?></p>
    <p class="transaction">
      <span class="date"><?php echo format_date($now = time(),'dd/MM/yyyy HH:mm') ?></span>
      /
      <span class="num">#<?php echo $ticket->Transaction->id ?>-<?php echo $sf_user->getId() ?></span>
    </p>
    <p class="ticket-bc"><?php
    switch ( sfConfig::get('app_tickets_id') ) {
    case 'qrcode':
      echo '<img src="'.url_for('ticket/barcode?id='.$ticket->id).'" />';
      break;
    default:
      echo image_tag('/liBarcodePlugin/php-barcode/barcode.php?scale=1&code='.$ticket->getIdBarcoded());
      break;
    }
    ?></p>
    <p class="spectator"><?php if ( sfConfig::get('app_tickets_spectator_display_all', false) ): ?>
             <span class="organism"><?php echo $ticket->Transaction->Professional->Organism ?></span>
             <span class="contact"><?php echo $ticket->Transaction->Contact ?></span>
         <?php else: ?><?php echo $ticket->Transaction->professional_id > 0 ? $ticket->Transaction->Professional->Organism : $ticket->Transaction->Contact ?><?php endif ?></p>
    <p class="mentions">
      <span class="optional"><?php $mentions = sfConfig::get('app_tickets_mentions'); echo nl2br($mentions['optional']) ?></span>
      <?php if ( $ticket->cancelling ): ?>
        <span class="cancelled-id">#<?php echo $ticket->cancelling ?></span>
      <?php endif ?>
      <span class="ticket-id">#<?php echo $ticket->id ?></span>
      <span class="keep-it"><?php echo __('Keep it') ?></span>
      <span class="seating"><span><?php echo $ticket->numerotation ? '' : ($ticket->Manifestation->voucherized ? __('Voucher exchange', null, 'li_tickets_email') : __('Free seating')) ?></span></span>
    </p>
    <p class="workspace <?php echo $ticket->Manifestation->Gauges->count() > 1 ? 'has_many' : 'one' ?>">
      <?php echo $ticket->Gauge->Workspace->getNameForTicket() ?>
    </p>
    <?php if ( $nb > 1 ): ?>
    <p class="nb"><?php echo __('%%nb%% places',array('%%nb%%' => $nb)) ?></p>
    <?php endif ?>
    <p class="comment"><?php echo $ticket->comment ?></p>
  </div>
  <div class="right">
    <p class="manifid">
      #<?php echo $ticket->Manifestation->id ?><span class="tariftop"><?php echo $ticket->price_name ?></span>
    </p>
    <p class="info <?php echo $ticket->Transaction->type ?> <?php echo $duplicate ? 'duplicate' : '' ?>"><span class="subtype"><?php echo __($ticket->Transaction->type) ?></span><span class="subtype"><?php echo $duplicate ? __('Duplicata') : '' ?></span></p>
    <p class="metaevt"><?php echo $ticket->Manifestation->Event->MetaEvent ?></p>
    <p class="datetime"><?php echo format_date($ticket->Manifestation->happens_at,'dd/MM/yyyy HH:mm') ?></p>
    <p class="placeprice">
      <span class="place"><?php echo strlen($buf = $ticket->Manifestation->Location) > ($max = $maxsize['place'] ? $maxsize['place'] : 15) ? mb_substr($buf,0,$max-3).'...' : $buf ?></span>
      /
      <span class="price"><?php echo format_currency($ticket->value,'€') ?></span>
    </p>
    <p class="price_name"><span class="name"><?php echo $ticket->price_name ?></span> <span class="price"><?php echo format_currency($ticket->value,'€') ?></span></p>
    <p class="price_vat"><span class="description"><?php echo $ticket->Manifestation->Vat->value*100 ?>&nbsp;%</span><span class="value"><?php echo format_currency($ticket->value*$ticket->Manifestation->Vat->value,'€') ?></span></p>
    <p class="spectator"><?php echo $ticket->Transaction->professional_id > 0 ? $ticket->Transaction->Professional->Organism : $ticket->Transaction->Contact ?></p>
    <p class="event"><?php echo strlen($buf = (string)$ticket->getRaw('Manifestation')->Event) > $maxsize['event_name_right'] ? mb_substr($buf,0,$maxsize['event_name_right']-3).'...' : $buf ?></p>
    <p class="cie"><?php echo strlen($buf = implode(', ',$creators)) > 20 ? mb_substr($buf,0,17).'...' : $buf; ?></p>
    <p class="org">
      <span class="orgas"><?php echo isset($orgas[0]) ? $orgas[0] : '' ?></span>
    </p>
    <p class="seat"><?php echo $ticket->numerotation ? __('Seat n°%%s%%',array('%%s%%' => $ticket->numerotation)) : '' ?></p>
    <p class="transaction">
      <span class="date"><?php echo format_date($now,'dd/MM/yyyy HH:mm') ?></span>
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
    <?php if ( $nb > 1 ): ?>
    <p class="nb"><?php echo __('%%nb%% places',array('%%nb%%' => $nb)) ?></p>
    <?php endif ?>
  </div>
</div>
