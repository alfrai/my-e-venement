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
  
  $type = $_GET['type'] ? $_GET['type'] : 'evt';
  
  $warning = "<p>Vous n'importerez que les informations principales des évènements traités, pour les détails, éditez leur fiche après l'import.</p>".'
    <form action="'.htmlsecure($_SERVER['PHP_SELF']).'" method="get" name="type">
      <p>Choisissez le type de données à importer: <select name="type">
        <option '.($type == 'evt'   ? 'selected="selected"' : '').' value="evt">Évènement</option>
        <option '.($type == 'salle' ? 'selected="selected"' : '').' value="salle">Salle</option>
        <option '.($type == 'manif' ? 'selected="selected"' : '').' value="manif">Manifestation</option>
      </select><input type="submit" name="submit" value="ok" /></p>
    </form>';
  
  $fields = array();
  
  switch ( $type ) {
  case 'evt':
    $exemple = '
      "Le Grand Cirque d\'Abdelaziz Nafeh","Le Grand Cirque","Cirque pour jeune public","01:00"
      "La petite roue","","Théâtre","02:00"';
    
    $fields = array(
      'nom',
      'petitnom',
      'typedesc',
      'duree',
    );
    
    // IMPORTING DATA
    if ( $fp = fopen($_FILES['import']['tmp_name'],'r') )
    for( $cpt = 0 ; $line = fgetcsv($fp) ; )
    if ( is_array($line) && count($line) == 4 )
  	{
 	    $evt = array();
  	  
      if ( is_array($line) )
      foreach( $line as $key => $field )
 	      $evt[$fields[$key]] = $field;
      
      if ( $bd->addRecord('billeterie.evenement',$evt) !== false )
        $cpt++;
    }
    break;
  case 'salle':
    $exemple = '
      "MJC du Moulin Vert","10 allée des peupliers","33000","BORDEAUX","France"
      "Théâtre des Oripeaux","Place de la Mairie","17000","LA ROCHELLE","France"';
    
    $fields = array(
      'nom',
      'adresse',
      'cp',
      'ville',
      'pays',
    );
    
    // IMPORTING DATA
    if ( $fp = fopen($_FILES['import']['tmp_name'],'r') )
    for( $cpt = 0 ; $line = fgetcsv($fp) ; )
    if ( is_array($line) && count($line) == 5 )
  	{
 	    $site = array();
  	  
      if ( is_array($line) )
      foreach( $line as $key => $field )
 	      $site[$fields[$key]] = $field;
      
      if ( $bd->addRecord('billeterie.site',$site) !== false )
        $cpt++;
    }
    break;
  case 'manif':
    $exemple = '
      "Le Grand Cirque d\'Abdelaziz Nafeh","Théâtre des Oripeaux","2010-10-05 20:50","1:00","Séance T.P.","150","2.10"
      "Le Grand Cirque d\'Abdelaziz Nafeh","Théâtre des Oripeaux","2010-10-07 20:30","1:10","Séance T.P.","130","2.10"';
    
    $fields = array(
      'evenement_nom',
      'site_nom',
      'date',
      'duree',
      'description',
      'jauge',
      'txtva',
    );
    
    // IMPORTING DATA
    if ( $fp = fopen($_FILES['import']['tmp_name'],'r') )
    for( $cpt = 0 ; $line = fgetcsv($fp) ; )
    if ( is_array($line) && count($line) == 7 )
  	{
 	    $manif = array();
  	  
      if ( is_array($line) )
      foreach( $line as $key => $field )
 	      $manif[$fields[$key]] = $field;
      
      $query   = " SELECT
        ( SELECT id FROM evenement WHERE nom ILIKE '".pg_escape_string($manif['evenement_nom'])."'
        ) AS evtid,
        ( SELECT id FROM site      WHERE nom ILIKE '".pg_escape_string($manif['site_nom'])."'
        ) AS siteid
      ";
      $request = new bdRequest($bd,$query);
      $infos = $request->getRecord();
      $request->free();
      
      unset($manif['site_nom']);
      unset($manif['evenement_nom']);
      $manif['evtid'] = $infos['evtid'];
      $manif['siteid'] = $infos['siteid'];
      
      if ( $bd->addRecord('billeterie.manifestation',$manif) !== false )
        $cpt++;
    }
    break;  
  }
  if ( $fp ) fclose($fp);
  
  require('../../gen/import.php');
?>
