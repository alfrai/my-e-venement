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
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	global $bd,$id;
	
	includeClass("bdRequest");
?>
<div class="reminder">
	<p class="th"><span>Clé</span><span>Tarif</span></p>
	<?php
		$query = " SELECT DISTINCT key, description, contingeant, desact
			   FROM tarif
			   WHERE date IN ( SELECT max(date) FROM tarif AS tmp WHERE tmp.key = tarif.key )
			     AND NOT desact
			   ORDER BY key";
		$request = new bdRequest($bd,$query);
		
		while ( $rec = $request->getRecordNext() )
		{
			echo '<p class="'.($rec["contingeant"] == 't' ? ' cont' : "").($rec["desact"] == 't' ? ' desact' : '').'">';
			echo '<span>'.htmlsecure($rec["key"]).'</span>';
			echo '<span>'.htmlsecure($rec["description"]).'</span>';
			echo '</p>';
		}
		
		$request->free();
	?>
</div>
