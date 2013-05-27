<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2012 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2012 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_helper('Date','Number') ?>
<span class="manif" style="background-color: #<?php echo $manif->Color ? $manif->Color->color : '' ?>;">
  <input type="radio" name="ticket[manifestation_id]" value="<?php echo $manif->id ?>" <?php if ( isset($first) && $first ) echo 'checked="checked"' ?>  />
  <a class="name" title="<?php echo $manif->Event ?>" href="<?php echo cross_app_url_for('event','event/show?id='.$manif->event_id) ?>">
    <?php echo $manif->Event ?>
  </a>
  <a  class="happens_at"
      href="<?php echo cross_app_url_for('event','manifestation/show?id='.$manif->id) ?>"
      title="<?php echo format_date(strtotime($manif->happens_at) + strtotime($manif->duration) - strtotime('0:00'), 'EEE d MMM yyyy HH:mm').' '.__('at').' '.$manif->Location ?>">
    <?php echo format_datetime($manif->happens_at,'EEE d MMM yyyy HH:mm') ?>
  </a>
  <?php if ( sfConfig::get('app_manifestations_show_location',false) ): ?>
  <a class="location" title="<?php echo $manif->Location ?>" href="<?php echo cross_app_url_for('event','location/show?id='.$manif->location_id) ?>">
    <?php echo $manif->Location ?>
  </a>
  <?php endif ?>
</span>
<span class="workspaces">
  <?php include_partial('ticket_manifestation_wslist',array('manif' => $manif)) ?>
</span>
<span class="prices">
<?php include_partial('ticket_manifestation_prices',array('manif' => $manif,)) ?>
<?php if ( $active ): ?>
  <?php $total = 0; $gid = $manif->Tickets[0]->gauge_id ?>
  <?php include_partial('ticket_manifestation_ws',array('ticket' => $manif->Tickets[0],'nb_gauges' => $manif->Gauges->count())) ?>
  <?php foreach ( $manif->Tickets as $ticket ): ?>
    <?php if ( $gid != $ticket->gauge_id ): ?>
      <?php $gid = $ticket->gauge_id ?>
      </span>
      <?php include_partial('ticket_manifestation_ws',array('ticket' => $ticket,'nb_gauges' => $manif->Gauges->count())) ?>
    <?php endif ?>
    <?php if ( $ticket->Duplicatas->count() == 0 ): ?>
    <input alt="#<?php echo $ticket->id ?>" type="hidden" name="ticket[prices][<?php echo $ticket->gauge_id ?>][<?php echo $ticket->Price ?>][]" value="<?php echo $ticket->value ?>" title="PU: <?php echo format_currency($ticket->value,'€') ?>" class="<?php echo $ticket->printed_at ? 'printed' : ($ticket->integrated_at ? 'integrated' : 'notprinted') ?>" />
    <?php $total += $ticket->value ?>
    <?php endif ?>
  <?php endforeach ?>
  </span>
<?php endif ?>
</span>
<span class="total"><?php if ( $active ) echo format_currency($total,'€') ?></span>
