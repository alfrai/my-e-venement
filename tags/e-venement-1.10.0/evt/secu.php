<?php
/**********************************************************************************
*
*               This file is part of e-venement.
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
	global $user,$bd,$config,$nav;
	require_once(dirname(__FILE__).'/../config.php');
	require_once(dirname(__FILE__).'/config.php');
	includeClass("bdRequest");
	includeClass("bd");
	
	// module inactif
	if ( !in_array("evt",$config["mods"]) )
	{
		$user->addAlert("Module inexistant");
		$nav->redirect($config["website"]["base"],"module désactivé");
	}
	
	if ( !is_object($bd) )
	{
		$bd = new bd (	$config["database"]["name"],
				$config["database"]["server"],
				$config["database"]["port"],
				$config["database"]["user"],
				$config["database"]["passwd"] );
		$bd->setPath("billeterie,public");
	}
	
	// space used for browsing this module
	if ( !isset($user->evtspace) && $_SERVER['PHP_SELF'] != $config["website"]["root"].$config['evt']['spacesurl'] )
	{
	  $query  = ' SELECT spaceid
	              FROM billeterie.rights
	              WHERE level > 0
	                AND id = '.$user->getId();
	  $request = new bdRequest($bd,$query);
	  $nbspaces = $request->countRecords();
	  
	  if ( $nbspaces > 1 )
	  {
	    $user->last_url = $_SERVER['PHP_SELF'];
  	  $nav->redirect($config["website"]["base"].$config['evt']['spacesurl']);
  	}
  	else
  	  $user->evtspace = intval($request->getRecord('id'));
	  $request->free();
	}
	
	// setting user's permissions for this module
	if ( !isset($user->evtlevel) )
	{
		$query	= " SELECT level FROM billeterie.rights WHERE id = ".$user->getId();
		$query .= $user->evtspace > 0 ? ' AND spaceid = '.$user->evtspace : ' AND spaceid IS NULL';
		$request = new bdRequest($bd,$query);
		$user->evtlevel = intval($request->getRecord("level"));
		$request->free();
	}
	
	// blocking unappropriate browsing
	if ( $user->evtlevel < $config["evt"]["right"]["view"] && !$user->hasRight($config["right"]["param"]) && !headers_sent() )
	{
		$user->addAlert($msg = "Vous n'avez pas le droit de visionner cette page");
		$nav->redirect($config["website"]["base"],$msg);
	}
?>
