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
	includeJS('jquery');
	$css[] = 'evt/styles/cat-tarifs.css';
	
	if ( !$config['ticket']['cat-tarifs'] )
	{
	  $bd->free();
	  $user->addAlert('Fonctionnalité non disponible.');
	  $nav->redirect(dirname($_SERVER['PHP_SELF']));
	}
	
	includeJS("ttt");
	includeLib("ttt");
	includeLib("actions");
	includeClass("bdRequest");
	
	$class = "cat-tarifs";
	$action = $actions["edit"];
	
  $query = 'SELECT DISTINCT key, description, contingeant, desact
            INTO TEMP tmp_tarifs
            FROM tarif
            WHERE date IN ( SELECT max(date) FROM tarif AS tmp WHERE tmp.key = tarif.key )
              AND NOT desact
            ORDER BY key';
	$tarifs = new bdRequest($bd,$query);
	$tarifs->free();
	
	includeLib("headers");
	require('cat-tarifs.inc.php');
  
  $query  = ' SELECT *
              FROM cattarifs_table t';
  $request = new bdRequest($bd,$query);
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<p class="actions"><a href="def/">Paramétrage</a><a class="nohref active">Cat. de tarifs</a><a href="." class="parent">..</a></p>
<div class="body">
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="post" id="groupes">
  <?php while ( $rec = $request->getRecordNext() ): ?>
  <div class="grp">Groupe:
    <table style="display: inline-table">
      <tr class="impair">
        <td class="impair"><input name="table[<?php echo $rec['id'] ?>][name]" value="<?php echo htmlsecure($rec['libelle']) ?>" /></td>
        <?php
          $rows = new bdRequest($bd,'SELECT * FROM cattarifs_row ORDER BY id');
          for ( $c = 0 ; $row = $rows->getRecordNext() ; $c++ ):
        ?>
        <td class="<?php echo $c % 2 == 0 ? 'pair' : 'impair' ?>"><input name="table[<?php echo $rec['id'] ?>][rows][<?php echo htmlsecure($row['id']) ?>]" value="<?php echo htmlsecure($row['libelle']) ?>" /></td>
        <?php endfor; ?>
        <td class="<?php echo $c % 2 == 0 ? 'pair' : 'impair' ?>"><input name="table[<?php echo $rec['id'] ?>][rows][new]" value="" /></td>
      </tr>
      <?php
        $lines = new bdRequest($bd,'SELECT * FROM cattarifs_line ORDER BY id');
        for ( $l = 0 ; $line = $lines->getRecordNext() ; $l++ ):
      ?>
      <tr class="<?php echo $l % 2 == 0 ? 'pair' : 'impair' ?>">
        <td class="impair"><input name="table[<?php echo $rec['id'] ?>][lines][<?php echo htmlsecure($line['id']) ?>]" value="<?php echo htmlsecure($line['libelle']) ?>" /></td>
        <?php
          $rows->firstRecord();
          for ( $c = 0 ; $row = $rows->getRecordNext() ; $c++ ):
        ?>
        <td class="<?php echo $c % 2 == 0 ? 'pair' : 'impair' ?>"><select name="table[<?php echo $rec['id'] ?>][tarifs][<?php echo $line['id'] ?>][<?php echo $row['id'] ?>]">
          <option value="">-tarifs-</option>
          <?php
            $tarifs = new bdRequest($bd,'
              SELECT *,
                    (SELECT tarifkey = t.key
                     FROM cattarifs_elt
                     WHERE lineid = '.$line['id'].'
                       AND rowid  = '.$row['id'].') AS selected,
                    key IN ( SELECT tarifkey
                             FROM cattarifs_line l, cattarifs_row r, cattarifs_elt e
                             WHERE e.lineid = l.id
                               AND e.rowid = r.id
                               AND (l.tableid = '.$rec['id'].' OR r.tableid = '.$rec['id'].') ) AS used
              FROM tmp_tarifs t
              ORDER BY selected DESC, used, key
            ');
            while ( $tarif = $tarifs->getRecordNext() ):
          ?>
          <option
            value="<?php echo htmlsecure($tarif['key']) ?>"
            <?php if ( $tarif['selected'] == 't' ) echo 'selected="selected"' ?>
            <?php if ( $tarif['used']     == 't' && $tarif['selected'] != 't' ) echo 'class="used"' ?>>
            <?php echo htmlsecure($tarif['key'].': '.$tarif['description']) ?>
          </option>
          <?php
            endwhile;
            $tarifs->free();
          ?>
        </select></td>
        <?php endfor; ?>
        <td class="<?php echo $c % 2 == 0 ? 'pair' : 'impair' ?>"></td>
      </tr>
      <?php endfor; ?>
      <tr class="<?php echo $l % 2 == 0 ? 'pair' : 'impair' ?>">
        <td class="<?php echo $c % 2 == 0 ? 'pair' : 'impair' ?>"><input name="table[<?php echo $rec['id'] ?>][lines][new]" value="" /></td>
        <?php for ( $cpt = 0 ; $cpt <= $c ; $cpt++ ): ?>
        <td class="<?php echo ($c+$cpt) % 2 != 0 ? 'pair' : 'impair' ?>"></td>
        <?php endfor; ?>
      </tr>
    </table>
    <?php
      $lines->free();
      $rows->free();
    ?>
  </div>
  <p class="submit"><input type="submit" name="submit" value="Valider" title="Valide l'ensemble des tables"/></p>
  <?php endwhile; ?>
  <div class="grp">Groupe:
    <table style="display: inline-table">
      <tr>
        <td><input name="table[new][name]" value="" /></td>
      </tr>
    </table>
  </div>
  <p class="submit"><input type="submit" name="submit" value="Ajouter" title="Valide l'ensemble des tables"/></p>
</form>
</div>
<?php
	includeLib("footer");
  $request->free();
  $bd->free();
?>

