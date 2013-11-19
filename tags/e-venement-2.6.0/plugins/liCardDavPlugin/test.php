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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  if ( !isset($_SERVER['argv'][1]) || !isset($_SERVER['argv'][2]) )
    throw new Exception('I need arguments');
  
  require_once 'lib/vendor/CardDAV-PHP/lib/carddav.php';
  require_once 'lib/vendor/vCard/lib/vCard.php';
  require_once 'lib/vendor/vCard/lib/vCardLI.php';
  require_once 'lib/carddav/liCardDavConnection.class.php';
  require_once 'lib/carddav/liCardDavConnectionZimbra.class.php';
  require_once 'lib/carddav/liCardDavVCard.class.php';
  require_once 'lib/exception/liCardDavException.class.php';
  
  $con = new liCardDavConnectionZimbra($_SERVER['argv'][1], array('username' => $_SERVER['argv'][2], 'password' => $_SERVER['argv'][3]));
  
  echo $con->checkConnection() ? 'Connected' : 'Not connected';
  echo "\n";
  
  /*
  $cpt = 0;
  $start = 0;
  foreach ( $ids = $con->getIdsList() as $id )
  {
    $cpt++;
    if ( $cpt < $start )
      continue;
    
    echo $con->getVCard($id, true);
    
    if ( $cpt > $start + 10 )
      break;
  }
  echo count($ids)." vCards\n";
  */
  
  echo "\n";
  $vcard = $con->getVCard('D4BCBCF1-1C6CB308-A57D1AAC');
  print_r($vcard);
  echo $vcard;
?>
