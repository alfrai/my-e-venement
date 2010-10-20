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
  $class = 'stats interval';
  $css[] = 'evt/styles/stats.css.php';
  includeJS('jquery');
  includeJS('stats','evt');
  
  $date = array(
    'start' => strtotime($_GET["start"]) ? strtotime($_GET["start"]) : strtotime('now'),
    'stop'  => strtotime($_GET["stop"]) ? strtotime($_GET["stop"]) : strtotime('+1 month'),
  );
  
  $options = array(
    'start' => date('Y-m-d',$date['start']),
    'stop'  => date('Y-m-d',$date['stop']),
    'period'=> $_GET['period'],
  );
  
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="get">
  <h2>État des jauges</h2>
  <p>
    <input type="hidden" name="uri" value="<?php includeGraphe('jauges','evt/stats/graphes') ?>" />
    <select name="period">
      <option value="manifs" <?php echo $_GET['period'] == 'manifs' ? 'selected="selected"' : '' ?>>Manifestations</option>
      <option value="events" <?php echo $_GET['period'] == 'events' ? 'selected="selected"' : '' ?>>Événements</option>
    </select>
    <span class="input">du: <input type="text" name="start" value="<?php echo date('Y-m-d',$date['start']) ?>" /></span>
    <span class="input">au: <input type="text" name="stop"  value="<?php echo date('Y-m-d',$date['stop']) ?>" /></span>
    <input type="submit" name="submit" value="ok" />
  </p>
  <p class="tickets csv"><?php includeGraphe('jauges','evt/stats/graphes',"État des jauges sur la période",true,$options); ?></p>
  <p class="tickets img"><?php includeGraphe('jauges','evt/stats/graphes',"État des jauges sur la période",false,$options); ?></p>
</form>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
