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
	global $bd, $config, $fctorgid;
	includeClass("bdRequest");
	
			// manifestation à ajouter
			$query	= " SELECT evt.id AS evtid, manif.id AS manifid, evt.categorie AS evtcat, evt.catdesc, evt.typedesc, evt.nom,
				           manif.date, color.libelle AS colorname, (SELECT count(*) FROM roadmap WHERE manifid = manif.id) AS nbpro,
				           site.nom AS sitenom, site.ville AS siteville, site.id AS siteid
				    FROM manifestation AS manif, evenement_categorie AS evt, colors AS color, site
				    WHERE date >= (SELECT value FROM params WHERE name = 'datemin')::date
				      AND date <= (SELECT value FROM params WHERE name = 'datemax')::date + '1 day'::interval
				      AND manif.evtid = evt.id
				      AND ( manif.colorid = color.id OR color.id IS NULL AND manif.colorid IS NULL )
				      AND jauge > 0
				      AND manif.siteid = site.id
				    ORDER BY categorie, catdesc, date";
			$request = new bdRequest($bd,$query);
			
			$manifs = $_SERVER["PHP_SELF"] != $config["website"]["root"]."pro/pro.php";
			$categorie = -1;
			while ( $rec = $request->getRecordNext() )
			{
				if ( $categorie != intval($rec["evtcat"]) )
				{
					if ( $categorie != -1 )
						echo '</p>';
					$categorie = intval($rec["evtcat"]);
					
					echo '<p class="cat '.($manifs ? hide : '').'"><span>';
					echo htmlsecure($rec["catdesc"] ? $rec["catdesc"] : "Sans catégorie");
					if ( $manifs )
					{
						echo ' <a onclick="javascript: '."this.parentNode.parentNode.className='cat';".'" class="view">(voir)</a>';
						echo ' <a onclick="javascript: '."this.parentNode.parentNode.className='cat hide';".'" class="hide">(cacher)</a>';
					}
					echo '</span></p>';
					
					echo '<p class="manifs">';
				}
				$time = strtotime($rec["date"]);
				
				echo '<span class="manif"
					    onmouseover="javascript: '."get_nbcontingeants(this.getElementsByTagName('jauge').item(0),".intval($rec["manifid"]).');">';
				
				echo '<a name="manif'.intval($rec["manifid"]).'"></a>';
				
				if ( !$manifs )
				echo '<span class="radio"><input type="checkbox" name="newmanif[]" value="'.intval($rec["manifid"]).'"/></span>';
				
				echo '<span class="date">le <a href="evt/infos/manif.php?id='.intval($rec["manifid"]).'&evtid='.intval($rec["evtid"]).'" class="'.$rec["colorname"].' manif">';
				echo $config["dates"]["dotw"][date("w",$time)].' '.date($config["format"]["date"],$time);
				echo ' à '.date($config["format"]["maniftime"],$time).'</a></span>';
				
				echo '<span class="fiche"><a href="pro/manif.php?newmanif='.intval($rec["manifid"]).'" class="manifpro" title="fiche manifestation"><span>fiche manifestation</span></a></span>';
				
				echo '<span class="quota">'.intval($rec["nbpro"]).'/<jauge>..</jauge></span>';
				
				echo '<span class="evt">';
				echo '<a href="evt/infos/fiche.php?id='.intval($rec["evtid"]).'">'.htmlsecure($rec["nom"]).'</a>';
				echo '</span>';
				
				echo '<span class="site"><a href="evt/infos/salle.php?id='.intval($rec["siteid"]).'">'.htmlsecure($rec["sitenom"]).'</a> ('.htmlsecure($rec["siteville"]).')</span>';
				echo '</span>';
			}
			
			$request->free();
?>
