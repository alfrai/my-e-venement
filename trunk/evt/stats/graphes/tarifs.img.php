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
	require("../conf.inc.php");
	$bd->free();
	
	if ( !function_exists('imageantialias') )
	{
	  function imageantialias($bool)
	  { return true; }
	}
	
  $data = unserialize($_GET['options']);
  $data[0]['type'] = 'Pie';
  
  // le graphe
  includeClass('Pie');
  $graph = new graph(450,400);
  
  includeClass('graphe');
  $graphe = new Graphe;
  $graphe->setData($data);
  $graphe->draw(array('width' => 450, 'height' => 400));
?>
