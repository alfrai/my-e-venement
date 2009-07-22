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
*    Copyright (c) 2006-2007 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require_once("conf.inc.php");
	includeClass("bdRequest");
	includeLib("bill");
	includeJS("ajax");
	includeJS("plnum","evt");
	
	if ( !isset($id) )
	{
		$args = split("/",$_SERVER["PATH_INFO"]);
		$id = intval($args[1]);
	}
	$css[] = "evt/infos/plans/salle.css.php/".$id;
	
	$query	= " SELECT *,
		      (SELECT (SELECT key FROM tarif WHERE id = tarifid)||reduc
		       FROM reservation_pre
		       WHERE plnum = site_plnum.id
		         AND transaction = ".intval($_POST["numtransac"])."
		         AND manifid = ".intval($manifid).") AS selected,
		      (SELECT count(*) > 0
		       FROM reservation_pre
		       WHERE plnum = site_plnum.id
		         AND manifid = ".intval($manifid)."
		         AND transaction != ".intval($_POST["numtransac"]).") AS reserved
		    FROM site_plnum
		    WHERE siteid = ".$id."
		    ORDER BY plname";
	$request = new bdRequest($bd,$query);
	
	$class .= ($edit = isset($_GET["edit"]) && !isset($bill)) ? " edit" : "";
	
	includeLib("headers");
	
	if ( $edit )
	{
?>
	<script type="text/javascript">
		document.onmouseup = plnum_mouseup;
		document.movingTarget = null;
	</script>
<?php	} ?>
<h1><?php echo $title ?></h1>
<?php
	if ( $bill )
		echo '<form class="plnum" action="evt/bill/billing.php" method="post" name="place">';
	else	echo '<form class="plnum" action="'.htmlsecure($_SERVER["PHP_SELF"]).'" method="get" name="place">';
	
	if ( !isset($bill) )
	{
		echo '<div id="close"><a title="fermer la fenêtre" href="javascript: window.close()"><span>fermer</span></a></div>';
		if ( $edit )
			echo '<div id="plnumedit"><a href="'.htmlsecure($_SERVER["PHP_SELF"]).'">voir le plan</a></div>';
		else	echo '<div id="plnumedit"><a href="'.htmlsecure($_SERVER["PHP_SELF"]).'?edit">éditer le plan</a></div>';
	}
?>
	<div id="mapping">
		<div	id="placesample"
		<?php if ( $edit ) { ?>
			onmousedown="javascript: plnum_mousedown(event,this); this.origX = event.layerX; this.origY = event.layerY;"
			onmouseup="javascript: plnum_mouseup(event,this);"
			ondblclick="javascript: plnum_delete(this);"
		<?php } ?>
			class="place"><span></span></div>
		<?php
			while ( $rec = $request->getRecordNext() )
			{
				/*
				// récup du tarif et de l'id du billet
				if ( $rec["selected"] )
				{
					$tmp = split(":",$rec["selected"]);
					$rec["resaid"] = intval($tmp[0]);
					$resa = preg_tarif($tmp[1]);
					$rec["tarif"]  = " - ".$resa["full"];
				}
				else 	$rec["resaid"] = "null";
				*/
				
				echo '<div ';
				if ( $edit )
				echo 'onmousedown="javascript: plnum_mousedown(event,this); this.origX = event.layerX; this.origY = event.layerY;"
						onmouseup="javascript: plnum_mouseup(event,this);"
						ondblclick="javascript: plnum_delete(this,'.intval($rec["id"]).');" ';
				if ( $bill && $rec['reserved'] != 't' )
				echo 'onclick="javascript: plnum_selectplace(this,'.$manifid.');" ';
				
				if ( $rec["selected"] )
				{
					$tmp = preg_tarif($rec["selected"]);
					$tmp = " - ".$tmp["full"];
				}
				else	$tmp = "";
				echo 'style="'.htmlsecure('	margin-left: '.$rec["onmapx"].';
				      margin-top: '.$rec["onmapy"].';
				      width: '.$rec["width"].';
				      height: '.$rec["height"].';').'"
				      title="'.htmlsecure("num. ".$rec["plname"].$tmp).'"
				      class="place '.($rec['selected'] ? 'selected' : '').' '.($rec["reserved"] == 't' ? "reserved" : "").'">';
				echo '<span>'.intval($rec["id"]).'</span>';
				if ( $rec["selected"] ) echo '<input type="hidden" name="billet['.$manifid.'][]" value="'.htmlsecure($rec["selected"].':plnum-'.$rec["id"]).'" />';
				echo '</div>';
			}
		?>
	</div>
	<?php if ( $edit ) { ?>
	<div	class="body" id="body"
		onmousedown="javascript: plnum_mousedown(event,this);"
		onmouseup="javascript: plnum_mouseup(event,this);">
		<div id="num"></div>
		<div id="area"></div>
	</div>
	<div id="alert"></div>
	<div class="plnum">
		<p>
			<input type="hidden" name="edit" value="" />
			w:&nbsp;<input type="text" name="width" value="<?php echo intval($_GET["width"]) ?>" size="4" maxlength="4" />
			h:&nbsp;<input type="text" name="height" value="<?php echo intval($_GET["height"]) ?>" size="4" maxlength="4" />
			<input type="reset" name="reset" value="RAZ" />
		</p>
		<p>
			rang:&nbsp;<input type="text" name="rowname" value="" size="4" maxlength="6" onchange="javascript: document.place.placename.value=1" />
			num.:&nbsp;<input type="text" name="placename" value="" size="4" maxlength="6" />
			<input type="hidden" name="salleid" value="<?php echo $id ?>" />
		</p>
	</div>
	<?php } elseif ( $bill ) { ?>
	<div class="body">
	</div>
	<div class="plnum">
		<p><input type="submit" name="submit" value="&lt;&lt; valider" />
			<?php
				$arr = array("numtransac","client","manif");
				foreach( $arr as $value )
				{
					if ( is_array($_POST[$value]) )
					foreach ( $_POST[$value] as $subvalue )
					echo '<input type="hidden" name="'.$value.'[]" value="'.htmlsecure($subvalue).'" />';
					else echo '<input type="hidden" name="'.$value.'" value="'.htmlsecure($_POST[$value]).'" />';
				}
				
				if ( is_array($_POST["billet"]) )
				foreach ( $_POST["billet"] as $manif => $billets )
				if ( is_array($billets) )
				foreach ( $billets as $resa )
				if ( $resa )
					echo '<input type="hidden" name="billet['.intval($manif).'][]" value="'.htmlsecure($resa).'" />';
			?>
			Tarif&nbsp;: <input type="text" name="resa" id="focus" value="" size="5" maxlenght="10" />
		</p>
	</div>
	<?php } else { ?>
	<div	class="body">
	</div>
	<?php } ?>
</form>
<?php
	// nettoyage des places concernées (numérotées, de la transaction en cours, de la manif en cours)
	if ( ($nb = $bd->delRecordsSimple("reservation_pre",array("NOT plnum" => NULL, "manifid" => $manifid, "transaction" => intval($_POST["numtransac"])))) === false )
		$user->addAlert("Impossible de nettoyer la base");
	
	$request->free();
	
	includeLib("footer");
	$bd->free()
?>
