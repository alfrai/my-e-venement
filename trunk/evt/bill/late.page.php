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
	includeClass("bdRequest");
	includeLib("ttt");
	includeLib("actions");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("bill","evt");
	includeJS("annu");
	
	global $query, $class, $credit, $title, $subtitle, $seeall, $order, $user;
	global $flashdate;
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$action = $actions["edit"];
	
	// valeurs par défaut (la clé du tableau doit etre la même que la clé du tableau passé en POST)
	$default["nom"] = "-DUPORT-";
	
	includeLib("headers");
	
	$name_start = trim($_GET["s"]) ? trim("".htmlsecure($_GET["s"])) : "";
	$org_start = trim($_GET["o"]) ? trim("".htmlsecure($_GET["o"])) : "";
	
	if ( $name_start != '' ) $query .= " AND nom ILIKE '".$name_start."%' ";
	if ( $org_start != '' )  $query .= " AND ( orgnom ILIKE '".$org_start."%' )";
	$query .= isset($order) ? $order : " ORDER BY nom, prenom, orgnom, transaction";
	$personnes = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php") ?>
<div class="body">
<h2><?php echo $subtitle ?></h2>
<div class="search top">
	<form name="formu" action="<?php echo $_SERVER["PHP_SELF"]?>" method="GET">
		<p>
			Recherche express sur le nom de famille&nbsp;:<br />
			<input type="text" name="s" id="focus" value="<?php echo $name_start ?>" />
		</p>
		<p>
			Recherche express sur le nom de l'organisme&nbsp;:<br />
			<input type="text" name="o" value="<?php echo $org_start ?>" />
		</p>
		<?php if ( $flashdate ) { ?>
		<p>
			Se positionner à la date (AAAA-MM-JJ) :<br/>
			<input type="text" name="flashdate" value="<?php echo htmlsecure($_GET["flashdate"]) ?>" />
		</p>
		<?php } ?>
		<p class="seeall">
			<span class="submit"><input type="submit" name="v" value="Valider" /></span>
			<?php if ( $credit ) { ?>
			<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input type="checkbox" name="seeall" value="yes" onclick="javascript: ttt_spanCheckBox(this);" <?php if ( $seeall ) echo 'checked="checked"'; ?>/>
				Montrer tout ?
			</span>
			<?php } ?>
		</p>
	</form>
</div>
<p class="letters top">
<?php
	$alphabet = "abcdefghijklmnopqrstuvwxyz";
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?s='.$cur.'">'.$cur.'</a> ';
?>
</p>
<ul class="contacts" id="personnes">
	<?php
		$nmegatotal = $megatotal = 0;
		while ( $rec =  $personnes->getRecord() )
		{
			$class = $rec["npai"] == 't' ? "npai" : "";
			echo '<li class="'.$class.'">'."\n";
			echo '<p>';
			if ( intval($rec["factureid"]) > 0 ) echo '<span class="numfact">FB'.intval($rec["factureid"]).'</span> #<a class="numop" href="evt/bill/billing.php?t='.htmlsecure($rec["transaction"]).'">'.htmlsecure($rec["transaction"]).'</a> ';
			echo '<span class="pers"><a href="ann/fiche.php?id='.$rec["id"].'&view">';
			echo htmlsecure($rec["nom"].' '.$rec["prenom"]);
			echo '</a>';
			if ( intval($rec["orgid"]) > 0 )
			{
				echo ' (<a href="org/fiche.php?id='.intval($rec["orgid"]).'&view">';
				echo htmlsecure($rec["orgnom"]).'</a>';
				if ( $fct = $rec["fctdesc"] ? $rec["fctdesc"] : $rec["fcttype"] )
				echo ' - '.htmlsecure($fct);
				echo ')';
			}
			echo "</span></p>\n";
			
			// la premiere entrée
			$total  = ($tmp = floatval($rec["topay"]) - floatval($rec["paid"])) > 0 ? $tmp : 0;
			$ntotal = ($tmp = floatval($rec["topay"]) - floatval($rec["paid"])) < 0 ? $tmp : 0;
			if ( !intval($rec["factureid"]) )
			{
  			echo ' <p class="transac"><span>';
  			echo '#<a href="evt/bill/billing.php?t='.htmlsecure($rec["transaction"]).'">'.htmlsecure($rec["transaction"]).'</a>';
  			if ( $credit ) echo ' (<span class="'.(floatval($rec["topay"]) - floatval($rec["paid"]) > 0 ? 'amount' : '').'">'.abs(floatval($rec["topay"])-floatval($rec["paid"])).'€</span>)';
  			echo "</span></p>\n";
  		}
			
			$last["persid"]	= intval($rec["id"]);
			$last["proid"]	= intval($rec["fctorgid"]);
			
			// les entrées suivantes
			while ( $rec = $personnes->getNextRecord() )
			if ( intval($rec["id"]) == $last["persid"] && intval($rec["fctorgid"]) == $last["proid"] && !isset($rec["factureid"]) )
			{
				$total  += ($tmp = floatval($rec["topay"]) - floatval($rec["paid"])) > 0 ? $tmp : 0;
				$ntotal += ($tmp = floatval($rec["topay"]) - floatval($rec["paid"])) < 0 ? $tmp : 0;
				echo '<p class="transac">'.($credit ? ' + ' : '').'<span>';
				echo '#<a href="evt/bill/billing.php?t='.htmlsecure($rec["transaction"]).'">'.htmlsecure($rec["transaction"]).'</a>';
				if ( $credit ) echo ' (<span class="'.(floatval($rec["topay"]) - floatval($rec["paid"]) > 0 ? 'amount' : '').'">'.abs(floatval($rec["topay"]) - floatval($rec["paid"])).'€</span>)';
				echo "</span></p>\n";
			}
			else	break;
			
			if ( abs($total)  > 0 ) $megatotal += $total;
			if ( abs($ntotal) > 0 ) $nmegatotal += $ntotal;
			if ( $credit )
			{
				echo '<p class="total">';
				echo '<span class="'.($total > 0 ? 'amount' : '').'">'.abs($total)."€</span> ";
				if ( $seeall ) echo '<span class="ntotal">'.abs($ntotal)."€</span>";
				echo "</p>\n";
			}
			echo '</li>';
		}
		if ( $credit )
		{
			echo '<li><p>';
			echo '<span>Total des dettes: </span></p>'."\n";
			echo '<p class="total">';
			echo '<span class="'.($megatotal > 0 ? 'amount' : '').'">'.abs($megatotal).'€</span> ';
			if ( $seeall ) echo '<span class="ntotal">'.abs($nmegatotal).'€</span>';
			echo '</p></li>';
		}
	?>
</ul>
<p class="letters bottom">
<?php
	for ( $i = 0 ; $cur = strtoupper($alphabet{$i}) ; $i++ )
		echo '<a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?s='.$cur.'">'.$cur.'</a> ';
?>
</p>
</div>
<?php
	$personnes->free();
	$bd->free();
	includeLib("footer");
?>
