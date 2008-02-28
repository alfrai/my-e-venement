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

includeClass("bdRequest");

function sco_printSelectManif($request)
{
	global $config;
	$r = "";
	for ( $catid = 0 ; $rec = $request->getRecordNext() ; )
	{
		if ( $catid != intval($rec["categorie"]) )
		{
			if ( $catid != 0 ) $r .= '</optgroup>';
				$r .= '<optgroup label="'.htmlsecure($rec['catdesc']).'">';
			$catid = intval($rec["categorie"]);
		}
		$r .= '<option value="'.intval($rec["manifid"]).'">';
		$r .= htmlsecure($rec["nom"].' le '.date($config["format"]["date"].' ('.$config["format"]["maniftime"].')',strtotime($rec["date"])));
		$r .= htmlsecure(' à '.$rec["siteville"].' ('.$rec["sitenom"].')').'</option>';
	}
	
	return $r;
}

// $tabpersid: id dans le tableau_personne
function sco_transpose($tabpersid)
{
	global $bd, $user;
	$bd->beginTransaction();
	$ok = true;
	
	$query	= " SELECT pers.id AS personneid, pers.fctorgid, manif.id AS manifid, ticket.tarifid, ticket.reduc, ticket.nb
		    FROM entry, manifestation AS manif, tableau_manif AS tabmanif, ticket,
		    	 personne_properso AS pers, tableau_personne AS tabpers
		    WHERE entry.tabpersid = ".intval($tabpersid)."
		      AND entry.valid
		      AND ticket.entryid = entry.id
		      AND manif.id = tabmanif.manifid
		      AND tabmanif.id = entry.tabmanifid
		      AND tabpers.id = entry.tabpersid
		      AND tabpers.personneid = pers.id
		      AND (tabpers.fctorgid = pers.fctorgid OR tabpers.fctorgid IS NULL AND pers.fctorgid IS NULL)";
	$request = new bdRequest($bd,$query);
	if ( $rec = $request->getRecord() )
	{
		$arr = array();
		$arr["personneid"]	= intval($rec["personneid"]);
		$arr["fctorgid"]	= intval($rec["fctorgid"]) > 0 ? intval($rec["fctorgid"]) : NULL;
		$arr["accountid"]	= $user->getId();
		// on créé la transaction
		if ( $ok = $ok && $bd->addRecord("transaction",$arr) )				// création de la transaction
		{
			// récup du numéro de transaction créé
			$transac = $bd->getLastSerial("transaction","id");
			
			// on note la transposition dans tableau_personne pour mettre des disabled partout
			$ok = $ok && $bd->updateRecordsSimple("tableau_personne",array("id" => intval($tabpersid)),array("transposed" => $transac));
			
			// on fait les reservation_pre
			while ( $rec = $request->getRecordNext() )
			for ( $i = 0 ; $i < $rec["nb"] ; $i++ )
			{
				$arr = array();
				$arr["accountid"]	= $user->getId();
				$arr["transaction"]	= $transac;
				$arr["manifid"]		= intval($rec["manifid"]);
				$arr["tarifid"]		= intval($rec["tarifid"]);
				$arr["reduc"]		= intval($rec["reduc"]);
				$ok = $ok && $bd->addRecord("reservation_pre",$arr);			// création des reservation_pre unitaires
			}
		}
	}
	
	$bd->endTransaction($ok);
	$request->free();
	
	return $ok ? $transac : false;
}

// $tabpersid: id dans le tableau_personne
function sco_untranspose($tabpersid)
{
	global $bd,$nav;
	$request = new bdRequest($bd,$query=" SELECT transposed FROM tableau_personne AS tabpers WHERE id = ".$tabpersid);
	$t = $request->getRecord("transposed");
	$request->free();
	
	if ( $r = $bd->updateRecordsSimple("tableau_personne",array("id" => $tabpersid),array("transposed" => NULL, "confirmed" => "f", "conftext" => NULL)) > 0 )
	{
		$nav->redirect("../evt/bill/annul.php?pretransac=".$t);
		$bd->free();
	}
	else	return $r;
}

?>
