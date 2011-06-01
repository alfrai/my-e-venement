<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    beta-libs is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with beta-libs; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006 Baptiste SIMON <baptiste.simon AT e-glop.net>
*
***********************************************************************************/
?>
<?php
	require("./conf.inc.php");
	
	// a space has been chosen, redirecting
	if ( isset($_GET['spaceid']) || !$config['evt']['spaces'] )
	{
	  // set the chosen space
	  $user->evtspace = intval($_GET['spaceid']);
	  
	  // set the last_url
	  $last_url = $user->last_url;
	  unset($user->last_url);
	  
	  // reset the evtlevel
	  $query  = 'SELECT level FROM billeterie.rights WHERE id = '.$user->getId();
	  $query .= $user->evtspace > 0 ? ' AND spaceid = '.$user->evtspace : ' AND spaceid IS NULL';
	  $request = new bdRequest($bd,$query);
	  $user->evtlevel = intval($request->getRecord('level'));
	  $request->free();
  	
	  // redirect
	  if ( $last_url )
	  $nav->redirect($last_url);
	}
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions">
	<a class="active nohref">Espaces</a><?php includePage("action.index"); if ( $user->evtlevel >= $config["evt"]["right"]["param"] ) { ?><a href="evt/def/" class="add">Paramétrage</a><?php } ?><a href="." class="parent">..</a>
</p>
<div class="body">
<h2>Les évènements</h2>
<?php if ( $config['evt']['spaces'] ): ?>
<h3>Sélection d'un espace de travail</h3>
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="get">
  <p>
    <?php
      $query  = ' SELECT *
                  FROM space
                  WHERE id IN ( SELECT spaceid FROM rights WHERE id = '.$user->getId().' )
                  ORDER BY name';
      $request = new bdRequest($bd,$query);
    ?>
    <select name="spaceid" onchange="javascript: submit()">
      <option value="0">Espace par défaut</option>
      <?php while ( $rec = $request->getRecordNext() ): ?>
      <option value="<?php echo intval($rec['id']) ?>" <?php echo $user->evtspace == intval($rec['id']) ? 'selected="selected"' : '' ?>><?php echo htmlsecure($rec['name']) ?></option>
      <?php endwhile; ?>
    </select>
    <?php $request->free(); ?>
    <input type="submit" name="ok" value="ok" />
  </p>
</form>
<?php endif; ?>
<?php @include("desc.txt"); ?>
</div>
<?php
	includeLib("footer");
?>
