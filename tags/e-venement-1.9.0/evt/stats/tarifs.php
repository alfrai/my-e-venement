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
  $class = 'stats';
  $css[] = 'evt/styles/stats.css.php';
  //includeJS('jquery');
  //includeJS('stats','evt');
  
  $from = strtotime($_GET['from']) ? strtotime($_GET['from']) : strtotime('1 year ago + 1 day');
  $to   = strtotime($_GET['to']) ? strtotime($_GET['to']) : strtotime('now');
  $evts = is_array($_GET['evt']) ? $_GET['evt'] : array();
  
  $tables = array();
  $where  = array(); 
  
  // filtre sur evenements
  if ( is_array($evts) )
  if ( count($evts) > 0 && intval($evts[0]) > 0 )
  {
    $tables[] = 'evenement e';
    $tables[] = 'manifestation m';
    $where[]  = 'e.id IN ('.implode(',',$evts).')';
    $where[]  = 'e.id = m.evtid';
    $where[]  = 'p.manifid = m.id';
  }
  $tables[] = 'tarif t';
  $tables[] = 'reservation_pre p';
  $where[]  = 'NOT annul';
  $where[]  = 'NOT contingeant';
  $where[]  = 't.id = p.tarifid';
  $where[]  = "p.date >= '".date('Y-m-d',$from)."'::date";
  $where[]  = "p.date <  '".date('Y-m-d',strtotime('+1 day',$to))  ."'::date";
  $query  = ' SELECT  t.key AS tarif,
                      sum((p.id IS NOT NULL AND ps.id IS NULL AND c.id IS NULL)::integer) AS demanded,
                      sum((ps.id IS NOT NULL AND c.id IS NULL)::integer) AS preselled,
                      sum((c.id IS NOT NULL)::integer) AS printed,
                      count(t.key) AS total
              FROM '.implode(',',$tables).'
              LEFT JOIN reservation_cur c ON c.resa_preid = p.id AND NOT canceled
              LEFT JOIN preselled ps ON ps.transaction = p.transaction
              WHERE '.implode(' AND ',$where).'
              GROUP BY t.key, t.contingeant
              ORDER BY t.key';
  $request = new bdRequest($bd,$query);
  
  // extraction CSV
  if ( isset($_GET['csv']) )
  {
    $arr = array();
    
    $arr[] = array( date('d/m/Y',$from).' -> '.date('d/m/Y',$to) );
    $arr[] = array('');
    
    $i = count($arr);
    $arr[$i][] = 'Tarif';
    $arr[$i][] = 'Demandes';
    $arr[$i][] = 'Pré-résas';
    $arr[$i][] = 'Réservations';
    $arr[$i][] = 'Total';
    
    for ( ++$i ; $rec = $request->getRecordNext() ; $i++ )
    {
      $arr[$i][] = $rec['tarif'];
      $arr[$i][] = $rec['demanded'];
      $arr[$i][] = $rec['preselled'];
      $arr[$i][] = $rec['printed'];
      $arr[$i][] = $rec['total'];
    }
    
    includeClass('csvExport');
    $csv = new csvExport($arr);
    $csv->printHeaders('tarifs-'.date('Y-m-d').'-'.date('Y-m-d',$from).'-'.date('Y-m-d',$to));
    echo $csv->createCSV();
    
    $request->free();
    beta_die();
  }
    
	includeLib("headers");
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="get">
  <h2>Ventes par tarif</h2>
  <p>
    <select name="evt[]" multiple="multiple">
    <?php
      $events = new bdRequest($bd,' SELECT id, nom FROM evenement ORDER BY nom');
      echo '<option value=""></option>';
      while ( $event = $events->getRecordNext() )
        echo '<option value="'.intval($event['id']).'" '.(in_array($event['id'],$evts) ? 'selected="selected"' : '').'>'.htmlsecure($event['nom']).'</option>';
      $events->free();
    ?>
    </select>
    <span class="input">du: <input type="text" name="from" value="<?php echo htmlsecure(date('Y-m-d',$from)) ?>" /></span>
    <span class="input">au: <input type="text" name="to"   value="<?php echo htmlsecure(date('Y-m-d',$to  )) ?>" /></span>
    <input type="submit" name="submit" value="ok" />
  </p>
  <p class="tarifs csv"><a href="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>?csv">Extraction des ventes</a> (CSV)</p>
<?php
  $data = array();
  $data['period']  = 'du '.date('d/m/Y',$from).' au '.date('d/m/Y',$to);
  $data['xlabels'] = array();
  $data[0] = array('legend'    => 'Répartition des tarifs (général)');
  $data[1] = array('legend'    => 'Répartition des tarifs réservés');
  $data[2] = array('legend'    => 'Répartition des tarifs pré-réservés');
  $data[3] = array('legend'    => 'Répartition des tarifs demandés');
  for ( $i = 1 ; $rec = $request->getRecordNext() ; $i++ )
  {
      $data['xlabels'][]    = $rec['tarif'];
      $data[0]['values'][]  = $rec['total'];
      $data[1]['values'][]  = $rec['printed'];
      $data[2]['values'][]  = $rec['preselled'];
      $data[3]['values'][]  = $rec['demanded'];
  }
  $request->free();
?>
  <p class="tarifs img">
<?php  
  for ( $i = 0 ; $i < 4 ; $i++ )
  if ( count($data[$i]['values']) > 0 )
  {
    $pie = array();
    $pie['period']  = $data[$i]['legend'].' ('.$data['period'].')';
    $pie['xlabels'] = $data['xlabels'];
    $pie[0] = $data[$i];
    includeGraphe('tarifs','evt/stats/graphes',$pie['period'],false,$pie);
  }
?>
  </p>
</form>
</div>
<?php
	includeLib("footer");
	$bd->free();
?>
