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
<div id="manifestations">
<h2><?php echo __('List events on which you make command') ?> :</h2>
<ul>
<?php foreach ( $manifestations  as $manif ): ?>
  <li>
    <span class="manif"><?php echo $manif ?></span>
    <span class="transaction_id"><?php $arr = array(); foreach ( $manif->Tickets AS $tck ) $arr[$tck->transaction_id] = link_to($tck->transaction_id, 'transaction/show?id='.$tck->transaction_id); echo '#'.implode(', #',$arr); ?></span>
  </li>
<?php endforeach ?>
</ul>
</div>
