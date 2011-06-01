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
	require_once("conf.inc.php");
	includeClass("bdRequest");
	
	if ( !isset($manifid) )
		$manifid = intval($_GET["id"]);
	
	if ( $_SERVER["PHP_SELF"] == $config["website"]["root"]."evt/infos/places.hide.php" || $more )
	{
		$query = " SELECT sum(nb) AS nb,tarif,reduc,printed,
			          transaction IN (SELECT transaction FROM preselled) AS preresa,
			          get_second_if_not_null(prix,prixspec) AS prix
			   FROM tickets2print_bymanif(".$manifid.") AS resa
			   LEFT JOIN transaction t ON t.id = transaction
			   WHERE canceled = false
			     ".($_GET['spaces'] != 'all' ? "AND t.spaceid ".($user->evtspace ? '= '.$user->evtspace : 'IS NULL') : '')."
			   GROUP BY tarif,reduc,printed,preresa,prix,prixspec
			   ORDER BY tarif,reduc,printed,preresa";
		$request = new bdRequest($bd,$query);
		$recette = 0;
	}
	
	// A CHANGER AVEC LE NOM DE CE FICHIER
	if( $_SERVER["PHP_SELF"] != $config["website"]["root"]."evt/infos/places.hide.php" )
	{
?>
			<div class="trans places">
				<h3 class="head">Places</h3>
				<?php if ( $more ) { ?>
				<ul><?php
					$prixtotal = 0;
					echo '<li class="tarif"><span class="name">';
					echo htmlsecure($last = $request->getRecord("tarif").' '.(($tmp = intval($request->getRecord("reduc"))) < 10 ? "0".$tmp : $tmp));
					echo '</span><ul>';
					while ( $rec = $request->getRecordNext() )
					{
						if ( $last != $rec["tarif"].' '.(($tmp = intval($rec["reduc"])) < 10 ? "0".$tmp : $tmp) )
						{
							echo '</ul>';
							echo '<span class="total">Total théorique: '.htmlsecure($prixtotal.'€ HT').'</span>';
							echo '</li>';
							$prixtotal = 0;
							echo '<li class="tarif"><span class="name">';
							echo htmlsecure($last = $rec["tarif"].' '.(($tmp = intval($rec["reduc"])) < 10 ? "0".$tmp : $tmp));
							echo '</span><ul>';
						}
						
						echo '<li class="places'.(intval($rec["nb"]) < 0 ? ' warn' : '').'">';
						echo $lastnb = intval($rec["nb"]).' ';
						echo $rec["printed"] == 'f'
							? ( $rec["preresa"] == 'f'
								? 'demande(s)'
								: 'pre-réservée(s)' )
							: ( $rec["preresa"] == 'f'
								? 'réservation(s)'
								: 'réservation(s) avec BdC' );
						echo '</li>';
						if ( $rec["printed"] != 'f' )
						{
							$recette += floatval($rec["prix"])*(1-intval($rec["reduc"])/100)*$lastnb/(1+$tva/100);
							$prixtotal += round(floatval($rec["prix"])*(1-intval($rec["reduc"])/100)*$lastnb/(1+$tva/100),2);
							//echo $prixtotal;
						}
					}
					echo '</ul></li>';
					echo '<span class="total">Total: '.htmlsecure($prixtotal.'€ HT').'</span>';
				?>
					<li>
						Ventes:
						<?php echo round($recette,2) ?>€ HT,
						<?php echo round($ttc = $recette*(1+$tva/100),2) ?>€ TTC
						(dont TVA: <?php echo round($ttc - $recette,2); ?>€)
					</li>
				</ul>
				<?php } // if ( $more ) ?>
				<p class="csvext">
					<span>Extraction <a href="evt/infos/places.hide.php?id=<?php echo $manifid ?>&spaces=<?php echo htmlsecure($_GET['spaces']) ?>">standard</a>...</span>
					<span>Extraction <a href="evt/infos/places.hide.php?id=<?php echo $manifid ?>&spaces=<?php echo htmlsecure($_GET['spaces']) ?>&msoffice">compatible Microsoft</a>...</span>
				</p>
			</div>
<?php
	}
	else
	{
		includeClass("csvExport");
		
		// les prix unitaires
		$query = " SELECT *, (SELECT count(*) > 0 FROM reservation_pre WHERE manifid = tarif.manifid) AS stop
			   FROM tarif_manif AS tarif
			   WHERE manifid = ".$manifid."
			   ORDER BY prix, key";
		
		$def = new bdRequest($bd,$query);
		$prix = array();
		while ( $rec = $def->getRecordNext() )
		{
			if ( $rec["stop"] == 't' ) $action = $actions["view"];
			$prix[$rec["key"]] = !is_null($rec["prixspec"]) ? floatval($rec["prixspec"]) : floatval($rec["prix"]);
		}
		$def->free();
		
		$arr = array();
		$i = 0;
		
		/*
		$query = " SELECT evt.*, manif.jauge, manif.date, manif.txtva, site.nom AS sitenom, site.ville AS siteville
			   FROM manifestation AS manif,evenement AS evt, site
			   WHERE manif.id = ".$manifid." AND evtid = evt.id AND siteid = site.id";
    */
    require 'query.hide.php';
		$manif = new bdRequest($bd,$query);
		if ( $rec = $manif->getRecord() )
		{
			$tva = $rec["txtva"];
			$arr[$i] = array();
			$arr[$i][] = $rec["nom"];
			$arr[$i][] = date($config["format"]["date"]." ".$config["format"]["maniftime"],strtotime($rec["date"]));
			$arr[$i][] = $rec["sitenom"]." (".$rec["siteville"].")";
			$arr[$i][] = "Jauge: ".intval($rec["jauge"]);
			$manif->free();
		}
		else
		{
			$user->addAlert("Manifestation introuvable");
			$manif->free();
			exit(1);
		}
		
		$rank = array();
		$arr[++$i] = array();
		$arr[++$i] = array();
		$arr[$i][] = "Tarif";
		$rank["tarif"] = count($arr[$i]) - 1;
		$arr[$i][] = "Reduction";
		$rank["reduc"] = count($arr[$i]) - 1;
		$arr[$i][] = "Prix unitaire TTC";
		$rank["puttc"] = count($arr[$i]) - 1;
		$arr[$i][] = "Demandes en attente";
		$rank["demandes"] = count($arr[$i]) - 1;
		$arr[$i][] = "Pre-reservations en attente";
		$rank["preresas"] = count($arr[$i]) - 1;
		$arr[$i][] = "Reservations effectuees";
		$rank["resas"] = count($arr[$i]) - 1;
		$arr[$i][] = "Total HT du tarif";
		$rank["ptht"] = count($arr[$i]) - 1;
		
		$last = array();
		$last["tarif"] = "";
		while ( $rec = $request->getRecordNext() )
		{
			// données par tarif
			if ( $last["tarif"] != $rec["tarif"].$rec["reduc"] )
			{
				$arr[++$i] = array();
				
				// init des différentes valeurs
				for ( $j = 0 ; $j < count($rank) ; $j++ )
					$arr[$i][$j] = 0;
				
				$arr[$i][$rank["tarif"]] = $rec["tarif"];
				$arr[$i][$rank["reduc"]] = $rec["reduc"]."%";
				
				// valeur exacte dans le tableau !!! changée ensuite
				$arr[$i][$rank["puttc"]] = $last["prix"] = floatval($prix[$rec["tarif"]])*(1-intval($rec["reduc"])/100);
				
				$last["tarif"] = $rec["tarif"].$rec["reduc"];
			}
			
			// demandes
			if ( $rec["printed"] == 'f' && $rec["preresa"] == 'f' )
				$arr[$i][$rank["demandes"]] = intval($rec["nb"]);
			
			// preresas
			if ( $rec["printed"] == 'f' && $rec["preresa"] == 't' )
				$arr[$i][$rank["preresas"]] = intval($rec["nb"]);
			
			// resas
			if ( $rec["printed"] == 't' )
				$arr[$i][$rank["resas"]] += intval($rec["nb"]);
		}
		
		// écriture du total + données manquantes + car. régionaux
		$recette = 0;
		for ( $j = 3 ; $j < count($arr) ; $j++ )
		{
			while ( count($arr[$j]) < 6 )
				$arr[$j][] = 0;
			$total = $arr[$j][5] * $arr[$j][2] / (1+$tva/100);
			$recette += $total;
			$arr[$j][] = str_replace(".",$config["regional"]["decimaldelimiter"],round($total,2));
			
			// correction des caractères régionaux
			$arr[$j][2] = str_replace(".",$config["regional"]["decimaldelimiter"],round($arr[$j][2],2));
		}
		
		// bilan
		$arr[++$i] = array();
		$arr[++$i] = array();
		$arr[$i][] = "Total HT des ventes";
		$arr[$i][] = "Taux de TVA";
		$arr[$i][] = "Total TTC des ventes";
		$arr[$i][] = "Total TVA";
		
		$arr[++$i] = array();
		$arr[$i][] = str_replace(".",$config["regional"]["decimaldelimiter"],round($recette,2));
		$arr[$i][] = str_replace(".",$config["regional"]["decimaldelimiter"],round($tva,2))."%";
		$arr[$i][] = str_replace(".",$config["regional"]["decimaldelimiter"],round($recette*(1+$tva/100),2));
		$arr[$i][] = str_replace(".",$config["regional"]["decimaldelimiter"],round($recette*($tva/100),2));
		
		$csv = new csvExport($arr,isset($_GET["msoffice"]));
		$csv->printHeaders("bilan-".$manifid."-places-".date("Ymd"));
		echo $csv->createCSV();
		
		$bd->free();
	}
	
	$request->free();
?>
