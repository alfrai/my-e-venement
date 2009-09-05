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
  
  $type = 'org';
  
  $fields = array(
    'categorie',
	  'nom',
	  'adresse',
	  'cp',
	  'ville',
	  'pays',
	  'email',
	  'url',
	  'telephone1',
	  'telephone2',
	  '...',
  );
  
  $exemple = '
  "Collectivité","Mairie de Bourboul les Bains","place de la Résistance","14220","Bourboul les Bains","France","contact@mairie-bourboul.fr","http://www.mairie-bourboul.fr/","02 58 10 10 10","02 58 10 10 01",...
  "Compagnie","La Hurlante","25 chemin de la coline","76000","ROUEN","France","cie-la-hurlante@free.fr","","02 45 85 62 35","","",...';
  
  $warning = "Attention: le nom de la catégorie est recherché dans la base pour trouver une correspondance... essayez de respecter l'accentuation, au risque de créer des catégories en doublon.";
  
	includeClass("bdRequest");
	
	$bd	= new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
	
	// IMPORTING DATA
	if ( $fp = fopen($_FILES['import']['tmp_name'],'r') )
	for( $cpt = 0 ; $line = fgetcsv($fp) ; )
	if ( is_array($line) && count($line) >= 7 )
	{
    $tel = $org = array();
    if ( is_array($line) )
    foreach( $line as $key => $field )
    {
      if ( $key == 0 )  // la catégorie
      {
        $cat = new bdRequest($bd,"SELECT id FROM org_categorie WHERE libelle ILIKE '".$field."'");
        if ( $cat->countRecords() > 0 )
          $catid = intval($cat->getRecord('id'));
        else
        {
          $bd->addRecord('org_categorie',array('libelle' => $field));
          $catid = $bd->getLastSerial('org_categorie','id');
        }
        if ( $catid > 0 )
          $org[$fields[$key]] = $catid;
      }
      elseif ( $key < 8 )
 	      $org[$fields[$key]] = $field;
 	    else
 	      $tel[]['numero'] = $field;
    }
    
    // organisme
    if ( $bd->addRecord('organisme',$org) !== false )
    {
      $cpt++;
      $orgid = $bd->getLastSerial('entite','id');
      
      // telephone
      foreach ( $tel as $value )
      if ( $value['numero'] )
      {
        $value['entiteid'] = $orgid;
        $bd->addRecord('telephone_organisme',$value);
      }
    }
  }
	if ( $fp ) fclose($fp);
  
  require('../gen/import.php');
?>
