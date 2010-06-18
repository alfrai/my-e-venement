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
	
	if ( !function_exists('imageantialias') )
	{
	  function imageantialias($image, $enabled)
	  { return false; }
  }
	
  $from = strtotime($_GET['from']) ? strtotime($_GET['from']) : strtotime('now');
  
  $dates = $label = array();
  $period = $_GET['period'] ? $_GET['period'] : 'weeks';
  switch ( $period ) {
  case 'manifs':
  case 'events':
    break;
  case 'days':
    $start = $now = strtotime('+1 '.$period,$from);
    for ( $i = 0 ; $i < 12 ; $i++ )
    {
      $nowlabel = strtotime('1 '.$period.' ago',$now); // cas particulier des jours convertis en timestamp
      $label[] = $config['dates']['dotw'][intval(date('w',$nowlabel))].' '.date('d/m',$nowlabel);
      $dates[] = date('Y-m-d',strtotime('+1 day',$now));
      $now = strtotime('1 '.$period.' ago',$now);
    }
    break;
  case 'years':
    $start = $now = strtotime((intval(date('Y',$from))+1).'-01-01');
    for ( $i = 0 ; $i < 12 ; $i++ )
    {
      $label[] = intval(date('Y',$now)) - 1;
      $dates[] = date('Y-m-d',$now);
      $now = strtotime($i.' '.$period.' ago',$now);
    }
    break;
  case 'month':
    $start = $now = strtotime(date('Y-m-01',strtotime('+1 '.$period,$from)));
    for ( $i = 0 ; $i < 12 ; $i++ )
    {
      $label[] = $config['dates']['MOTY'][intval(date('n',strtotime('1 '.$period.' ago',$now))) - 1].' '.date('y',strtotime('1 '.$period.' ago',$now));
      $dates[] = date('Y-m-d',$now);
      $now = strtotime('1 '.$period.' ago',$now);
    }
    break;
  default:
    $period = 'weeks';
  case 'weeks':
    $start = $now = strtotime('next monday',$from);
    for ( $i = 0 ; $i < 12 ; $i++ )
    {
      $label[] = date('d/m',strtotime('1 '.$period.' ago',$now)).' -> '.date('d/m',strtotime('1 day ago',$now));
      $dates[] = date('Y-m-d',$now);
      $now = strtotime('1 '.$period.' ago',$now);
    }
    $break;
  }
  
  if ( in_array($period,array('events','manifs')) )
  {
    if ( $period == 'events' )
    {
      $from = " (SELECT evt.id, min(date) AS date, evt.nom
                 FROM evenement evt, manifestation
                 WHERE evt.id = manifestation.evtid
                   AND date >= '".date('Y-m-d',$from)."'::date
                   AND manifestation.id IN (SELECT manifid FROM reservation_pre rp WHERE NOT annul)
                 GROUP BY evt.nom, evt.id
                 ORDER BY date
                 LIMIT 6)
                UNION
                (SELECT evt.id, min(date) AS date, evt.nom
                 FROM evenement evt, manifestation
                 WHERE evt.id = manifestation.evtid
                   AND date < '".date('Y-m-d',$from)."'::date
                   AND manifestation.id IN (SELECT manifid FROM reservation_pre rp WHERE NOT annul)
                 GROUP BY evt.nom, evt.id
                 ORDER BY date DESC
                 LIMIT 6)";
    }
    else  // ( $period == 'events' )
    {
      $from = " (SELECT manifestation.id, manifestation.date, evt.nom
	               FROM evenement evt, manifestation
	               WHERE evt.id = manifestation.evtid
	                 AND date < '".date('Y-m-d',$from)."'::date
	                 AND manifestation.id IN (SELECT DISTINCT manifid FROM reservation_pre rp WHERE NOT annul)
	               ORDER BY date
	               LIMIT 6)
	              UNION
	              (SELECT manifestation.id, manifestation.date, evt.nom
	               FROM evenement evt, manifestation
	               WHERE evt.id = manifestation.evtid
	                 AND date >= '".date('Y-m-d',$from)."'::date
	                 AND manifestation.id IN (SELECT DISTINCT manifid FROM reservation_pre rp WHERE NOT annul)
	               ORDER BY date
	               LIMIT 6)";
    }
  	$query  = " SELECT m.nom, m.date,
	                     sum((c.id IS NULL AND ps.id IS NULL)::integer) AS demanded,
	                     sum((ps.id IS NOT NULL AND c.id IS NULL)::integer) AS preselled,
	                     count(c.id) AS printed
	              FROM (".$from.") AS m, ".($period == 'events' ? 'manifestation manif,' : '')." reservation_pre p
	              LEFT JOIN reservation_cur c ON p.id = c.resa_preid AND NOT canceled
	              LEFT JOIN preselled ps ON ps.transaction = p.transaction
	              WHERE NOT annul
                  AND ".($period == 'manifs' ? 'p.manifid = m.id' : 'p.manifid = manif.id AND manif.evtid = m.id')."
	              GROUP BY m.date, m.id, m.nom
	              ORDER BY m.date";
  }
  else  // ( in_array($period,array('events','manifs')) )
  {
  	$query  = " SELECT d.date,
	                     sum((c.id IS NULL AND ps.id IS NULL AND p.id IS NOT NULL)::integer) AS demanded,
	                     sum((ps.id IS NOT NULL AND c.id IS NULL)::integer) AS preselled,
	                     count(c.id) AS printed,
	                     (SELECT count(*)
	                      FROM reservation_pre p, manifestation m
	                      WHERE p.manifid = m.id
	                        AND ( p.id IN (SELECT c.resa_preid FROM reservation_cur c WHERE NOT canceled)
	                           OR p.transaction IN (SELECT transaction FROM preselled ps)
	                            )
	                        AND m.date < d.date
	                        AND m.date >= d.date - '1 ".$period."'::interval
	                        AND NOT annul) AS spectators
	              FROM (SELECT '".implode("'::date AS date UNION SELECT '",$dates)."') AS d
	              LEFT JOIN reservation_pre p ON NOT annul AND p.date >= d.date - '1 ".$period."'::interval AND p.date < d.date
	              LEFT JOIN reservation_cur c ON p.id = c.resa_preid AND NOT canceled
	              LEFT JOIN preselled ps ON ps.transaction = p.transaction
	              GROUP BY d.date
	              ORDER BY d.date DESC";
	}
  $request = new bdRequest($bd,$query);
  
  $values = array(
    'demanded'  => array(),
    'preselled' => array(),
    'printed'   => array(),
  );
  while ( $rec = $request->getRecordNext() )
  {
    $values['demanded'][]  = intval($rec['demanded']);
    $values['preselled'][] = intval($rec['preselled']);
    $values['printed'][]   = intval($rec['printed']);
    $values['spectators'][]= intval($rec['spectators']);
  }
  if ( in_array($period,array('events','manifs')) )
  {
    $request->firstRecord();
    while ( $rec = $request->getRecordNext() )
    if ( $period == 'manifs' )
      $label[] = trim(mb_substr($rec['nom'],0, 8, 'UTF-8')).(mb_strlen($rec['nom'],'UTF-8') >  8 ? '... ' : ' ').date('d/m',strtotime($rec['date']));
    else
      $label[] = trim(mb_substr($rec['nom'],0,15, 'UTF-8')).(mb_strlen($rec['nom'],'UTF-8') > 15 ? '...' : '');
  }
  
  $request->free();
  $bd->free();
  
  includeClass('graphe');
  if ( !isset($_GET['csv']) )
    includeClass('inc/Color');
  
  $graphe = new Graphe;

  $names = array(
    'days'   => 'Jour',
    'weeks'  => 'Semaine',
    'month'  => 'Mois',
    'years'  => 'Année',
    'events' => 'Événement',
    'manifs' => 'Manifestation',
  );
  
  $data = array(
    'period'  => $names[$period],
    'xlabels' => array_reverse($label),
  );
  $plot = array();
  foreach ( $values as $key => $value )
  {
    $plot['values'] = array_reverse($value);
    $plot['type']   = 'BarPlot';
    
    switch ( $key ) {
    case 'spectators':
      $plot['color']  = !isset($_GET['csv']) ? new DarkRed(25) : NULL;
      $plot['legend'] = 'Spectateurs';
      $plot['type']  = 'LinePlot';
      break;
    case 'demanded':
      $plot['color'] = !isset($_GET['csv']) ? new LightBlue(25) : NULL;
      $plot['legend']  = 'Demandes';
      break;
    case 'preselled':
      $plot['color'] = !isset($_GET['csv']) ? new LightOrange(25) : NULL;
      $plot['legend']  = 'Pré-résas';
      break;
    case 'printed':
      $plot['color'] = !isset($_GET['csv']) ? new Red(25) : NULL;
      $plot['legend']  = 'Réservations';
      break;
    }
    $data[] = $plot;
  }
  
  $graphe->setData($data);
  if ( isset($_GET['csv']) )
    $graphe->csv('suivi-billetterie-'.date('Ymd').'-'.date('Ymd',$from));
  else
    $graphe->draw();
?>
