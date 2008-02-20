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
	require("conf.inc.php");
	
	if ( ($persid = intval($_GET["persid"])) <= 0 )
		$nav->redirect($config["website"]["base"]."sco");
	$fctorgid = intval($_GET["fctorgid"]);
	
	$query	= " SELECT pers.*, manif.id AS manifid, manif.date, site.nom AS sitenom, site.id AS siteid,
			   evt.id AS evtid, evt.nom AS evtnom, ticket.nb, tarif.key AS tarif, ticket.reduc,
			   entry.valid, entry.secondary, tabmanif.id AS tabmanifid, transposed,
			   site.ville AS siteville, confirmed, conftext,
			   tableau.id AS tableauid, tabpers.id AS tabpersid
		    FROM tableau_personne AS tabpers, personne_properso AS pers, entry, ticket, tarif,
		    	 manifestation AS manif, tableau_manif AS tabmanif, site, evenement AS evt, tableau
		    WHERE tabpers.personneid = ".$persid."
		      AND tabpers.fctorgid ".($fctorgid ? "= ".$fctorgid : "IS NULL")."
		      AND pers.fctorgid ".($fctorgid ? "= ".$fctorgid : "IS NULL")."
		      AND tabpers.personneid = pers.id
		      AND entry.tabpersid = tabpers.id
		      AND entry.id = ticket.entryid
		      AND entry.tabmanifid = tabmanif.id
		      AND tabmanif.manifid = manif.id
		      AND manif.siteid = site.id
		      AND manif.evtid = evt.id
		      AND tarif.id = ticket.tarifid
		      AND tableau.id = tabpers.tableauid
		    ORDER BY evtnom, date, sitenom, confirmed, conftext";
	$request = new bdRequest($bd,$query);
	
	// si pas de résultat, go home
	if ( $request->countRecords() <= 0 )
		$nav->redirect($config["website"]["base"]."sco");
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php includePage("actions"); ?>
<div class="body">
<h2>Fiche individuelle</h2>
<p class="personne"><?php
	echo '<a href="ann/fiche.php?id='.$persid.'">'.htmlsecure($request->getRecord("nom")." ".$request->getRecord("prenom")).'</a>';
	if ( $fctorgid > 0 )
	{
		echo ' (';
		echo '<a href="org/fiche.php?id='.intval($request->getRecord("orgid")).'">'.htmlsecure($request->getRecord("orgnom")).'</a>';
		if ( $request->getRecord("fcttype") ) echo " - ".htmlsecure($request->getRecord("fctdesc") ? $request->getRecord("fctdesc") : $request->getRecord("fcttype"));
		echo ')';
	}
?></p>
<h3>Les spectacles</h3>
<ul class="cmd"><?php
	$lastevt = 0;
	$lastmanif = 0;
	while ( $rec = $request->getRecordNext() )
	{
		if ( $lastevt != intval($rec["evtid"]) )
		{
			// tous passages sauf le premier
			if ( $lastevt != 0 )
				echo '</li></ul></li>';
			
			echo '<li class="evt">';
			echo '<a href="evt/infos/fiche.php?id='.intval($rec["evtid"]).'">'.$rec["evtnom"].'</a>';
			echo '<ul>';
			
			$lastevt = intval($rec["evtid"]);
			$lastmanif = 0;
		}
		
		if ( $lastmanif != intval($rec["tabmanifid"]) || $lastrec["tabpersid"] != $rec["tabpersid"] )
		{
			// tous passages sauf le premier
			if ( $lastmanif != 0 )
				echo '</li>';
			
			echo '<li class="manif '.(($conf = $rec["confirmed"]) == 't' ? 'conf' : '').'">';
			if ( $rec["conftext"] ) echo '<span class="desc">'.htmlsecure($conftext = $rec["conftext"]).'</span>';
			echo '<a href="evt/infos/manif.php?id='.intval($rec["manifid"]).'&evtid='.intval($rec["evtid"]).'">';
			$time = strtotime($rec["date"]);
			echo "le ".date($config["format"]["date"],$time);
			echo " à ".date($config["format"]["maniftime"],$time);
			echo '</a>';
			echo '<span class="salle">salle: <a href="evt/infos/salle.php?id='.intval($rec["siteid"]).'">'.htmlsecure($rec["sitenom"]).'</a> à '.htmlsecure($rec["siteville"]).'</span>';
			echo '<span class="entry">(<a href="sco/fiche.php?id='.intval($rec["tableauid"]).'">#'.intval($rec["tableauid"]).'</a>)</span>';
			
			// pour faire la transposition en fin de ligne.
			$lastrec = $rec;
			
			$lastmanif = intval($rec["tabmanifid"]);
		}
		
		echo ' <span class="ticket'.($rec["valid"] == 't' ? " highlight" : "").($rec["secondary"] == "t" ? " secondchoice" : "").'">';
		echo intval($rec["nb"]).htmlsecure($rec["tarif"]).( intval($rec["reduc"]) < 10 ? "0".intval($rec["reduc"]) : intval($rec["reduc"]) );
		echo '</span>';
	}
	
	// dernier passage
	if ( $lastevt != 0 )
		echo '</li></ul></li>';
?></ul>
<h3>Les entrées</h3>
<ul class="entries">
	<?php
		$query	= " SELECT tableau.id AS tableauid, tabpers.transposed, tabpers.id AS tabpersid
			    FROM tableau_personne AS tabpers, entry, tableau
			    WHERE personneid = ".$persid."
			      AND fctorgid ".($fctorgid ? " = ".$fctorgid : " IS NULL")."
			      AND entry.tabpersid = tabpers.id
			      AND valid
			      AND tableau.id = tableauid
			    ORDER BY tableau.id";
		$request = new bdRequest($bd,$query);
		while ( $rec = $request->getRecordNext() )
		{
			echo '<li>';
			echo '<a title="consulter" class="next" href="sco/fiche.php?id='.intval($rec["tableauid"]).'">#'.intval($rec["tableauid"]).'</a>';
			if ( is_null($rec["transposed"]) )
				echo '<a title="Transposer en billetterie (ctrl+clic)" class="operation new" href="sco/fiche.php?id='.intval($rec["tableauid"]).'&line='.intval($rec["tabpersid"]).'">&gt;&gt;</a>';
			else	echo '<a title="Suivre en billetterie (ctrl+clic)" class="operation" href="evt/bill/billing.php?t='.intval($rec["transposed"]).'&s=3">&gt;&gt;</a>';
			echo '</li>';
		}
		$request->free();
	?>
	
</ul>
</div>
<?php
	$request->free();
	$bd->free();
	
	includeLib("footer");
?>
