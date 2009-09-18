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
  $tables = $_POST['table'];
  
  if ( !$config['ticket']['cat-tarifs'] )
    die(1);
  
  // ajouter un groupe
  if ( $name = $tables['new']['name'] )
    $bd->addRecord('cattarifs_table',array('libelle' => $name));
  unset($tables['new']);
  
  if ( is_array($tables) )
  foreach ( $tables as $key => $table )
  {
    $key = intval($key);
    
    // mises à jour de noms de groupes
    if ( $table['name'] )
    $bd->updateRecordsSimple('cattarifs_table',array('id' => $key),array('libelle' => $table['name']));
    
    // ajout d'une colonne
    if ( $name = $table['rows']['new'] )
      $bd->addRecord('cattarifs_row',array('libelle' => $name, 'tableid' => $key));
    unset($table['rows']['new']);
    
    // updates des noms de colonnes
    foreach ( $table['rows'] as $rowid => $name )
      $bd->updateRecordsSimple('cattarifs_row',array('id' => intval($rowid)),array('libelle' => $name));
    
    // ajout d'une ligne
    if ( $name = $table['lines']['new'] )
      $bd->addRecord('cattarifs_line',array('libelle' => $name, 'tableid' => $key));
    unset($table['lines']['new']);
    
    // updates des noms de lignes
    foreach ( $table['lines'] as $lineid => $name )
      $bd->updateRecordsSimple('cattarifs_line',array('id' => intval($lineid)),array('libelle' => $name));
    
    // ajout d'un tarif
    if ( is_array($tarifs = $table['tarifs']) )
    foreach ( $tarifs as $lineid => $rows )
    if ( is_array($rows) )
    foreach ( $rows as $rowid => $tarifkey )
    {
      $bd->beginTransaction();
      $bd->delRecordsSimple('cattarifs_elt',array('lineid' => intval($lineid), 'rowid' => intval($rowid)));
      if ( $tarifkey )
      $bd->addRecord ('cattarifs_elt',array('lineid' => intval($lineid), 'rowid' => intval($rowid), 'tarifkey' => $tarifkey));
      $bd->endTransaction();
    }
  }
?>
