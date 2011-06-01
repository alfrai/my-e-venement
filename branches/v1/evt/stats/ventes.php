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
  $class = 'stats date';
  $css[] = 'evt/styles/stats.css.php';
  includeJS('jquery');
  includeJS('stats','evt');
  
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="get">
  <h2>Activité de la billetterie</h2>
  <p>
    <input type="hidden" name="uri" value="" />
    <select name="period">
      <?php foreach ( array(
        'manifs' => 'Manifestations',
        'events' => 'Événements',
        'days'   => 'Jours',
        'weeks'  => 'Semaines',
        'month'  => 'Mois',
        'years'  => 'Années',
      ) as $key => $value ): ?>
      <option value="<?php echo $key ?>" <?php echo $key == ($_GET['period'] ? $_GET['period'] : 'weeks') ? 'selected="selected"' : '' ?>><?php echo $value ?></option>
      <?php endforeach; ?>
    </select>
    <span class="input">Date: <input type="text" name="from" value="<?php echo htmlsecure($_GET['from'] ? $_GET['from'] : '')?>" /></span>
    <input type="submit" name="submit" value="ok" />
  </p>
  <p class="tickets csv"><?php includeGraphe('tickets','evt/stats/graphes',"Suivi de l'activité de billetterie",true,array('period' => $_GET['period'], 'from' => $_GET['from'])); ?></p>
  <p class="tickets img"><?php includeGraphe('tickets','evt/stats/graphes',"Suivi de l'activité de billetterie",false,array('period' => $_GET['period'], 'from' => $_GET['from'])); ?></p>
</form>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
