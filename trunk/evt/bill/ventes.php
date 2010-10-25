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
	require("conf.inc.php");
	
	includeClass("bdRequest");
	includeLib("ttt");
	includeJS("ttt");
	
	$action = $actions["edit"];
	$class .= " livre";
	$css[] = "evt/styles/jauge.css";
	$css[] = "evt/styles/colors.css.php";
	$subtitle = "Livre des ventes";
	
	$date = array();
	$date["start"] = trim($_GET["b"]["value"]) && $_GET["b"]["value"] != $_GET["b"]["default"]
		? trim($_GET["b"]["value"])
		: date("Y-m-d",strtotime("1 month ago"));
	$date["stop"]  = trim($_GET["e"]["value"]) && $_GET["e"]["value"] != $_GET["e"]["default"]
		? trim($_GET["e"]["value"])
		: date("Y-m-d",strtotime("+1 day"));
	
	// on ne check pas canceled = false car on les considère vendus même si un duplicata est en cours
	// par contre, on ne prend en compte que le premier billet d'une série de duplicatas
	$query  = " CREATE TEMP TABLE billets AS
		    SELECT preresa.manifid, manif.jauge, preresa.annul, preresa.reduc, tarif.key AS tarif,
		           getprice(preresa.manifid,tarif.id) * (1-reduc/100) AS prix,
		           txtva, manif.id AS evtid, manif.nom, manif.date, count(*) AS nb,
		           siteid, sitenom, ville, manif.colorname
		    FROM reservation_cur AS resa, tarif, info_resa AS manif, reservation_pre AS preresa, transaction
		    WHERE resa.date <  '".pg_escape_string($date["stop"])."'
		      AND resa.date >= '".pg_escape_string($date["start"])."'
		      AND resa.resa_preid = preresa.id
		      AND transaction.id = preresa.transaction AND transaction.spaceid ".($user->evtspace ? '= '.$user->evtspace : 'IS NULL')."
		      AND tarif.id = preresa.tarifid
		      AND manif.manifid = preresa.manifid
		      AND resa.date = (SELECT MIN(date) FROM reservation_cur WHERE resa_preid = resa.resa_preid)
		      AND (    NOT resa.canceled OR resa.canceled AND
		                                    (SELECT count(*) FROM reservation_cur WHERE resa_preid = resa.resa_preid AND NOT canceled) > 0 )
		    GROUP BY preresa.manifid, preresa.annul, preresa.reduc, tarif, prix, tarif.id,
		             evtid, manif.nom, manif.date, txtva, siteid, sitenom, ville, jauge, colorname
		    ORDER BY manif.nom, manif.date, jauge DESC, annul, tarif;
		    SELECT * FROM billets";
	$billets = new bdRequest($bd,$query);
	
	if ( !isset($_GET["csv"]) )
	{
?>
<?php
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php") ?>
<div class="body">
<h2><?php echo $subtitle ?></h2>
<form action="<?php echo htmlsecure($_SERVER["PHP_SELF"]) ?>" method="get">
	<p>
		<span class="debut">À partir du <?php printField("b",$date["start"],$default["date"],22,17) ?></span>,
		<span class="debut">jusqu'au <?php printField("e",$date["stop"],$default["date"],22,17) ?></span>
		<span class="submit"><input type="submit" name="submit" value="Ok" /></span>
	</p>
	<p class="livre">
		Voir le <a href="evt/bill/caisse.php?<?php echo htmlsecure("b[value]=".$date["start"]."&e[value]=".$date["stop"]) ?>">
		livre de caisse</a> correspondant aux dates en cours...
	</p>
        </form>
	<p class="csvext">
		<span>Extraction <a href="<?php echo htmlsecure($_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]) ?>&csv">standard</a>...</span>
		<span>Extraction <a href="<?php echo htmlsecure($_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]) ?>&csv&msoffice">compatible Microsoft</a>...</span>
	</p>
<?php
	$last = array();
	$last["evt"] = intval($billets->getRecord("evtid"));
	$total = array();
	$total["totaux"] = array();
	$total["tottva"] = array();
	
	// chq evenement
	while ( $rec = $billets->getRecord() )
	{
		$last["manif"] = intval($rec["manifid"]);
		$total["tva"] = array();
		$total["evt"]["gains"]  = 0;
		$total["evt"]["pertes"] = 0;
?>
	<div class="evt">
		<p class="nom"><a href="evt/infos/fiche.php?id=<?php echo intval($rec["evtid"]) ?>&view"><?php echo htmlsecure($rec["nom"]) ?></a></p>
		<?php
			// chq manifestation
			while ( $rec = $billets->getRecord() )
			{
				$total["manif"]["gains"]  = 0;
				$total["manif"]["pertes"] = 0;
		?>
		<ul class="manif">
			<li class="date <?php echo htmlsecure($rec["colorname"]) ?>">Le
				<a class="date" href="evt/infos/manif.php?id=<?php echo intval($rec["manifid"]) ?>&evtid=<?php echo intval($rec["evtid"]) ?>&view">
				<?php echo date($config["format"]["date"]." à ".$config["format"]["maniftime"],strtotime($rec["date"])) ?></a>
				à <?php echo '<a href="evt/infos/salle.php?id='.intval($rec["siteid"]).'">'.htmlsecure($rec["sitenom"])."</a>, ".htmlsecure($rec["ville"]) ?>
			(<?php echo round($tva = floatval($rec["txtva"]),2) ?>% de TVA)<ul class="billets">
			<?php
				// chq tarif
				while ( $rec = $billets->getRecordNext() )
				{
					$prixunitairettc = floatval($rec["prix"]);
					$prixtotalttc = $prixunitairettc*intval($rec["nb"]);
					$annul = $rec["annul"] == 't' ? '-' : '';
					
					echo '<li class="tarif '.($rec["annul"] == 't' ? 'annul' : '').'">';
					echo '<span class="nb">'.intval($rec["nb"])."</span> ";
					echo '<span class="tarif">'.htmlsecure($rec["tarif"]).($rec["annul"] == 't' ? ' annulé(s)' : '').'</span>';
					echo ' à ';
					echo '<span class="reduc">'.(intval($rec["reduc"])).'% de réduc.</span> ';
					echo '<span class="totalht">Total HT: ';
						echo '<span class="eur">'.$annul.round($prixtotalht = $prixtotalttc/(1+$tva/100),2).'€</span>';
						if ( $annul )
							$total["manif"]["pertes"] += $prixtotalht;
						else	$total["manif"]["gains"]  += $prixtotalht;
					echo '</span> ';
					echo '<span class="more">';
						echo '(TVA: <span class="eur">'.round($prixtotalttc - $prixtotalht,2).'€</span>, ';
						echo 'PU TTC: <span class="eur">'.$annul.round($prixunitairettc,2).'€</span>)';
					echo '</span> ';
					echo '</li>';
					
					$rec = $billets->getRecord();
					if ( intval($rec["manifid"]) != $last["manif"] )
					{
						$last["manif"] = intval($rec["manifid"]);
						break;
					}
				} // tarifs
			?>
				<li class="total">Totaux par manifestations<ul>
					<li class="gains">
						Recettes: <span class="eur"><?php echo round($total["manif"]["gains"],2); ?>€ HT</span>
						(TVA: <span class="eur"><?php echo round($buf = $total["manif"]["gains"]*(1+$tva/100)-$total["manif"]["gains"],2) ?>€</span>)
						<?php
							$total["tva"]["".$tva]["gains"] += $buf;
							$total["evt"]["gains"] += $total["manif"]["gains"];
						?>
					</li>
					<li class="pertes">
						Dépenses: <span class="eur"><?php echo round($total["manif"]["pertes"],2); ?>€ HT</span>
						(TVA: <span class="eur"><?php echo round($buf = $total["manif"]["pertes"]*(1+$tva/100)-$total["manif"]["pertes"],2) ?>€</span>)
						<?php
							$total["tva"]["".$tva]["pertes"] += $buf;
							$total["evt"]["pertes"] += $total["manif"]["pertes"];
						?>
					</li>
				</ul></li>
			</ul></li>
		<?php
				if ( intval($rec["evtid"]) != $last["evt"] )
				{
					$last["evt"] = intval($rec["evtid"]);
					break;
				}
				echo '</ul></li>';
			} // manifestations
		?>
		<li>Totaux par évènement<ul><?php
			echo '<li>';
			$total["totaux"]["gains"] += $total["evt"]["gains"];
			echo 'Recettes: <span class="eur">'.round($total["evt"]["gains"],2).'€ HT</span> ';
			echo '(';
			$tmp = array();
			foreach ( $total["tva"] as $key => $value )
			{
				$total["tottva"][$key]["gains"] += $value["gains"];
				$tmp[] = 'TVA à '.round($key,2).'%: <span class="eur">'.round($value["gains"],2).'€</span>';
			}
			echo implode(", ",$tmp);
			echo ')';
			echo '</li>';
			echo '<li>';
			$total["totaux"]["pertes"] += $total["evt"]["pertes"];
			echo 'Dépenses: <span class="eur">'.round($total["evt"]["pertes"],2).'€ HT</span> ';
			echo '(';
			$tmp = array();
			foreach ( $total["tva"] as $key => $value )
			{
				$total["tottva"][$key]["pertes"] += $value["pertes"];
				$tmp[] = 'TVA à '.round($key,2).'%: <span class="eur">'.round($value["pertes"],2).'€</span>';
			}
			echo implode(", ",$tmp);
			echo ')';
			echo '</li>';
		?></ul></li>
		</ul>
	</div>
<?php
	} // evenements
?>
	<div class="evt totaux">
		<p class="nom">Totaux des totaux</p>
		<ul>
			<li>
				Recettes:
				<span class="eur"><?php echo round($total["totaux"]["gains"],2) ?>€</span>
				(<?php
					$tmp = array();
					$buf = 0;
					foreach ( $total["tottva"] as $key => $value )
					{
						$buf += $value["gains"];
						$r  = "TVA à ".round($key,2).'%: ';
						$r .= '<span class="eur">'.round($value["gains"],2).'€</span>';
						$tmp[] = $r;
					}
					$tmp[] = 'soit un total de <span class="eur">'.round($total["totaux"]["gains"]+$buf,2).'€</span> TTC';
					echo implode(", ",$tmp);
				?>)
			</li>
			<li>
				Dépenses:
				<span class="eur"><?php echo round($total["totaux"]["pertes"],2) ?>€</span> HT
				(<?php
					$tmp = array();
					$buf = 0;
					foreach ( $total["tottva"] as $key => $value )
					{
						$buf += $value["pertes"];
						$r  = "TVA à ".round($key,2).'%: ';
						$r .= '<span class="eur">'.round($value["pertes"],2).'€</span>';
						$tmp[] = $r;
					}
					$tmp[] = 'soit un total de <span class="eur">'.round($total["totaux"]["pertes"]+$buf,2).'€</span> TTC';
					echo implode(", ",$tmp);
				?>)
			</li>
			<li>
			  Synthèse:
				<span class="eur"><?php echo round($total["totaux"]["gains"]-$total["totaux"]["pertes"],2) ?>€</span> HT
				(<?php
					$tmp = array();
					$buf = 0;
					foreach ( $total["tottva"] as $key => $value )
					{
						$buf += $value['gains']-$value["pertes"];
						$r  = "TVA à ".round($key,2).'%: ';
						$r .= '<span class="eur">'.round($value['gains']-$value["pertes"],2).'€</span>';
						$tmp[] = $r;
					}
					$tmp[] = 'soit un total de <span class="eur">'.round($total['totaux']['gains']-$total["totaux"]["pertes"]+$buf,2).'€</span> TTC';
					echo implode(", ",$tmp);
				?>)
			</li>
		</ul>
	</div>
</div>

<?php
	includeLib("footer");
?>
<?php
	}
	else // if ( isset($_GET["csv"]) )
	{
		includeClass("csvExport");
		
		$last = 0; // derniere manif
		$i = 0;
		$arr = array();
		
		$prix	= array();
		$tva	= array();
		
		$arr[$i] = array();
		$arr[$i][] = "Du :";
		$arr[$i][] = $date["start"];
		$arr[++$i] = array();
		$arr[$i][] = "Au :";
		$arr[$i][] = $date["stop"];
		
		$arr[++$i] = array();
		$arr[++$i] = array();
		$arr[$i][] = "Evenement";
		$arr[$i][] = "Date/Heure";
		$arr[$i][] = "Lieu";
		$arr[$i][] = "Jauge";
		
		$query	= " SELECT DISTINCT tarif, reduc, annul, prix * (1-reduc/100) AS prix
			    FROM billets
			    ORDER BY annul, tarif";
		$request = new bdRequest($bd,$query);
		while ( $rec = $request->getRecordNext() )
		{
			$arr[$i][] = $rec["tarif"].($rec["annul"] == 't' ? ' (annul)' : '').' à '.str_replace(".",$config["regional"]["decimaldelimiter"],round($rec["prix"]*(1-$rec["reduc"]/100),2)).'€';
			$prix[$rec["tarif"].(intval($rec["reduc"]) < 10 ? "0" : "").intval($rec["reduc"]).$rec["prix"].$rec["annul"]] = count($arr[$i]) - 1;
		}	
		$request->free();
		
		$arr[$i][] = "Total HT";
		
		$query	= " SELECT DISTINCT txtva
			    FROM billets
			    ORDER BY txtva";
		$request = new bdRequest($bd,$query);
		while ( $rec = $request->getRecordNext() )
		{
			$arr[$i][] = "TVA a ".str_replace(".",$config["regional"]["decimaldelimiter"],round($rec["txtva"],2))."%";
			$tva[$rec["txtva"]] = count($arr[$i]) - 1;
		}
		$request->free();
		
		function endLine($last)
		{
			global $total, $tva, $arr, $i, $prix, $config;
			
			if ( $last != 0 )
			{
				$total["tva"]["px"] = $total["ttc"] - $total["ht"];
				
				$arr[$i][4+count($prix)] = str_replace(".",$config["regional"]["decimaldelimiter"],round($total["ht"],2));
				$arr[$i][$tva[$total["tva"]["tx"]]] = str_replace(".",$config["regional"]["decimaldelimiter"],round($total["tva"]["px"],2));
			}
		}
		
		while ( $rec = $billets->getRecordNext() )
		{
			endLine($last);
			
			// inits
			$total = array();
			$total["ttc"] = 0;
			$total["ht"]  = 0;
			$total["tva"] = array();
			$total["tva"]["tx"] = $rec["txtva"];
			
			if ( $last != intval($rec["manifid"]) )
			{
				$arr[++$i] = array();
				
				// debut ligne
				$arr[$i][] = $rec["nom"].' (#'.$rec["manifid"].')';	// 0. evt
				$arr[$i][] = $rec["date"];				// 1. date
				$arr[$i][] = $rec["sitenom"]." - ".$rec["ville"];	// 2. lieu
				$arr[$i][] = $rec["jauge"];				// 3. jauge
				
				// remplissage par défaut du tableau des prix
				for ( $j = 0 ; $j < count($prix) ; $j++ )
					$arr[$i][] = 0;
			
				$arr[$i][] = 0;						// 3+count($prix)-1. total ht
				
				for ( $j = 0 ; $j < count($tva) ; $j++ )		// 3+count($prix)+$j. total TVA
					$arr[$i][] = 0;
				
				$last = intval($rec["manifid"]);
			}
			
			// le prix total ht des billets
			$arr[$i][$prix[$rec["tarif"].(intval($rec["reduc"]) < 10 ? "0" : "").intval($rec["reduc"]).$rec["prix"].$rec["annul"]]]
				= intval($rec["nb"]);
			$tmp = intval($rec["nb"]) * (1-intval($rec["reduc"])/100) * floatval($rec["prix"]);
			$fact = $rec["annul"] == 'f' ? 1 : -1;
			$total["ttc"] += $tmp * $fact;
			$total["ht"]  += $tmp * $fact / (1+floatval($rec["txtva"])/100);
		}
		
		endLine($last);
		
		$csv = new csvExport($arr,isset($_GET["msoffice"]));
		$csv->printHeaders("livre-ventes");
		echo $csv->createCSV();
	}
?>
<?php
	$billets->free();
	$bd->free();
?>

