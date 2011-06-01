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
	
	includeClass("bd");
	includeClass("bdRequest");
	
	$jauge = true;
	
	if ( isset($_GET['options']) )
	$_GET = unserialize($_GET['options']);
	
	$date["start"] = strtotime($_GET["start"]) ? strtotime($_GET["start"]) : strtotime('now');
	$date["stop"]	 = strtotime($_GET["stop"]) ? strtotime($_GET["stop"]) : strtotime('+1 month');
	$period = in_array($_GET['period'],array('manifs','events')) ? $_GET['period'] : 'manifs';
	
	$select = $from = $leftj = $where = $group = $order = array();
	
	$from[]   = 'evenement e';
	$from[]   = 'manifestation m';
	
	$leftj[]   = 'reservation_pre p ON m.id = p.manifid';
	$leftj[]  = 'masstickets mt ON mt.transaction = p.transaction AND p.manifid = mt.manifid';
	$leftj[]  = 'bdc ON bdc.transaction = p.transaction';
	$leftj[]  = 'contingeant cont ON cont.transaction = p.transaction';
	$leftj[]  = 'reservation_cur c ON c.resa_preid = p.id AND NOT canceled';
	
	$select[] = 'e.id AS evtid';
	$select[] = 'e.nom';
	if ( $period == 'manifs' )
	{
	  $select[] = 'm.id AS manifid';
	  $select[] = 'm.date';
	  $select[] = 'm.jauge';
	}
	else
	{
	  $select[] = '(SELECT sum(jauge) FROM manifestation WHERE manifestation.evtid = e.id) AS jauge';
	}
	$select[] = 'sum((c.id IS NOT NULL AND mt.id IS NULL AND NOT annul AND NOT canceled)::integer)-sum((c.id IS NOT NULL AND mt.id IS NULL AND annul AND NOT canceled)::integer) AS intern_printed';
	$select[] = 'sum((c.id IS NULL AND cont.id IS NOT NULL AND mt.id IS NULL)::integer) AS intern_contingents';
	$select[] = 'sum((c.id IS NULL AND bdc.id IS NOT NULL AND mt.id IS NULL)::integer) AS intern_bdc';
	$select[] = 'sum((c.id IS NOT NULL AND mt.id IS NOT NULL)::integer) AS part_printed';
	if ( $period == 'manifs' )
	  $select[] = '(SELECT sum(nb) FROM masstickets WHERE manifid = m.id) AS part_total';
	else
	  $select[] = '(SELECT sum(nb) FROM masstickets, manifestation WHERE manifid = manifestation.id AND manifestation.evtid = e.id) AS part_total';
  
  $where[]  = 'm.evtid = e.id';
  $where[]  = "m.date >= '".date('Y-m-d',$date['start'])."'::date";
  $where[]  = "m.date <  '".date('Y-m-d',strtotime('+1 day',$date['stop']))."'::date";
  
  $groupby[]= 'e.id';
  $groupby[]= 'e.nom';
  if ( $period == 'manifs' )
  {
    $groupby[]= 'm.id';
    $groupby[]= 'm.date';
    $groupby[]= 'm.jauge';
  }
  else
    $groupby[]= 'jauge';
  
  if ( $period == 'manifs' )
    $orderby[]= 'm.date';
  $orderby[]= 'e.nom';
  $orderby[]= 'm.jauge';
  
	$query = ' SELECT '.implode(', ',$select).'
	           FROM '.implode(', ',$from).'
	           LEFT JOIN '.implode(' LEFT JOIN ',$leftj).'
	           WHERE '.implode(' AND ',$where).'
	           GROUP BY '.implode(', ',$groupby).'
	           ORDER BY '.implode(', ',$orderby);
  $request = new bdRequest($bd,$query);
  
	$data = array('xlabels' => array());
  while ( $rec = $request->getRecordNext() )
  {
    $jauge = intval($rec['jauge']);
    $rec['part_reste']   = intval($rec['part_total']) - intval($rec['part_printed']);
    $rec['reste'] = $jauge
      - intval($rec['intern_printed'])
      - intval($rec['intern_bdc'])
      - intval($rec['intern_contingents'])
      - intval($rec['part_total']);
    
    if ( $rec['part_reste'] < 0 )
      $rec['part_reste'] = 0;
    
    $total = $rec['reste'] < 0 ? $jauge - $rec['reste'] : $jauge;
    $tmp = array();
    
    $tmp['reste']             = intval($rec['reste'])*100/$total;
    $tmp['intern']['printed'] = intval($rec['intern_printed'])*100/$total;
    $tmp['intern']['bdc']     = intval($rec['intern_bdc'])*100/$total;
    $tmp['intern']['cont']    = intval($rec['intern_contingents'])*100/$total;
    $tmp['part'  ]['printed'] = intval($rec['part_printed'])*100/$total;
    $tmp['part'  ]['reste']   = intval($rec['part_reste'])*100/$total;
    
    // pour faire la super-position des barres
    $tmp['part'  ]['reste']   +=
    $tmp['part'  ]['printed'] +=
    $tmp['intern']['cont']    +=
    $tmp['intern']['bdc']     +=
    $tmp['intern']['printed'];
    if ( intval($rec['reste']) > 0 )
      $tmp['reste']           += $tmp['part']['reste'];
    
    $tmp['reste']             = round($tmp['reste']);
    $tmp['intern']['printed'] = round($tmp['intern']['printed']);
    $tmp['intern']['bdc']     = round($tmp['intern']['bdc']);
    $tmp['intern']['cont']    = round($tmp['intern']['cont']);
    $tmp['part'  ]['printed'] = round($tmp['part'  ]['printed']);
    $tmp['part'  ]['reste']   = round($tmp['part'  ]['reste']);
    
    $data[0]['values'][] = $tmp['reste'];
    $data[1]['values'][] = $tmp['part'  ]['reste'];
    $data[2]['values'][] = $tmp['part'  ]['printed'];
    $data[3]['values'][] = $tmp['intern']['cont'];
    $data[4]['values'][] = $tmp['intern']['bdc'];
    $data[5]['values'][] = $tmp['intern']['printed'];
    $data['xlabels'][]   = $period == 'manifs'
      ? trim(mb_substr($rec['nom'],0,8,'UTF-8')).(mb_strlen($rec['nom'],'UTF-8') > 7 ? '...' : '').' '.date('d/m H:i',strtotime($rec['date']))
      : trim(mb_substr($rec['nom'],0,19,'UTF-8')).(mb_strlen($rec['nom'],'UTF-8') > 18 ? '...' : '');
  }
  
  $request->free();
  $bd->free();
  
  includeClass('graphe');
  includeClass('inc/Color');
  
  function before_add_group(&$group)
  {
    $group->axis->bottom->label->setAngle(75);
    $group->setPadding(40, 40, 60, 100);
  }
  
	$data['period']  = $date['start'].' -> '.$date['stop'];
  for ( $i = 0 ; $i < 6 ; $i++ )
    $data[$i]['type']   = 'BarPlot';
  $data[0]['legend'] = 'Reste général';
  $data[0]['color']  = new Green;
  $data[1]['legend'] = 'Reste part.';
  $data[1]['color']  = new LightGreen;
  $data[2]['legend'] = 'Vendus part.';
  $data[2]['color']  = new LightRed;
  $data[3]['legend'] = 'Contingents';
  $data[3]['color']  = new VeryLightOrange;
  $data[4]['legend'] = 'Bons de comm.';
  $data[4]['color']  = new LightOrange;
  $data[5]['legend'] = 'Vendus';
  $data[5]['color']  = new Red;
  
  $graphe = new Graphe;
  $graphe->setData($data);
  $graphe->draw(array('width' => 1000, 'height' => 460), 0);
  
?>
