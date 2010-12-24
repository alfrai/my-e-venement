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
	includeLib("bill");
	includeLib("ttt");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("annu");
	
	if ( $user->evtlevel < $config["evt"]["right"]["mod"] )
	{
		$user->addAlert($msg = "Vous n'avez pas un niveau de droits suffisant pour accéder à cette fonctionnalité");
		$nav->redirect($config["website"]["base"]."evt/bill/",$msg);
	}
	
	//$nofacture = true;
	$cantgetback = false;
	$subtitle = "Retour de dépôt - Vente directe";
	$class = "bill evt vdir";
	$spectateur = "Responsable du contingeant";
	
	function pasDeBillet()
	{
		global $user, $bd, $nav;
		
		$user->addAlert("Sans billet demandé, pas d'action possible.");
		$bd->free();
		$nav->redirect($_SERVER["PHP_SELF"]);
	}
	
	$data = array();
	$action = array();
	$data[$name	= "numtransac"]	= $_POST[$name];
	$data[$name	= "manif"]	= $_POST[$name];
	$data[$name	= "client"]	= $_POST[$name];
	$data[$name	= "billet"]	= $_POST[$name];
	$data[$name	= "invendu"]	= $_POST[$name];
	$action[$name	= "filled"]	= isset($_POST[$name]);
	$action[$name	= "printed"]	= isset($_POST[$name]);
	
	// récup d'une transaction en marche
	if ( isset($_POST["oldtransac"]) || isset($_GET["t"]) )
	{
		// numéro de transac
		$data["numtransac"]	= $_POST["oldtransac"] ? $_POST["oldtransac"] : $_GET["t"];
		
		// les manifs sélectionnées
		$query = " SELECT *
			   FROM tickets2print_bytransac('".pg_escape_string($data["numtransac"])."')
			   LEFT JOIN transaction t ON t.id = transaction
			   WHERE transaction IN ( SELECT transaction FROM contingeant )
			     AND t.spaceid ".($user->evtspace ? '= '.$user->evtspace : 'IS NULL');
		$request = new bdRequest($bd,$query);
		$data["manif"]		= array();
		$data["billet"]		= array();
		for ( $nbtickets = 0 ; $rec = $request->getRecordNext() ; $nbtickets++ )
		{
			$data["billet"][intval($rec["manifid"])][] = intval($rec["nb"]).$rec["tarif"].( intval($rec["reduc"]) < 10 ? "0".intval($rec["reduc"]) : intval($rec["reduc"]));
			$nbtickets++;
			if ( !in_array(intval($rec["manifid"]),$data["manif"]) )
				$data["manif"][] = intval($rec["manifid"]);
		}
		$request->free();
		
		// la personne responsable du dépôt
		$query = " SELECT * FROM contingeant WHERE transaction = '".pg_escape_string($data["numtransac"])."'";
		$request = new bdRequest($bd,$query);
		if ( $rec = $request->getRecord() )
			$data["client"] = intval($rec["fctorgid"]) > 0 ? "prof_".intval($rec["fctorgid"]) : "pers_".intval($rec["personneid"]);
		$request->free();
	}
	
	// les pré-requis sont sélectionnés
	if ( $data["numtransac"] && is_array($data["manif"]) //&& $nbtickets > 0
	  && intval(substr($data["client"],5)) > 0 )
	{
		$stage = 2;
		
		// nb de billets pré-réservés
		$nb_billets = 0;
		if ( is_array($data["billet"]) )
		foreach ( $data["billet"] as $manif )
		if ( is_array($manif) )
		foreach ( $manif as $value )
		{
			$resa = preg_tarif($value);
			$nb_billets += abs(intval($resa["nb"]));
		}
		
		// nb de billets invendus
		$nb_invendus = 0;
		if ( is_array($data["invendu"]) )
		foreach ( $data["invendu"] as $manif )
		if ( is_array($manif) )
		foreach ( $manif as $value )
		{
			$resa = preg_tarif($value);
			$nb_invendu += abs(intval($resa["nb"]));
		}
		
		// si pas de billets pré-réservés
		if (( ($action["filled"] && !isset($_POST["stage3"])) || $action["printed"] ) && $nb_billets <= 0 && $nb_invendus > 0 )
			pasDeBillet();
		
		/* stages 3-4 */
		
		if ( $action["filled"] )
		{
			$bd->beginTransaction();
			$ok = true;
			
			// ajout des resas et passage en stage 3 si faisable
			if ( is_array($inv = $_POST["invendu"]) && is_array($masstickets = $_POST["billet"]) )	// les deux variables sont des tableaux
			foreach ( $inv as $manifid => $invendu )						// pour chq manif où on a eu des invendus
			if ( array_key_exists($manifid,$masstickets) )						// si on a bien donné des billets de masse pour cette manif
			foreach ( $masstickets[$manifid] as $massticket )
			{
				$resa = preg_tarif($massticket);
				
				// case non remplie dans les billets rendus
				if ( $invendu[$resa["tarif"].$resa["reduc"]] == "" )
				{
					$stage = 2;	// on revient au stage 2
					$ok = false;	// sans faire de modification aucune
					$user->addAlert("Vous avez mal rempli le nombre de billets retournés : les réponses vides sont interdites");
					break(2);	// on sort de la boucle
				}
				
				if ( intval($invendu[$resa["tarif"].$resa["reduc"]]) >= 0 )
				{
					// correction d'un retour
					if ( ($nb = $resa["nb"]-intval($invendu[$resa["tarif"].$resa["reduc"]])) < 0 )
					{
						$user->addAlert("Attention ! Vous diminuez le nombre de places retournées d'un dépôt.");
						$user->addAlert("Ce genre d'opération ne devrait pas arriver.");
						$user->addAlert("Vérifiez bien ce que vous venez de faire");
						// ATTENTION: ne pas faire d'action relative, sinon risque d'erreur en cas de rechargement de page
						$bd->beginTransaction();
						// on compte combien de tickets ont été enregistrés jusqu'à présent
						$query	= "SELECT (
							    SELECT count(cur.id)
							    FROM reservation_pre AS pre, reservation_cur AS cur
							    WHERE pre.id = cur.resa_preid
							      AND pre.transaction = '".pg_escape_string($data["numtransac"])."'
							      AND pre.tarifid = get_tarifid(".$manifid.",'".pg_escape_string($resa["tarif"])."')
							      AND reduc = ".intval($rec["reduc"])." )
							   + (
							    SELECT nb
							    FROM masstickets
							    WHERE transaction = '".pg_escape_string($data["numtransac"])."'
							    AND tarifid = get_tarifid(".$manifid.",'".pg_escape_string($resa["tarif"])."')
							    AND reduc = ".intval($rec["reduc"])."
							    AND manifid = ".$manifid." ) AS more";
						$mass	= new bdRequest($bd,$query);
						$moremass = intval($mass->getRecord("more"));
						$mass->free();
						// on remet à 0 les tickets soit-disant vendus
						$ok = $ok && $bd->delRecords(
							"reservation_cur",
							"id IN (SELECT cur.id
								FROM reservation_cur AS cur, reservation_pre AS pre
								WHERE pre.transaction = '".pg_escape_string($data["numtransac"])."'
								  AND pre.tarifid = get_tarifid(".$manifid.",'".pg_escape_string($resa["tarif"])."')
								  AND reduc = ".intval($rec["reduc"])."
								  AND pre.id = cur.resa_preid)"
							);
						// on remet toutes les pre-resa concernées sur des places contingentées
						$ok = $ok && $bd->updateRecordsRaw(
							"reservation_pre",
							"    transaction = '".pg_escape_string($data["numtransac"])."'
							 AND tarifid = get_tarifid(".$manifid.",'".pg_escape_string($resa["tarif"])."')
							 AND reduc = ".intval($resa["reduc"])."
							 AND manifid = ".$manifid,
							array("tarifid" => "get_tarifid_contingeant(".$manifid.")", "reduc" => 0)
							);
						// on remet le compteur de masstickets à sa valeur initiale
						$ok = $ok && $bd->updateRecords(
							"masstickets",
							$buf =
							"    transaction = '".$data["numtransac"]."'
							 AND tarifid = get_tarifid(".$manifid.",'".$resa["tarif"]."')
							 AND reduc = ".intval($resa["reduc"])."
							 AND manifid = ".$manifid,
							array("nb" => $moremass)
							);
						$resa["nb"] = $moremass;
						if ( $ok ) $stage = 3;
						$bd->endTransaction($ok);
					}
					
					if ( ($nb = $resa["nb"] - intval($invendu[$resa["tarif"].$resa["reduc"]])) >= 0 )
					{
						$stage = 3;
						
						// s'il y a qqch à faire
						if ( $nb > 0 )
						{
							// MAJ des reservation_pre et ajouts dans reservation_cur pour les billets vendus
							$query = "SELECT decontingeanting(
										'".$data["numtransac"]."',
										".$manifid.",
										".$user->getId().",
										get_tarifid_contingeant(".$manifid."),
										get_tarifid(".$manifid.",'".pg_escape_string($resa["tarif"])."'),
										".$resa["reduc"].",
										".intval($invendu[$resa["tarif"].$resa["reduc"]]).") AS result";
							$request = new bdRequest($bd,$query);
							if ( $request->getRecord("result") != 't' )
							{
								$user->addAlert("Impossible d'ajouter les billets sur-vendus.");
								$ok = false;
							}
							$request->free();
							
							// en cas d'erreur on revient au stage 2
							if ( !$ok ) $stage = 2;
						}
					}
					else
					{
						$user->addAlert("Vous avez eu plus de retours de billets du type ".$resa["tarif"].$resa["reduc"]." qu'il n'en restait ! (max = ".$resa["nb"].")");
						$ok = false;
						$stage = 2;
					}
				} // if ( intval($invendu[$resa["tarif"].$resa["reduc"]]) >= 0 )
			} // foreach ( $masstickets[$manifid] as $massticket )
			
			$bd->endTransaction($ok);

		} // if ( $action["filled"] )
		
		// le reglement
		if ( $stage == 3 || isset($_POST["money"]) || isset($_POST["paid"]) || isset($_POST["facture"]) )
		{
			$stage = 3;
			
			// suppression des paiements sélectionnés
			if ( is_array($_POST["paiement"]["del"]) )
			{
				$arr = array();
				foreach ( $_POST["paiement"]["del"] as $value )
				if ( intval($value) > 0 ) $arr[] = intval($value);
				
				if ( count($arr) > 0 )
				{
					$where	= "(id = ".implode(" OR id = ",$arr).")
						   AND transaction = '".pg_escape_string($data["numtransac"])."'";
					if ( !$bd->delRecords("paiement",$where) )
						$user->addAlert("Erreur lors de la suppression des paiements sélectionnés");
				}
			}
		
			// ajout des paiements à ajouter
			if ( is_array($_POST["paiement"]["montant"]) && is_array($_POST["paiement"]["mode"]) )
			foreach($_POST["paiement"]["montant"] as $num => $montant)
			if ( abs(intval($montant)) > 0 && intval($_POST["paiement"]["mode"][$num]) > 0 )
			{
				$arr = array();
				$arr["modepaiementid"]	= intval($_POST["paiement"]["mode"][$num]);
				$arr["montant"]		= floatval($montant);
				$arr["transaction"]	= $data["numtransac"];
				if ( $_POST["paiement"]["date"][$num]["value"] != $_POST["paiement"]["date"]["default"] && $_POST["paiement"]["date"][$num]["value"] )
					$arr["date"]            = date($config["format"]["sysdate"],strtotime($_POST["paiement"]["date"][$num]["value"]));
							
				if ( !$bd->addRecord("paiement",$arr) )
					$user->addAlert("Impossible d'ajouter le paiement de '".$arr["montant"]."' €");
			}
			
			// récupération des paiements déjà effectués
			$query	= " SELECT * FROM paiement WHERE transaction = '".$data["numtransac"]."' ORDER BY id";
			$request = new bdRequest($bd,$query);
			$data["paiement"] = array();
			for ( $i = 0 ; $rec = $request->getRecordNext() ; $i++ )
				$data["paiement"][$i] = array("id" => intval($rec["id"]), "mode" => intval($rec["modepaiementid"]), "montant" => floatval($rec["montant"]), "date" => $rec["date"]);
			$request->free();
			
			// montant réglé
			$given = 0;
			$prices = array();
			foreach ( $data["paiement"] as $value )
				$given += $value["montant"];
			
			// total à payer
			$prices	= getPrices($data["numtransac"]);
			$topay	= $prices[0];
			
			if ( isset($_POST["facture"]) )
			{
				includePage("facture");
				$bd->free();
				exit(0);
			}
			
			// comparaison -> resa terminée ?
			$reste = $topay - $given;
			if (( $reste <= 0 || $topay == 0 ) && isset($_POST["paid"]))
			{
				$stage = 4;
				includePage("vdir-4");
				$bd->free();
				exit(0);
			}
		
		} // if ($stage == 3 && ( isset($_POST["printed"]) || isset($_POST["paid"]) || isset($_POST["facture"]) || isset($_POST["money"]) ))
		
		if ( $stage < 4 )
			includePage("vdir-2");
	}
	else
	{
		$stage = 1;
		includePage("vdir-1");
	}
	
	$bd->free();
?>
