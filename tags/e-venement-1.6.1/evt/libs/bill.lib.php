<?
/**********************************************************************************
*
*	    This file is part of beta-libs.
*
*    beta-libs is free software; you can redistribute it and/or modify
*    it under the terms of the GNU Lesser General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU Lesser General Public License for more details.
*
*    You should have received a copy of the GNU Lesser General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/

	function preg_tarif($txt)
	{
		$preg_tarif = '/^(-{0,1}\d*)([a-zA-Z]{1,5})(\d{0,2})(:.+){0,1}$/';
		$arr = array();
		preg_match_all($preg_tarif,$txt,$arr);
		if ( $arr[2][0] )
		{
			$r = array();
			$r["tarif"]	= strtoupper($arr[2][0]);
			$r["nb"]	= $arr[1][0] === "" ? 1 : intval($arr[1][0]);	// was: $arr[1][0] == 0 ? 1 : intval($arr[1][0]);
			$r["reduc"]	= $arr[3][0] ? $arr[3][0] : '00';
			$r["other"]	= $arr[4][0] ? substr($arr[4][0],1) : '';	// permet de passer d'autres informations informelles après un caractère ":"
			$r["full"]	= $r["nb"].$r["tarif"].$r["reduc"];
			return $r;
		}
		else	return $arr;
	}
	
	function printHiddenFields($data)
	{
		if ( isset($data["numtransac"]) )
			_printHiddenField("numtransac",$data["numtransac"]);
		if ( isset($data["client"]) )
			_printHiddenField("client",$data["client"]);
		if ( is_array($data["manif"]) )
		foreach ( $data["manif"] as $value )
			_printHiddenField("manif[]",$value);
	}
	function _printHiddenField($name,$value)
	{ echo '<input type="hidden" name="'.$name.'" value="'.$value.'" />'; }
	
	function printManif($manif)
	{
		global $config, $default;
		
		echo '<span class="nom">';
		echo '<a href="evt/infos/manif.php?evtid='.intval($manif["id"]).'&id='.intval($manif["manifid"]).'&view">'.htmlsecure($manif["nom"]).'</a>';
		echo '<span class="desc">'.$default["opennewpage"].'</span>';
		echo '</span> ';
		echo 'à <span class="ville">'.htmlsecure($manif["ville"]).'</span> ';
		echo '(<span class="salle">'.htmlsecure($manif["sitenom"]).'</span>), ';
		echo 'le <span class="date '.htmlsecure($manif["colorname"]).(strtotime($manif["date"]) < strtotime('now') ? " past" : " tocome").'">';
			echo $config["dates"]["dotw"][intval(date('w',strtotime($manif["date"])))].' '.date($config["format"]["date"],strtotime($manif["date"]));
			echo ' à '.date($config["format"]["ltltime"],strtotime($manif["date"]));
		echo '</span>';
	}
	
	// $billets: array(manifid => array(1st place desc, 2nd place desc, ...), ...)
	// les [place desc] sont l'équivalent de ce que peut ressortir preg_tarif()
	function getPrices($transactionid, $printed = false)
	{
		global $bd;
		
		// total à payer
		$topay		= 0;
		$prices		= array();
		
		// accélération notoire du calcul des sommes à payer
  		$more	= $printed ? " AND id IN (SELECT resa_preid FROM reservation_cur WHERE NOT canceled)" : "";
		$query	= " SELECT manifid, SUM((NOT annul)::integer*2-1)*getprice(manifid,tarifid)*(1-reduc/100) AS prix
			    FROM reservation_pre AS pre
			    WHERE transaction = ".pg_escape_string($transactionid)." ".$more."
			    GROUP BY tarifid,manifid,reduc";
		/*
		$query	= " SELECT getprice(manifid,tarif)*nb*(100-reduc)/100 AS prix, manifid
			    FROM tickets2print_bytransac('".pg_escape_string($transactionid)."')";
		if ( $printed )
		$query .= " WHERE NOT canceled AND printed";
		*/
		$request = new bdRequest($bd,$query);

		while ( $rec = $request->getRecordNext() )
		{
			$prices[0] += floatval($rec["prix"]);
			$prices[intval($rec["manifid"])] += floatval($rec["prix"]);
		}
		$request->free();
		
		return $prices;
	}
	
	function decimalreplace($val)
	{
		global $config;
		return $config["regional"]["decimaldelimiter"] != "."
			? str_replace(".",$config["regional"]["decimaldelimiter"],$val)
			: $val;
	}
?>
