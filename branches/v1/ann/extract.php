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
	includeClass("csvExport");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	// données envoyées en POST ou en GET
	$vars		= $_GET["csv"];
	$vars['grpid'] = $_GET['grpid'];
	if ( !isset($vars["fields"]) )
		$vars["fields"]	= $_POST["csv"]["fields"];
	if ( !isset($vars["persid"]) && !isset($vars["fctorgid"]) )
	{
		$vars["persid"]		= $_POST["csv"]["persid"];
		$vars["fctorgid"]	= $_POST["csv"]["fctorgid"];
	}
	if ( !isset($vars["grpid"]) ) $vars["grpid"] = $_POST["csv"]["grpid"];
	
	$labels = isset($_POST['labels']);
	$printfields = ( isset($_GET["printfields"]) ? $_GET["printfields"] == "yes" : $_POST["printfields"] == "yes" ) && !$labels;
	$msexcel = isset($_GET["msexcel"]) ? $_GET["msexcel"] == "yes" : $_POST["msexcel"] == "yes";
	$entonnoir = ( isset($_GET["entonnoir"]) ? $_GET["entonnoir"] == "yes" : $_POST["entonnoir"] == "yes" ) || $labels;
	
	$fields		= array();
	$persid		= array();
	$fctorgid	= array();
	
	if ( is_array($vars["persid"]) )
	foreach ( $vars["persid"] as $value )
		$persid[] = intval($value);
	if ( is_array($vars["fctorgid"]) )
	foreach ( $vars["fctorgid"] as $value )
		$fctorgid[] = intval($value);
	if ( is_array($vars["fields"]) )
	foreach ( $vars["fields"] as $value )
		$fields[] = pg_escape_string($value);
	
	// enregistrement des préférences
	$arr = array();
	$opt = $fields;
	$arr["value"]		= implode(";",$fields);
	if ( $entonnoir )	$arr["value"] .= ";entonnoir";
	if ( $msexcel )		$arr["value"] .= ";msexcel";
	if ( $printfields )	$arr["value"] .= ";printfields";
	$cond = array();
	$cond["accountid"] = $user->getId();
	$cond["key"] = "ann.extractor";
	if ( $bd->updateRecordsSimple("options",$cond,$arr) === 0 )
	{
		$arr = array_merge($arr,$cond);
		$bd->addRecord("options",$arr);
	}
	
	// infos à extraire
	$info = false;
	if ( $tmp = array_search("info",$fields) )
	{
		unset($fields[$tmp]);
		$info = true;
	}
	
	if ( count($fields) > 0 )
	{
		// possibilité d'avoir des infos à extraire && groupe static
		if ( intval($vars["grpid"])."" == $vars["grpid"]."" )
		{
			$vars["persid"] = array();
			$vars["fctorgid"] = array();
			$query	= '(SELECT personne."'.implode('",personne."',$fields).'" '.($info ? ", grppers.info" : "")."
				    FROM groupe, groupe_personnes AS grppers, personne_extractor AS personne
				    WHERE groupe.id = ".$vars["grpid"]."
				      AND grppers.groupid = groupe.id
				      AND grppers.personneid = personne.id
				      AND personne.fctorgid IS NULL
				   UNION ALL ".'
				    SELECT personne."'.implode('",personne."',$fields).'" '.($info ? ", grpfct.info" : "")."
				    FROM groupe, groupe_fonctions AS grpfct, personne_extractor AS personne
				    WHERE groupe.id = ".$vars["grpid"]."
				      AND grpfct.groupid = groupe.id
				      AND grpfct.fonctionid = personne.fctorgid)
				   ORDER BY nom, prenom";
			if ( $info ) $fields[] = "info";
		}
		else
		{
			$cond = array();
			if ( count($persid) > 0 )
				$cond[] = "p.id IN (".implode(',',$persid).") AND fctorgid IS NULL";
			if ( count($fctorgid) > 0 )
				$cond[] = "p.fctorgid IN (".implode(',',$fctorgid).")";
			if ( count($cond) > 0 )
				$cond = implode(' OR ',$cond);
		  $query  = ' SELECT "'.implode('","',$fields).'" FROM (
		              SELECT DISTINCT ON (p.nom, p.prenom, p.id, p.fctorgid)
		                     p.*,
                         telp.numero AS telnum, telp.type AS teltype,
                         telo.numero AS orgtelnum, telo.type AS orgteltype '.($info ? ", p.info" : "").'
                  FROM personne_properso p
                  LEFT JOIN telephone_personne telp ON p.id = telp.entiteid
                  LEFT JOIN telephone_organisme telo ON p.orgid = telo.entiteid
                  WHERE '.$cond.'
                  ORDER BY p.nom, p.prenom, p.id, p.fctorgid
                  ) AS personne_extractor';
		}
		
		$request = new bdRequest($bd,$query);
		//print_r($fields);
		//print_r($request->getAllRecords());
		$arr = $printfields ? array_merge( array($fields), $request->getAllRecords() ) : $request->getAllRecords();
    if ( is_array($arr) )
		foreach ( $arr as $line => $lcontent )
		{
			foreach ( $lcontent as $col => $value )
			{
				if ( $entonnoir && $value )		// si on préfère les données pro
				{
					switch ( $col ) {
					case "orgcp":
					case "orgville":
					case "orgpays":
					case "orgadr":
						if ( in_array($key = 'adresse',$fields) )
							$arr[$line][$key] = trim($lcontent["orgadr"]);
						if ( in_array($key = "cp",$fields) )
							$arr[$line][$key] = trim($lcontent["orgcp"]);
						if ( in_array($key = "ville",$fields) )
							$arr[$line][$key] = trim($lcontent["orgville"]);
						if ( in_array($key = "pays",$fields) )
							$arr[$line][$key] = trim($lcontent["orgpays"]);
						break;
					case "orgteltype":
						$arr[$line]["teltype"]  = trim($value);
            break;
					case "orgtelnum":
						$arr[$line]["telnum"]   = trim($value);
						break;
				  case 'orgemail':
					  $arr[$line][$col] = trim($value);
				  case 'proemail':
				    if ( in_array($key = 'email',$fields) && (trim($lcontent["proemail"]) || trim($lcontent["orgemail"])) )
				      $arr[$line][$key] = trim($lcontent["proemail"]) ? trim($lcontent["proemail"]) : trim($lcontent["orgemail"]);
				    break;
					default:
						$arr[$line][$col] = trim($value);
						break;
					}
				}
				elseif ( is_string($value) )		// par défaut...
					$arr[$line][$col] = trim($value);
			}
			
			// on retire les colonnes non nécessaires en cas de fusion pro/perso
			if ( $entonnoir )
			unset($arr[$line]["orgadr"],$arr[$line]["orgcp"],$arr[$line]["orgville"],$arr[$line]["orgpays"],$arr[$line]["orgtelnum"],$arr[$line]["orgteltype"],$arr[$line]["proemail"]);
		}
		
		// on retire les headers des colonnes non nécessaires en cas de fusion pro/perso
		if ( $entonnoir && $printfields && is_array($arr) )
		foreach ( $arr[0] as $key => $value )
			switch ( $value ) {
			case "orgadr":
			case "orgcp":
			case "orgville":
			case "orgpays":
			case "orgtelnum":
			case "orgteltype":
				unset($arr[0][$key]);
				break;
			}
		
	  if ( $labels )
	  {
      $params = array();
      $query  = " SELECT * FROM options WHERE key LIKE 'labels.%'";
      $request = new bdRequest($bd,$query);
      while ( $rec = $request->getRecordNext() )
        $params[substr($rec['key'],7)] = $rec['value'];
      $request->free();
      includePage('../gen/labels');
	  }
	  else
	  {
		  $csv = new csvExport($arr,$msexcel);
		  $csv->printHeaders($user->getLogin().'.'.date('YmdHis'));
		  echo $csv->createCSV();
		}
		$request->free();
		$bd->free();
	}
	else
	{
		$user->addAlert("Erreur lors de l'extraction, contactez votre administrateur.");
		$bd->free();
		$nav->redirect($_SERVER["HTTP_REFERER"]);
	}
?>
