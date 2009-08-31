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
	includeLib("ttt");
	includeLib("actions");
	includeJS("ttt");
	includeJS("ajax");
	includeJS("annu");
	
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require('actions.php'); ?>
<div class="body">
<h2>Importer de données</h2>
<?php if ( $_FILES['import'] ): ?>
<div id="result">
  <p>Résultat de l'import :</p>
  <ul>
    <?php if ( is_array($cpt) ): foreach ( $cpt as $key => $value ): ?>
    <li class="<?php echo htmlsecure($key) ?>"><?php echo htmlsecure($value) ?> entrée(s)</li>
    <?php endforeach; else: ?>
    <li class="org"><?php echo htmlsecure($cpt) ?> entrée(s)</li>
    <?php endif; ?>
  </ul>
</div>
<?php else: ?>
<div class="model">
  <p class="format">Format CSV, encodage UTF-8, séparateur de champ ',' (virgule), séparateur de texte '"' (double gillemet)</p>
  <p class="fields"><span>Séquence des champs&nbsp;:</span> <span class="sys">"<?php echo implode('","',$fields) ?>"</span></p>
  <p class="warning"><?php echo $warning ?></p>
  <p class="exemple">
    Exemple (2 lignes)&nbsp;:
    <pre><?php echo $exemple ?></pre>
  </p>
</div>
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']) ?>" method="post" enctype="multipart/form-data">
  <p>Fichier à importer : <input type="file" name="import" /></p>
  <p><input type="submit" name="submit" value="upload" /></p>
</form>
<?php endif; ?>
</div>
<?php
	$bd->free();
	includeLib("footer");
?>
