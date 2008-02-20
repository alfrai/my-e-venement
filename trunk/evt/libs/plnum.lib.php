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
*    Copyright (c) 2006-2007 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	// $manif correspond à un record représentant la manifestation courante
	// $resa à un tableau associatif représentatif des réservations courantes
	function plnum($manif,$resa,$disable)
	{
		global $bd,$data;
		$action = $actions["edit"];
		$defaultValue = '-pl. libre-';
		
		if ( $manif["plnum"] == 't' )
		{
			// - numéro de place bien libre
			// - numéro de place existant dans la salle
			
			$query = " SELECT plnum, resa.id
				   FROM reservation_pre AS resa, tarif
				   WHERE tarifid = tarif.id
				     AND tarif.key = '".pg_escape_string($resa["tarif"])."'
				     AND reduc = ".intval($resa["reduc"])."
				     AND manifid = ".intval($manif["manifid"])."
				     AND transaction = '".pg_escape_string($data['numtransac'])."'
				   ORDER BY plnum";
			$request = new bdRequest($bd,$query);
			
			for ( $i = abs($resa["nb"]) ; $i > 0 ; $i-- )
			{
				if ( !($rec = $request->getRecordNext()) ) $rec = array();
				echo '<input	type="text"
						value="'.($rec["plnum"] ? intval($rec["plnum"]) : (is_array($resa["other"]) && ($buf = array_shift($resa["other"])) ? intval($buf) : '-pl. libre-')).'"
						name="plnum['.$i.']"
						class="'.($rec["plnum"] || $buf ? '' : exemple).'"
						'.($disable ? 'disabled="disabled"' : "").'
						onfocus="'."javascript: ttt_onfocus(this,'".$defaultValue."'); this.className=''".'"
						onblur="'."javascript: ttt_onblur(this,'".$defaultValue."'); plnum_verif(this,".intval($manif["manifid"]).",'".$defaultValue."',".intval($rec["id"]).");".'" />';
			}
			
			$request->free();
		}
	}
?>
