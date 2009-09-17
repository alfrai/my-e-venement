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
	// si $user est le user courant et si la jauge dépasse, alors on affiche une alerte
	function printJauge($nbtotal,$nbpreresas,$nbresas,$sizemax,$nbcommandes = NULL,$sizecmd = NULL,$user = NULL,$contingents = array())
	{
		// les tailles
		$outsized = $sizemax*110/100;
		if ( is_null($sizecmd) ) $sizecmd = $outsized;
		$px = $sizemax;
		$reste = $nbtotal - $nbpreresas - $nbresas;
		$nbtotaldiv = intval($nbtotal) > 0 ? intval($nbtotal) : 1;
		
		$booked = intval($px*$nbresas/$nbtotaldiv);
		if ( $booked	< 1 && $nbresas		> 0 ) $booked = 1;
		
		$prebooked = intval($px*$nbpreresas/$nbtotaldiv);
		if ( $prebooked	< 1 && $nbpreresas	> 0 ) $prebooked = 1;
		
		
		if ( $prebooked + $booked	> $outsized )
		{
			$ratio = ($prebooked + $booked) / $outsized;
			$prebooked = intval($prebooked / $ratio);
			$booked = intval($booked / $ratio);	
		}
		
		$free = intval($px*$reste/$nbtotaldiv);
		if ( $free	< 1 && $reste		> 0 ) $free = 1;
		
		$cmd = intval($px*$nbcommandes/$nbtotaldiv);
		if ( $cmd	< 1 && $nbcommandes	> 0 ) $cmd = 1;
		if ( $cmd < 0 ) $cmd = 0;
		if ( $cmd > $sizecmd ) $cmd = $sizecmd;
		
		if ( !is_null($nbcommandes) ) echo '<span style="width: '.$cmd.'px" class="command" title="'.$nbcommandes.' pl. demandées"></span>';
		echo '<span style="padding-left: '.$free.'px;" class="free" title="'.$reste.' pl. libres"></span>';
		echo '<span style="padding-right: '.$prebooked.'px;" class="prebooked" title="'.$nbpreresas.' pl. pré-rés.">';
		if ( count($contingents) > 0 )
		{
		  echo '<span class="contingents">';
		  foreach ( $contingents as $transaction => $contingent )
		    echo '<span title="'.
		      htmlsecure(
            ($contingent['orgnom'] ? $contingent['orgnom'] : $contingent['nom'].' '.$contingent['prenom']).': '.
            $contingent['nb'].' pl., '.
            '#'.$transaction
		      ).
		    '" style="padding-left: '.(($prebooked*$contingent['nb']/$nbpreresas)-1).'px"></span>';
		  echo '</span>';
		}
		echo '</span>';
		echo '<span style="padding-left: '.$booked.'px;" class="booked" title="'.$nbresas.' pl. réservées"></span>';
		
		echo '<span class="jauge '.($nbtotal < $nbpreresas + $nbresas ? 'err' : ($reste < $nbcommandes ? 'warn' : '')).'"
		            title="'.($reste < $nbpreresas + $nbresas ? ($nbpresas+$nbresas).'pl. de trop' : '').'">'.$nbtotal.' pl.</span>';
		
		if ( is_object($user) && $reste < 0 )
		{
			$user->addAlert("ATTENTION: la jauge est dépassée.");
		}
	}
?>
