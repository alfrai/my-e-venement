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
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  // JSON CONTENT
  
  $manifs = array();
  
  foreach ( $manifestations as $manif )
  {
    $manifs[] = array(
      'id' => $manif->id,
      'title' => !isset($event_id) ? (string)$manif->Event : (string)$manif->Location,
      'start' => $manif->happens_at,
      'end' => date('Y-m-d H:i:s',strtotime($manif->happens_at)+$manif->duration),
      'allDay' => false,
      'hackurl' => url_for('manifestation/show?id='.$manif->id),
      'editable' => $sf_user->hasCredential('event-manif-edit'),
    );
    
    if ( $manif->color_id )
      $manifs[count($manifs)-1]['backgroundColor'] = '#'.$manif->Color->color;
  }
?>
<?php if ( $debug ): ?>
<pre><?php print_r($manifs) ?></pre>
<?php else: ?>
<?php echo json_encode($manifs) ?>
<?php endif ?>
