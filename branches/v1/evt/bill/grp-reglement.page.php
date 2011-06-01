<?php
	global $bd, $user, $data, $prices, $nofacture, $sqlcount;
	
	$reste = $prices[0];
	if ( is_array($data["paiement"]) )
	foreach ( $data["paiement"] as $pay )
		$reste -= $pay["montant"];
?>
<div class="bill">
<p class="titre"><span>Règlements</span></p>
<?php
	$query = " SELECT * FROM modepaiement ORDER BY libelle";
	$modes = new bdRequest($bd,$query);
	if ( is_array($data["paiement"]) )
	foreach ( $data["paiement"] as $value )
	{
?>
<p class="paid">
	<span>
		<span class="del"><input type="checkbox" name="paiement[del][]" value="<?php echo intval($value["id"]) ?>" /><span class="desc">Retirer ce règlement</span></span>
		<span class="titre">Règlement&nbsp;: </span>
		<span class="montant"><?php echo floatval($value["montant"]) ?> €</span>
		<span class="mode">(<?php
			$modes->firstRecord();
			while ( $rec = $modes->getRecordNext() )
			if ( intval($rec["id"]) == intval($value["mode"]) )
				echo htmlsecure(strtolower($rec["libelle"]));
		?>)</span>
		<span class="date"><?php echo htmlsecure(date($config["format"]["date"],strtotime($value["date"]))) ?></span>
	</span>
</p><?php } ?>
<p class="paymode">
	<span>
		<span>Montant du règlement&nbsp;: </span>
		<span><input	id="focus" type="text" name="paiement[montant][]" value="" class="montant"
				onblur="javascript: bill_addLine(this,this.parentNode.parentNode.parentNode,document.getElementById('reste'));" /> €</span>
	</span>
	<span>
		<span>Mode de paiement&nbsp;: </span>
		<span><select name="paiement[mode][]"><?php
			echo '<option value="">-mode de paiement-</option>';
			$modes->firstRecord();
			while ( $rec = $modes->getRecordNext() )
				echo '<option value="'.htmlsecure($rec["id"]).'">'.htmlsecure($rec["libelle"]).'</option>';
		?></select></span>
	</span>
	<span>
		<span>Date de valeur&nbsp;: </span>
		<span>
			<?php
				global $actions, $action;
				$actions = array();
				$action = $actions["edit"] = true;
				$defaultdate = date($config["format"]["sysdate"]);
				printField("paiement[date][]","",$defaultdate,12,10);
			?>
			<span class="desc">Ce champ est optionnel</span>
		</span>
	</span>
</p>
<input type="hidden" name="paiement[date][default]" value="<?php echo $defaultdate ?>" />
<p class="reste" id="reste"><?php
	echo '<span>';
	echo $reste > 0 ? 'Reste dû' : 'À rendre';
	echo '&nbsp;: </span>';
	echo '<span class="reste'.($reste >= 0 ? " debiteur" : " crediteur").'">'.abs(floatval($reste)).'&nbsp;€</span>';
	$modes->free();
?></p>
</div>
<div class="valid">
	<p class="submit"><input type="submit" name="money" value="Ajouter" /></p>
	<?php if ( !$nofacture ) { ?>
		<p class="print"><input type="submit" name="facture" value="Facture" />
			<span onclick="javascript: ttt_spanCheckBox(this.getElementsByTagName('input').item(0));">
				<input onclick="javascript: ttt_spanCheckBox(this);" type="checkbox" name="msexcel" value="" />MSExcel
			</span>
		</p>
	<?php } ?>
</div>
