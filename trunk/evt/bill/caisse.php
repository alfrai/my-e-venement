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
	$subtitle = "Livre de caisse";
	
	$date = array();
	$date["start"] = trim($_GET["b"]["value"]) && $_GET["b"]["value"] != $_GET["b"]["default"]
		? trim($_GET["b"]["value"])
		: date("Y-m-d",strtotime("1 month ago"));
	$date["stop"]  = trim($_GET["e"]["value"]) && $_GET["e"]["value"] != $_GET["e"]["default"]
		? trim($_GET["e"]["value"])
		: date("Y-m-d",strtotime("+1 day"));
	
	$query  = " CREATE TEMP TABLE paiements AS
		    SELECT montant, date, mode.id, libelle, numcompte, transaction
		    FROM modepaiement AS mode, paiement AS paie, transaction
		    WHERE paie.date < '".pg_escape_string($date["stop"])."'
		      AND paie.date >=  '".pg_escape_string($date["start"])."'
          AND transaction.id = paie.transaction ".($_GET['spaces'] != 'all' ? "AND transaction.spaceid ".($user->evtspace ? '= '.$user->evtspace : 'IS NULL') : '')."
		      AND modepaiementid = mode.id
		    ORDER BY date, montant, libelle";
	$pays	= new bdRequest($bd,$query);
	$pays->free();
	
	if ( !isset($_GET["csv"]) )
	{
		$query	= " SELECT personne.id AS personneid, personne.nom, personne.prenom, transaction.id, paiements.*
			    FROM paiements, transaction, personne_properso AS personne
			    WHERE transaction = transaction.id
			      AND (personneid = personne.id OR personne.id IS NULL AND personneid IS NULL)
			      AND (transaction.fctorgid = personne.fctorgid OR personne.fctorgid IS NULL AND transaction.fctorgid IS NULL)
			    ORDER BY libelle,montant";
		$pays = new bdRequest($bd,$query);	
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
    <?php if ( $config['evt']['spaces'] ): ?><span class="spaces all"><input type="checkbox" name="spaces" value="all" <?php echo $_GET['spaces'] == 'all' ? 'checked="checked"' : '' ?>" title="Tous les espaces" /></span><?php endif ?>
		<span class="submit"><input type="submit" name="submit" value="Ok" /></span>
	</p>
	<p class="livre">
		Voir le <a href="evt/bill/ventes.php?<?php echo htmlsecure("b[value]=".$date["start"]."&e[value]=".$date["stop"]) ?>">
		livre des ventes</a> correspondant aux dates en cours...
	</p>
</form>
	<p class="csvext">
		<span>Extraction <a href="<?php echo htmlsecure($_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]) ?>&csv">standard</a>...</span>
		<span>Extraction <a href="<?php echo htmlsecure($_SERVER["PHP_SELF"]."?".$_SERVER["QUERY_STRING"]) ?>&csv&msoffice">compatible Microsoft</a>...</span>
	</p>
	<div class="cpt"><ul>
<?php
	$total = array();
	$last = 0;
	
	function endLine($last)
	{
		global $total;
		
		if ( $last != 0 )
		{
			$total["tot"] += $total["mode"];
			
			echo '<li class="total"><span class="label">sous-total:</span> ';
			echo '<span class="montant eur">'.round($total["mode"],2).'€</span></li>';
			echo '</ul>';
			echo '</li>';
		}
	}
	
	// chq compte
	while ( $rec = $pays->getRecordNext() )
	{
		if ( $last != intval($rec["id"]) )
		{
			endLine($last);
			
			$total["mode"] = 0;
			$last = intval($rec["id"]);
			
			echo '<li class="cpt">';
			echo '<span class="nom">'.htmlsecure($rec["libelle"]).'</span>';
			echo '<span class="num">'.htmlsecure(' (#'.$rec["numcompte"].')').'</span>';
			echo '<ul>';
		}
?>
			<li>
				<span class="transac">#<a href="evt/bill/<?php echo ($_SESSION['ticket']['new-bill'] ? 'new-bill.php' : 'billing.php').'?t='.intval($rec['transaction']) ?>"><?php echo htmlsecure($rec["transaction"]) ?></a>:</span>
				<span class="eur montant"><?php
					$total["mode"] += floatval($rec["montant"]);
					echo htmlsecure(round($rec["montant"],2).'€');
				?></span>
			</li>
<?php
	}
	endLine($last);
?>
		<li class="totaux">
			<span class="nom">Total des entrées en caisse:</span>
			<span class="eur montant"><?php echo round($total["tot"],2) ?>€</span>
		</li>
	</div></ul>
</div>
<?php
	includeLib("footer");
?>
<?php
	}
	else // if ( isset($_GET["csv"]) )
	{
		$query	= " SELECT id, sum(montant) AS montant, libelle, numcompte
			    FROM paiements
			    GROUP BY id, libelle, numcompte
			    ORDER BY libelle";
		$pays = new bdRequest($bd,$query);
		
		includeClass("csvExport");
		
		$arr = array();
		
		$arr[$i] = array();
		$arr[$i][] = "Du :";
		$arr[$i][] = $date["start"];
		$arr[++$i] = array();
		$arr[$i][] = "Au :";
		$arr[$i][] = $date["stop"];
		
		$arr[++$i] = array();
		$arr[++$i] = array();
		$arr[$i][] = "Mode de paiement";
		$arr[$i][] = "Numero de compte";
		$arr[$i][] = "Solde";
		
		$last = 0;
		while ( $rec = $pays->getRecordNext() )
		{
			$arr[++$i] = array();
			$arr[$i][] = $rec["libelle"];
			$arr[$i][] = $rec["numcompte"];
			$arr[$i][] = str_replace(".",$config["regional"]["decimaldelimiter"],$rec["montant"]);
		}
		
		$csv = new csvExport($arr,isset($_GET["msoffice"]));
		$csv->printHeaders("livre-caisse");
		echo $csv->createCSV();
	}
?>
<?php
	$pays->free();
	$bd->free();
?>

