<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	global $config;
	$mod = ( $user->evtlevel >= $config["evt"]["right"]["mod"] );
?>
<p class="actions">
<?php
	echo '<a href="'.($href = "evt/bill/index.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").'">Index</a>';
	
	if ( $mod )
	{
		echo '<a href="'.($href = "evt/bill/billing.php").'" class="';
		echo ($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "");
		echo ' index">Billets</a>';
	}
	if ( $mod && $config['ticket']['new-bill'] )
	{
		echo '<a href="'.($href = "evt/bill/new-bill.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").'">Billets (new)</a>';
	}
	if ( $mod ) echo '<a href="'.($href = "evt/bill/annul.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").'  annul">Annul.</a>';
	if ( $config["ticket"]["dematerialized"] && $mod )
	echo '<a href="'.($href = "evt/bill/infotick.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").'  annul">Infoticks</a>';
	
	if ( $mod ) echo '<a href="'.($href = "evt/bill/depot.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active " : "").' depot">Dépôt</a>';
	if ( $mod ) echo '<a href="'.($href = "evt/bill/vdir.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").' ventedir">Ventes</a>';
	echo '<a href="'.($href = "evt/bill/waitingdep.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").' attente">En cours</a>';
	
	echo '<a href="'.($href = "evt/bill/queries.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").' queries">Demandes</a>';
	echo '<a href="'.($href = "evt/bill/waitingbdc.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").' bdc">BdC</a>';
	echo '<a href="'.($href = "evt/bill/factures.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").' bdc">Factures</a>';
	echo '<a href="'.($href = "evt/bill/credit.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").' credit">Dettes</a>';
	echo '<span class="desc">Demande du temps de calcul</span>';
	
	echo '<a href="'.($href = "evt/bill/ventes.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").' ventes">Ventes</a>';
	echo '<a href="'.($href = "evt/bill/caisse.php").'" class="'.($config["website"]["root"].$href == $_SERVER["PHP_SELF"] ? "active" : "").' caisse">Caisse</a>';
	
	echo '<a href="evt/" class="parent">..</a>';
?>
</p>
