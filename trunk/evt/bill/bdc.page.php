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
  global $bd,$user,$data,$default,$config,$sqlcount,$css,$arr;
  
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
		
		$arr = array();
		$i = 0;
		if ( $rec = $request->getRecord() )
		{
			$arr[$i][]	= $bdcid;				// numéro de BdC
			$arr[$i][]	= $rec["prenom"];			// prenom
			$arr[$i][]	= $rec["nom"];				// nom
			$arr[$i][]	= $rec["orgnom"];			// nom de orga
			$arr[$i][]	= $rec["orgnom"]
					? trim($rec["orgadr"])
					: trim($rec["adresse"]);		// adresse de l'orga
			$arr[$i][] 	= $rec["orgnom"]
					? $rec["orgcp"]
					: $rec["cp"];				// cp de l'orga
			$arr[$i][]	= $rec["orgnom"]
					? $rec["orgville"]
					: $rec["ville"];			// ville de l'orga
			$arr[$i][]	= $rec["orgnom"]
					? $rec["orgpays"]
					: $rec["pays"];				// pays de l'orga
			$arr[$i][]	= $rec["transaction"];			// numéro de transaction
				
			while ( $rec = $request->getRecordNext() )
			{
				$i++;
				$arr[$i][] = $rec["evtnom"];				// titre du spectacle
				$arr[$i][] = date("Y/m/d",strtotime($rec["date"]));	// date
				$arr[$i][] = date("H:i",strtotime($rec["date"]));	// heure
				$arr[$i][] = $rec["sitenom"];				// nom du site
				$arr[$i][] = $rec["siteville"];				// ville du site
				$arr[$i][] = $rec["sitecp"];				// cp du site
				$arr[$i][] = $rec["tarif"];				// tarif
				$arr[$i][] = intval($rec["nb"]);			// nombre
				$arr[$i][] = decimalreplace($rec["prix"]);		// PU
				$arr[$i][] = floatval($rec["prix"])*intval($rec["nb"]);	// total
				$arr[$i][] = decimalreplace($rec["txtva"]);	// taux de TVA en %
			}
		}
    $request->free();
    
    if ( !$config['ticket']['bdc_facture_html_output'] )
    {
      $csv = new csvExport($arr,isset($_POST["msexcel"]));
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
    /*
    // si on sort le BdC en html
    $title = 'BdC';
    $css[] = 'evt/styles/bdc-facture.css';
    includeLib('headers');
    
    echo '<div id="seller">';
    $seller = $config['ticket']['seller'];
    if ( is_array($seller) )
    {
      if ( $seller['logo'] )
      echo '<p class="logo"><img alt="logo" src="'.htmlsecure($seller['logo']).'" /></p>';
      unset($seller['logo']);
      
      foreach ( $seller as $key => $value )
        echo '<p class="'.htmlsecure($key).'">'.htmlsecure($value).'</p>';
    }
    echo '</div>';
    
    // les données client
    $tmp = array_shift($arr);
    $customer = array('bdcid','prenom','nom','orgnom','adresse','cp','ville','pays','transaction');
    echo '<div id="customer">';
    foreach ( $customer as $key => $value )
      echo '<p class="'.$value.'">'.$tmp[$key].'</p>';
    echo '</div>';
    
    // récupération du numéro de bon de commande et de transaction
    $bdcid = intval($tmp[0]);
    $transac = $tmp[count($tmp)-1];
    
    echo '<p id="ids">Bon de commande <span class="bdcid">#'.$bdcid.'</span> (pour l\'opération <span class="transac">#'.$transac.'</span>)</p>';
    
    // les lignes du bdc
    $ligne = array('evt','date','heure','salle','ville','cp','tarif','nb','pu','ttc','tva','ht');
    $totaux = array('ht' => 0, 'tva' => array(), 'ttc' => 0);
    $engil = array(); // permet d'avoir le rang d'une valeur recherché
    foreach ( $ligne as $key => $value )
      $engil[$value] = $key;
    
    echo '<table id="lines">';
    while ( $tmp = array_shift($arr) )
    {
      $tva = floatval(str_replace(',','.',$tmp[$engil['tva']]))/100;
      
      // les totaux
      $totaux['ttc'] += $tmp[$engil['ttc']];
      $totaux['ht'] += $tmp[$engil['ttc']]/(1+$tva); 
      $totaux['tva'][$tmp[$engil['tva']].''] += $tmp[$engil['ttc']] - $tmp[$engil['ttc']]/(1+$tva);
      
      // les arrondis, les calculs TVA
      $tmp[$engil['ht']]    = round($tmp[$engil['ttc']]/(1+$tva),2);
      $tmp[$engil['pu']]    = round($tmp[$engil['pu']],2);
      $tmp[$engil['ttc']]   = round($tmp[$engil['ttc']],2);
      
      $tmp[$engil['date']]  = date('d/m/Y',strtotime($tmp[$engil['date']]));
      echo '<tr>';
      foreach ( $ligne as $key => $value )
        echo '<td class="'.$value.'">'.$tmp[$key].'</p>';
      echo '</tr>';
    }
    echo '
      <thead><tr>
        <th class="evt">Evènement</p>
        <th class="date">Date</p>
        <th class="heure">Heure</p>
        <th class="salle">Salle</p>
        <th class="ville">Ville</p>
        <th class="cp">CP</p>
        <th class="tarif">Tarif</p>
        <th class="nb">Qté</p>
        <th class="pu">PU TTC</p>
        <th class="ttc">TTC</p>
        <th class="tva">TVA</p>
        <th class="ht">HT</p>
      </tr></thead>';
    echo '</table>';
    
    echo '<div id="totaux">';
      echo '<p class="total"><span>Total HT:</span><span class="float">'.round($totaux['ht'],2).'</span></p>';
      foreach ( $totaux['tva'] as $key => $value )
      echo '<p class="tva"><span>TVA '.$key.'%:</span><span class="float">'.round($value,2).'</span></p>';
      echo '<p class="ttc"><span>Total TTC:</span><span class="float">'.round($totaux['ttc'],2).'</span></p>';
    echo '</div>';
    
    includeLib('footer');
    */
  }
  
  $bd->free();
?>
