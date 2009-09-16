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
  require("conf.inc.php");
  includeLib('headers');
  
  if ( !$_GET['nom'] && intval(substr($_GET['client'],5)) <= 0 )
    die();
  
  if ( $_GET['nom'] )
    $where = " nom ILIKE '".pg_escape_string($_GET['nom'])."%' ";
  else
  {
    if ( substr($_GET['client'],0,4) == 'prof' )
         $where = ' fctorgid = ';
    else $where = ' id = ';
    $where .= intval(substr($_GET['client'],5));
  }
  $query = '  SELECT *
              FROM personne_properso
              WHERE '.$where.'
              ORDER BY nom, prenom, orgnom, fctdesc';
  $request = new bdRequest($bd,$query);
?>
<div class="list">
<ul>
<?php while ( $rec = $request->getRecordNext() ): ?>
  <li>
    <input type="hidden" name="id" value="<?php echo $rec['id'] ?>" />
    <input type="radio" name="client" value="<?php echo $rec['orgid'] ? 'prof_'.$rec['fctorgid'] : 'pers_'.$rec['id'] ?>" />
    <span>
      <a href="ann/fiche.php?id=<?php echo $rec['id'] ?>"><?php echo htmlspecialchars($rec['nom'].' '.$rec['prenom']) ?></a>
      <?php if ( $rec['orgid'] ): ?>
      (<a href="org/fiche.php?id=<?php echo $rec['orgid'] ?>"><?php echo htmlspecialchars($rec['orgnom']) ?></a><?php if ( $rec['fctdesc'] || $rec['fcttype'] ) echo htmlspecialchars(' - '.($rec['fctdesc'] ? $rec['fctdesc'] : $rec['fcttype'])) ?>)
      <?php endif; ?>
    </span>
  </li>
<?php endwhile; ?>
</ul>
</div>
<?php  
  $request->free();
  includeLib('footer');
?>
