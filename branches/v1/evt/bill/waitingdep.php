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
	includeLib("actions");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("bill","evt");
	includeJS("annu");
	
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	$action = $actions["edit"];
	$class .= " contingeant";
	$subtitle = "Dépôts ou contingents non clôturés";
	
	// MAJ des dépots (cloture)
	if ( intval($_GET["close"]) != 0 && $user->evtlevel >= $config["evt"]["right"]["mod"] ) // verif egalement des droits
	if ( !$bd->updateRecordsSimple("contingeant",
	        array("transaction" => intval($_GET["close"]), 't.id' => intval($_GET["close"]), 't.spaceid' => $user->evtspace ? $user->evtspace : NULL),
	        array("closed" => "t"),
	        'transaction t') )
		$user->addAlert("Impossible de fermer le dépôt/contingent #".intval($_GET["close"]).".");
	
	
	// dates limites
	$limit_date = false;	// permet d'activer/désactiver la notion de date limite
	$pastmonth = $_GET["vision"] ? intval($_GET["vision"]) : 2;
	
	// valeurs par défaut (la clé du tableau doit etre la même que la clé du tableau passé en POST)
	$default["nom"] = "-DUPORT-";
	
	includeLib("headers");
	
	$name_start = trim($_GET["s"]) ? trim("".htmlsecure($_GET["s"])) : "";
	$org_start = trim($_GET["o"]) ? trim("".htmlsecure($_GET["o"])) : "";
	
	$query  = " SELECT *
		    FROM waitingdepots
		    LEFT JOIN transaction t ON t.id = transaction
		    WHERE nom ILIKE '".$name_start."%'
		      ".($_GET['spaces'] != 'all' ? "AND t.spaceid ".($user->evtspace ? '= '.$user->evtspace : 'IS NULL') : '')."
		      AND cont > 0
		      AND NOT closed";
	if ( $limit_date )	$query	.= "   AND date >= NOW() - '".$pastmonth." month'::interval";
	if ( $org_start != '' )	$query .= " AND ( orgnom ILIKE '".$org_start."%' )";
	$transacs = new bdRequest($bd,$query);
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
			<input type="text" name="s" id="focus" value="<?php echo htmlsecure($name_start) ?>" />
		</p>
		<p>
			Recherche express sur le nom de l'organisme&nbsp;:<br />
			<input type="text" name="o" value="<?php echo htmlsecure($org_start) ?>" />
		</p>
		<?php if ( $limit_date ) { ?>
		<p>
			Vision&nbsp;:<br />
			<select name="vision">
				<option value="0">à partir d'aujourd'hui</option>
				<?php
					for ( $i = 1 ; $i <= 18 ; $i++ )
						echo '<option value="'.$i.'" '.($i == $pastmonth ? 'selected="selected"' : '').'>'.$i.' mois dans le passé</option>';
				?>
			</select>
		</p>
		<?php } ?>
		<p class="seeall">
			<span class="submit"><input type="submit" name="v" value="Valider" /></span>
			<?php if ( $config['evt']['spaces'] ): ?><span class="spaces"><input type="checkbox" name="spaces" value="all" title="Tous les espaces" <?php echo $_GET['spaces'] == 'all' ? 'checked="checked"' : '' ?> /></span><?php endif ?>
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
		function printTransac($rec)
		{
			if ( intval($rec["total"]) > 0 )
			{
				echo '<p class="transac"><span>';
				echo '#<a href="evt/bill/depot.php?t='.htmlsecure($rec["transaction"]).'">'.htmlsecure($rec["transaction"]).'</a> ';
				echo '<span>(';
				if ( intval($rec["total"]) > 0 )
					echo intval($rec["total"]).' pl. au total';
				if ( ($cont = intval($rec["cont"])) > 0 )
				{
					$nt = $cont > 1 ? 'nt' : '';
					echo ', reste'.$nt.' '.$cont.' pl. cont.';
				}
				if ( ($mass = intval($rec["masstick"])) > 0 )
				{
					echo intval($rec["cont"]) > 0
						? ' et '
						: ', reste'.($mass > 1 ? 'nt' : '').' ';
					$s = $mass > 1 ? 's' : '';
					echo $mass.' billet'.$s.' invendu'.$s;
				}
				echo ')</span>';
				echo "</span></p>\n";
			}
		}
		
		while ( $rec =  $transacs->getRecordNext() )
		{
			$class = $rec["npai"] == 't' ? "npai" : "";
			echo '<li class="'.$class.'">'."\n";
			if ( $user->evtlevel >= $config["evt"]["right"]["mod"] )
			echo '<a class="close" href="'.htmlsecure($_SERVER["PHP_SELF"]).'?spaces='.htmlsecure($_GET['spaces']).'&close='.htmlsecure($rec["transaction"]).'"><span class="in">x</span><span class="out">&nbsp;&nbsp;</span></a>';
			printTransac($rec);
			echo '<p><span class="pers"><a href="ann/fiche.php?id='.$rec["id"].'&view">';
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
			echo '</li>';
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
	$transacs->free();
	$bd->free();
	includeLib("footer");
?>

