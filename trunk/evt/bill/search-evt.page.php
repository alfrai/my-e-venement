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
  
  if ( isset($_GET['nom']) )
  {
    $where = " evt.nom ILIKE '".pg_escape_string($_GET['nom'])."%'";
    $limit = NULL;
    $order = '(SELECT date FROM manifestation WHERE evtid = evt.id ORDER BY date LIMIT 1), nom, evtid, date, ville';
  }
  elseif ( is_array($_GET['manifid']) )
  {
    $manifs = array();
    foreach ( $_GET['manifid'] as $value )
      $manifs[] = intval($value);
    $where = ' manif.id IN ( '.implode(',',$manifs).' )';
    $order = 'date, nom, ville';
  }
  else
  {
    $where = '';
    $limit = '5';
    $order = '(SELECT date FROM manifestation WHERE evtid = evt.id ORDER BY date LIMIT 1), nom, evtid, date, ville';
  }
  
  if ( is_array($_GET['exclude']) )
    $excludes = ' AND manif.id NOT IN ('.implode(',',$_GET['exclude']).') ';
  
  $where = $where ? ' AND '.$where : '';
  $query = '  SELECT  evt.nom, evt.id AS evtid, manif.date, manif.id, colors.color, manif.description,
                      site.id AS siteid, site.nom AS sitenom, site.ville, site.cp, site.pays
              FROM evenement AS evt, manifestation AS manif, colors, site 
              WHERE '.(!is_array($_GET['manifid']) ? "manif.date > NOW() - '1 DAY'::interval AND " : '').'
                    manif.evtid = evt.id
                AND (colors.id = manif.colorid OR colors.id IS NULL AND manif.colorid IS NULL)
                AND site.id = manif.siteid
                '.$where.'
                '.$excludes.'
              ORDER BY '.$order;
  if ( $limit ) $query .= ' LIMIT '.intval($limit);
  $request = new bdRequest($bd,$query);
  
  $evtid = 0;
?>
<div class="list">
<ul>
<?php while ( $rec = $request->getRecord() ): ?>
  <?php if ( $new = ($rec['evtid'] != $evtid) ): ?>
  <li>
    <?php $evtid = $rec['evtid'] ?>
    <a href="evt/infos/fiche.php?id=<?php echo htmlspecialchars($rec['evtid']) ?>"><?php echo htmlspecialchars($rec['nom']) ?></a>
    <ul>
  <?php endif; ?>
      <li class="evt">
        <input type="radio" name="manifs[]" value="<?php echo intval($rec['id']) ?>" />
        <span style="background-color: #<?php echo $rec['color'] ?>">
          le <a href="evt/infos/manif.php?id=<?php echo $rec['id'] ?>"><?php echo htmlspecialchars(date('d/m/Y à H:i',strtotime($rec['date']))) ?></a>
          à  <a href="evt/infos/salle.php?id=<?php echo $rec['siteid'] ?>"><?php echo htmlspecialchars($rec['sitenom'].' - '.$rec['ville'].', '.substr($rec['cp'],0,2)) ?></a>
        </span>
        <?php
          $tarifs = new bdRequest($bd,'
            SELECT key, prix, prixspec
            FROM tarif_manif
            WHERE manifid = '.$rec['id'].'
              AND NOT desact
              AND NOT contingeant
            ORDER BY key');
          while ( $tarif = $tarifs->getRecordNext() ):
        ?>
        <input type="hidden" class="prix" name="<?php echo $tarif['key'] ?>" value="<?php echo $tarif['prixspec'] ? $tarif['prixspec'] : $tarif['prix'] ?>" />
        <?php
          endwhile;
          $tarifs->free();
        ?>
        <p class="jauge">Calcul en cours...</p>
        <span class="total">0</span>
      </li>
  <?php if ( !($rec = $request->getNextRecord()) || $rec['evtid'] != $evtid ): ?>
    </ul>
  </li>
  <?php endif; ?>
<?php endwhile; ?>
</ul>
</div>
<?php  
  $request->free();
  includeLib('footer');
?>
