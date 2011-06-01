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
	global $bd, $jauge, $noinput, $user;
	
	/**
	  * if ( $jauge == true ) => print jauges
	  * if ( $noinput == true ) => don't print form subelements
	  *
	  **/
	
	echo '<h2>'.($noinput ? 'Voir...' : 'Ajouter...').'</h2>';
	includeClass("bdRequest/array");
	
	// Les spectacles
	$query	= "SELECT evt.id AS id, manif.id AS manifid, evt.nom, site.nom AS sitenom, site.ville,
		          manif.date, evt.categorie, evt.catdesc, color.libelle AS colorname
		   FROM evenement_categorie AS evt, site, manifestation AS manif
		   LEFT JOIN color ON color.id = manif.colorid
		   LEFT JOIN space_manifestation sm ON sm.manifid = manif.id AND sm.spaceid ".($user->evtspace ? '= '.$user->evtspace : 'IS NULL')."
		   WHERE manif.date >= NOW() - '1 hour'::interval
		     AND evt.id = manif.evtid
		     AND site.id = manif.siteid
		     AND CASE WHEN ".($user->evtspace ? 'true' : 'false')." THEN sm.jauge ELSE manif.jauge END > 0
		     ".(count($data["manif"]) > 0 ? "AND manif.id NOT IN (".implode(',',$data["manif"]).")" : "")."
		   ORDER BY catdesc, nom, date";
	$evt = new arrayBdRequest($bd,$query);
	
	$lastcat = -1;
	while ( $rec = $evt->getRecordNext() )
	{
		if ( $lastcat != intval($rec["categorie"]) )
		{
			if ( $lastcat != -1 ) echo '</div>';
			$lastcat = intval($rec["categorie"]);
			echo '<div class="manifestations hide">';
			echo '<p class="cat">';
			echo '<span class="titre">'.htmlsecure($rec["catdesc"] ? $rec["catdesc"] : "Sans catégorie").'</span> ';
			echo '<span class="more">(<a onclick="';
			echo "javascript: e=this.parentNode.parentNode.parentNode; c='manifestations'; if(e.className==c)e.className=c+' hide'; else e.className=c;";
			echo '">Afficher / Cacher</a>)</span>';
			echo '</p> ';
		}
		echo '<p class="content" onmouseover="javascript: bill_jauge('.intval($rec["manifid"]).');"
					 onclick="javascript: '."ttt_spanCheckBox(this.getElementsByTagName('input').item(0))".';">';
		echo '<span class="sel">';
		
		if ( !$noinput )
		{
			echo '<input type="checkbox" name="manif[]" value="'.intval($rec["manifid"]).'" ';
			echo 'onclick="javascript: '."ttt_spanCheckBox(this)".';" ';
			echo '/>';
		}
		echo '</span> ';
		printManif($rec);
		echo '</p> ';
		if ( $jauge )
		{
			echo '<p class="content jauge" onclick="javascript: '."ttt_spanCheckBox(this.parentNode.getElementsByTagName('input').item(0))".';">';
			echo '<span id="manif_'.intval($rec["manifid"]).'">';
			//printJauge(intval($rec["jauge"]),intval($rec["preresas"]),intval($rec["resas"]),450,intval($rec["commandes"]),550);
			echo '</span></p>';
		}
	}
	$evt->free();
	if ( $lastcat != -1 ) echo '</div>';
?>
	<div class="manifestations hide">
		<p class="cat">
			<span class="titre">Manifestations passées</span>
			<span class="more">(<a onclick="javascript:
				e=this.parentNode.parentNode.parentNode; c='manifestations'; if(e.className==c)e.className=c+' hide'; else e.className=c;
				printAllOldManifs(this.parentNode.parentNode.parentNode.getElementsByTagName('div').item(0));
			">Afficher / Cacher</a>)</span>
		</p>
		<div></div>
	</div>
	<?php if ( !$noinput ) { ?>
	<p class="valid">
		<input type="submit" class="next" value="Ajouter" name="add" />
	</p>
	<?php } ?>
