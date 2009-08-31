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
*    Copyright (c) 2006-2009 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
  require("conf.inc.php");
  includeClass('bdRequest');
  
  $type = 'ann';
  
  $exemple = '
  "M.","BRASENS","Georges","13 rue du Sud","22000","ST BRIEUC","France","georges@brassens.fr","02 96 52 48 89","","","","","",""
  "M.","STALLMAN","Richard","2 chemin du levant","64000","PAU","France","stallman@fsf.org","05 25 68 37 12","","Free Software Foundation","Direction","Directeur","05 84 67 32 33","stallman@fsf.org"';
  
  $fields = array(
	  'titre',
	  'nom',
	  'prenom',
	  'adresse',
	  'cp',
	  'ville',
	  'pays',
	  'email',
	  'telephone1',
	  'telephone2',
	  'organisme_nom',
	  'service',
	  'fctdesc',
	  'protel',
	  'proemail',
  );
  
  $warning = "
    Le nom de l'organisme servira de repère pour l'organisme auquel relier le contact.
    Le dernier organisme ajouté à ce nom servira de référence en cas d'homonymies.
    Si aucun n'organisme n'existe à ce nom, toutes les informations professionnelles seront perdues.";
  
	includeClass("bdRequest");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	// IMPORTING DATA
	if ( $fp = fopen($_FILES['import']['tmp_name'],'r') )
	for( $cpt = array('pro' => 0, 'pers' => 0) ; $line = fgetcsv($fp) ; )
	if ( is_array($line) && count($line) >= 10 )
	{
    $tel = $pro = $pers = array();
    if ( is_array($line) )
    foreach( $line as $key => $field )
    {
      if ( $key < 8 )
 	      $pers[$fields[$key]] = $field;
      elseif ( $key < 10 )
 	      $tel[]['numero'] = $field;
 	    else
 	      $pro[$fields[$key]]  = $field;
    }
    
    // personne
    if ( $bd->addRecord('personne',$pers) !== false )
    {
      $cpt['pers']++;
      $pro['personneid'] = $bd->getLastSerial('entite','id');
      
      // telephone
      foreach ( $tel as $value )
      if ( $value['numero'] )
      {
        $value['entiteid'] = $pro['personneid'];
        $bd->addRecord('telephone_personne',$value);
      }
      
      // org_personne
      if ( $pro['organisme_nom'] && $pro['personneid'] > 0 )
      {
        $request = new bdRequest($bd,"SELECT id FROM organisme WHERE nom ILIKE '".$pro['organisme_nom']."' ORDER BY id DESC LIMIT 1");
        unset($pro['organisme_nom']);
        $pro['organismeid'] = intval($request->getRecord('id'));
        $request->free();
        
        if ( $pro['organismeid'] > 0 )
        {
          $pro['telephone'] = $pro['protel'];
          unset($pro['protel']);
          $pro['email'] = $pro['proemail'];
          unset($pro['proemail']);
          $pro['fonction'] = $pro['fctdesc'];
          unset($pro['fctdesc']);
          
          $cpt['pro'] += $bd->addRecord('org_personne',$pro) !== false ? 1 : 0;
        }
      }
    }
  }
	if ( $fp ) fclose($fp);
  
  require('../gen/import.php');
?>
