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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
if (!( isset($transaction) && $transaction->id == $ticket->transaction_id ))
  $transaction = $ticket->Transaction;

if ( is_null($ticket->duplicating) && is_null($ticket->cancelling) && !$ticket->hasBeenCancelled() )
if ( $ticket->printed_at || $ticket->integrated_at || $transaction->Order->count() > 0 )
{
  if ( !isset($events[$ticket->Manifestation->Event->meta_event_id]) )
  {
    $events[$ticket->Manifestation->Event->meta_event_id] = array('name' => (string)$ticket->Manifestation->Event->MetaEvent);
    $sort[$ticket->Manifestation->Event->meta_event_id] = array('name' => 0);
  }
  if ( !isset($events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]) )
    $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id] = array(
      'happens_at' => 0,
      'event' => $ticket->Manifestation->Event,
      'title' => (string)$ticket->Manifestation->Event->MetaEvent,
      'ids' => array(),
      'value' => 0,
      'transaction_ids' => array()
    );
  if ( $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['happens_at'] < $ticket->Manifestation->happens_at )
    $sort[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id] = $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['happens_at'] = $ticket->Manifestation->happens_at;
  $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['ids'][$ticket->id] = $ticket->id;
  $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['value'] += $ticket->value;
  $events[$ticket->Manifestation->Event->meta_event_id][$ticket->Manifestation->Event->id]['transaction_links'][(($p = $ticket->printed_at || $ticket->integrated_at || $ticket->cancelling) ? 'p' : 'r').$ticket->transaction_id]
    = '#'.cross_app_link_to($ticket->transaction_id, 'tck', 'ticket/sell?id='.$ticket->transaction_id, false, null, false, $p ? 'title="'.__('All printed').'"' : 'class="not-printed" title="'.__('Ordered').'"');
  $total['value'] += $ticket->value;
  $total['ids'][$ticket->id] = $ticket->id;
}
