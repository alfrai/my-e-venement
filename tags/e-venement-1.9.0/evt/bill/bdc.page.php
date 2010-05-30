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
  global $bd,$user,$data,$default,$config,$sqlcount,$css,$compta;
  
  includeClass("csvExport");
  includeClass("reservations");
  
	if ( substr($data["client"],0,4) == "prof" )
	{
		$proid		= intval(substr($data["client"],5));
		if ( $proid )
		  $query  = " SELECT id FROM personne_properso WHERE fctorgid = ".$proid;
		$request  = new bdRequest($bd,$query);
		$clientid = intval($request->getRecord("id"));
		$request->free();
	}
	else
	{
		$clientid = intval(substr($data["client"],5));
		$proid = NULL;
	}
	
	// les vars par défaut
	$msg = "Impossible d'enregistrer le BdC";
	$content = NULL;
	
	$resa = new reservations($bd,$user,$data["numtransac"],$clientid,$proid);
	$places = array();

  $bd->beginTransaction();
  
  // nettoyage des pré-resas en base
  if ( $bd->delRecords('reservation_pre',"transaction = '".pg_escape_string($data['numtransac'])."' AND plnum IS NULL") === false )
    $user->addAlert('Erreur dans le nettoyage des pré-réservations');
  
  if ( is_array($data["billets"] = $_POST["billet"]) )
  foreach ( $data["billets"] as $manifid => $billet )
  if ( intval($manifid) > 0 )
  {
    $places[intval($manifid)]       = array();
    
    if ( is_array($billet) )
    foreach ( $billet as $value )
    {
			// récup des données
			$arr = preg_tarif(strtoupper($value));
			
			// enregistrement des pre-resas et mémorisation des billets
			if ( $resa->addPreReservation(intval($manifid),$arr) )
				$places[intval($manifid)][] = $arr;
			
			unset($arr);
		}
	} // foreach ( $data["billets"] as $manifid => $billet )
	
	$bd->endTransaction();
	
	// enregistrement du BdC en base
	if ( $bd->delRecordsSimple("bdc",array("transaction" => $data["numtransac"])) !== false )
	if ( $bd->addRecord("bdc",array("transaction" => $data["numtransac"], "accountid" => $user->getId())) !== false )
	{
		$msg = NULL;
		$bdcid = $bd->getLastSerial("preselled","id");
		
		$query	= " SELECT transaction, tarif.description AS tarif, evt.nom AS evtnom, manif.date,
			           site.nom AS sitenom, site.ville AS siteville, site.cp AS sitecp,
			           personne.*,
			           ticket.nb, manif.txtva, getprice(manifid,get_tarifid(manifid,tarif)) AS prix 
			    FROM tickets2print_bytransac('".$data["numtransac"]."') AS ticket, manifestation AS manif, site,
			         evenement AS evt, personne_properso AS personne, tarif
			    WHERE personne.id = ".$clientid."
			      AND personne.fctorgid ".($proid ? "= ".$proid : "IS NULL")."
			      AND evt.id = manif.evtid
			      AND ticket.manifid = manif.id
			      AND manif.siteid = site.id
			      AND get_tarifid(manifid,tarif) = tarif.id
			    ORDER BY site.nom, manif.date, tarif.key";
		$request = new bdRequest($bd,$query);
		
		$compta = array();
		$i = 0;
		if ( $rec = $request->getRecord() )
		{
			$compta[$i][]	= $bdcid;				// numéro de BdC
			$compta[$i][]	= $rec["prenom"];			// prenom
			$compta[$i][]	= $rec["nom"];				// nom
			$compta[$i][]	= $rec["orgnom"];			// nom de orga
			$compta[$i][]	= $rec["orgnom"]
					? trim($rec["orgadr"])
					: trim($rec["adresse"]);		// adresse de l'orga
			$compta[$i][] 	= $rec["orgnom"]
					? $rec["orgcp"]
					: $rec["cp"];				// cp de l'orga
			$compta[$i][]	= $rec["orgnom"]
					? $rec["orgville"]
					: $rec["ville"];			// ville de l'orga
			$compta[$i][]	= $rec["orgnom"]
					? $rec["orgpays"]
					: $rec["pays"];				// pays de l'orga
			$compta[$i][]	= $rec["transaction"];			// numéro de transaction
				
			while ( $rec = $request->getRecordNext() )
			{
				$i++;
				$compta[$i][] = $rec["evtnom"];				// titre du spectacle
				$compta[$i][] = date("Y/m/d",strtotime($rec["date"]));	// date
				$compta[$i][] = date("H:i",strtotime($rec["date"]));	// heure
				$compta[$i][] = $rec["sitenom"];				// nom du site
				$compta[$i][] = $rec["siteville"];				// ville du site
				$compta[$i][] = $rec["sitecp"];				// cp du site
				$compta[$i][] = $rec["tarif"];				// tarif
				$compta[$i][] = intval($rec["nb"]);			// nombre
				$compta[$i][] = decimalreplace($rec["prix"]);		// PU
				$compta[$i][] = floatval($rec["prix"])*intval($rec["nb"]);	// total
				$compta[$i][] = decimalreplace($rec["txtva"]);	// taux de TVA en %
			}
		}
    $request->free();
    
    if ( !$config['ticket']['bdc_facture_html_output'] )
    {
      $csv = new csvExport($compta,isset($_POST["msexcel"]));
      $content = $csv->createCSV();
    }
  }
  
  if ( !is_null($msg) )
    $user->addAlert($msg);
  if ( !$config['ticket']['bdc_facture_html_output'] )
  {
    // si on extrait un BdC dans un tableur
    if ( is_object($csv) )
    {
      $csv->printHeaders("bdc");
      echo $content;
    }
    else  $nav->redirect($_SERVER["HTTP_REFERER"]);
  }
  else
  {
    includePage('bdc-facture');
  }
  
  $bd->free();
?>
