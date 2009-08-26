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
	includeLib("actions");
	includeLib("ttt");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("annu");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	$id = intval($_GET["id"]);
	
	// mise en forme des données
	$new = &$_POST["field"];
	$tel = &$_POST["tel"];
	
	if ( $user->hasRight($nav,$config["right"]["view"]) )
		$action = $actions["view"];
	else	exit(0);
	
	// acquisition des données à afficher
	$request = false;
	if ( $id > 0 )
	{
		$query	= " SELECT *
			    FROM personne
			    WHERE id = ".$id;
		$request = new bdRequest($bd,$query);
		
		if ( $request->countRecords() == 0 )
		{
			$request->free();
			$request = false;
		}
	}
	
	if ( $rec["npai"] == 't' )
		$class = "npai";
	
	if ( !$request && $action != $actions["add"] )
	{
		exit(0);
	}
	elseif ( $request )
	{
		$rec = $request->getRecord();
		$request->free();
	}
?>
<?php
	$query	= " SELECT str FROM str_model WHERE usage = 'titretype'";
	$titregen = new bdRequest($bd,$query);
?>
<span onclick="javascript: this.parentNode.className='';" id="ficheindivclose"></span>
<form class="personne<?php if ( $rec["npai"] == 't' ) echo " npai"?>" name="formu" action="ann/fiche.php?id=<?php echo $action == $actions["add"] ? 0 : $id ?>&view" method="post">
	<div class="contact">
		<p class="titre">Contact</p>
		<input type="hidden" name="fctdefault" value="<?php echo htmlsecure($fctDefault) ?>" />
		<input type="hidden" name="emaildefault" value="<?php echo htmlsecure($emailDefault) ?>" />
		<input type="hidden" name="srvdefault" value="<?php echo htmlsecure($srvDefault) ?>" />
		<input type="hidden" name="typedefault" value="<?php echo htmlsecure($typeDefault) ?>" />
		<input type="hidden" name="numdefault" value="<?php echo htmlsecure($numDefault) ?>" />
		<input type="hidden" name="descdefault" value="<?php echo htmlsecure($default["description"]) ?>" />
		<input type="hidden" name="isnan" value="<?php echo htmlsecure("Veuillez saisir un numéro de téléphone valide") ?>" />
		<input type="hidden" name="id" value="<?php echo $id ?>" />
		<p class="global">
			<span>
				<?php
					if ( $action != $actions["add"] && $action != $actions["edit"] )
						echo htmlsecure($rec["titre"]);
					else
					{
				?>
				<select class="titre" name="field[titre][value]" id="focus">
					<option value=""></option>
					<?php
						while ( $titre = $titregen->getRecordNext('str') )
							echo '<option value="'.htmlsecure($titre).'" '.($rec["titre"] == $titre ? 'selected="selected"' : "").'>'.htmlsecure($titre).'</option>';
					?>
				</select>
				<?php	} ?>
			</span>
			<span><?php printField("field[".($name = "nom")."]",$rec[$name],"-DUPORT-",127,15,NULL,NULL,"ttt_nomblur(this,'-DUPORT-')",NULL,'onkeyup="javascript: annu_search(this)"') ?></span>
			<span><?php printField("field[".($name = "prenom")."]",$rec[$name],"-Ilene-",255,15) ?></span>
		</p>
		<p class="adresse">
			<span><?php printField("field[".($name = "adresse")."]",$rec[$name],"-3, rue du Stang-",NULL,NULL,true) ?></span>
			<br/>
			<span><?php printField("field[".($name = "cp")."]",$rec[$name],"-29640-",10,6) ?></span>
			<span><?php printField("field[".($name = "ville")."]",$rec[$name],"-Bolazec-",255) ?></span>
			<br />
			<span><?php printField("field[".($name = "pays")."]",$rec[$name],"-France-",255) ?></span>
		</p>
		<p class="email">
			<span>
			<?php
				if ( $action == $actions["view"] ) echo '<a class="email" href="mailto:'.$rec["email"].'">';
				printField("field[".($name = "email")."]",$rec[$name],"-i.duport@dom.tld-",255);
				if ( $action == $actions["view"] ) echo '</a>';
			?>
			</span>
		</p>
		<p class="npai">
			<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(1));">
			<input type="hidden" name="field[npai][value]" value="f" />
			<?php
				if ( $action == $actions["edit"] )
				{
					echo '<input type="checkbox" name="field[npai][value]" value="t"';
					if ( $rec["npai"] == 't' ) echo ' checked="checked"';
					echo ' onclick="javascript: ttt_spanCheckBox(this)" />';
				}
				if ( $action == $actions["edit"] || $rec["npai"] == 't' )
					echo "<span>N'habite plus l'adresse indiquée</span>";
			?>
			</span>
		</p>
		<?php
			if ( $action != $actions["add"] )
			{
		?>
		<p class="dates">
			<span>Créé le <?php echo date($config["format"]["date"].' à '.$config["format"]["time"],strtotime($rec["creation"])) ?></span>
			<span>
				Modifié le
				<?php
					echo date($config["format"]["date"].' à '.$config["format"]["time"],
						$action == $actions["edit"] ? strtotime("now") : strtotime($rec["modification"]) );
				?>
			</span>
		</p>
		<?php	} ?>
	</div>
	<div class="tel">
		<p class="titre">Téléphones</p>
		<div class="clip">
		<?php
			if ( $action == $actions["edit"] || $action == $actions["add"] )
			{
				$query  = " SELECT str AS type FROM str_model WHERE usage = 'teltype' ORDER BY str";
				$typegen = new bdRequest($bd,$query);
		?>
		<p id="telmodel" class="tel">
			<span class="type">
				<input	type="text" name="tel[type][][value]"
					class="exemple"
					value="<?php echo $typeDefault ?>"
					onfocus="javascript:ttt_onfocus(this,'<?php echo $typeDefault ?>');"
					onblur="javascript: ttt_onblur(this,'<?php echo $typeDefault ?>');"
					size="16" maxlength="127" />
				<select	name="tel[typegen][][value]" size="<?php echo $typegen->countRecords() + 1 ?>"
					onchange="javascript: ttt_teltypegen(this,'<?php echo $typeDefault ?>');">
					<option value=""></option>
					<?php
						$typegen->firstRecord();
						while ( $rec = $typegen->getRecordNext("type") )
							echo '<option value="'.$rec.'">'.$rec.'</option>';
					?>
				</select>
			</span>
			<span class="num">
				<input	type="text" name="tel[num][][value]"
					class="exemple"
					value="<?php echo $numDefault ?>" size="14" maxlength="40"
					onfocus="javascript: ttt_onfocus(this,'<?php echo $numDefault ?>');"
					onblur="javascript: ttt_tel(this,'<?php echo $numDefault ?>',true);" />
			</span>
		</p>
		<?php
			} // if ( $action == $actions["edit"] || $action == $actions["add"] )
			
			$query	= " SELECT *
				    FROM telephone_personne
				    WHERE entiteid = ".$id;
			if ( $action == $actions["add"] ) $query = NULL;
			$request = new bdRequest($bd,$query);
			
			while ( $rec = $request->getRecordNext() )
			{
		?>
		<p class="tel">
			<span class="type">
				<?php
					printField("tel[type][]",$rec["type"],$typeDefault,127,16,false,NULL,NULL,false);
					if ( $action == $actions["edit"] || $action == $actions["add"] )
					{
				?>
				<select	name="tel[typegen][][value]" size="<?php echo $typegen->countRecords() + 1 ?>"
					onchange="javascript: ttt_teltypegen(this,'<?php echo $typeDefault ?>');">
					<option value=""></option>
					<?php
						$typegen->firstRecord();
						while ( $type = $typegen->getRecordNext("type") )
							echo '<option value="'.$type.'">'.$type.'</option>';
					?>
				</select>
				<?php } ?>
			</span>
			<span class="num"><?php printField("tel[num][]",$rec["numero"],$numDefault,40,14,false,NULL,"ttt_tel(this,'".$numDefault."',false)",false); ?></span>
		</p>
		<?php
			} // while ( $rec = $request->getRecordNext() )
			$request->free();
		?>
		</div>
	</div>
	<div class="access">
		<p><a href="ann/fiche.php?id=<?php echo $id ?>&edit" title="Ce lien est fait pour être ouvert dans un nouvel onglet... (ctrl+clic)">Accédez à la fiche</a> &gt;&gt;</p>
		<p>(pour modification).</p>
	</div>
</form>
</div>
<?php
	$titregen->free();
	$bd->free();
?>
