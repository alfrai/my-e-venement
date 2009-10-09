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

	includeLib("headers");
  
  $from = strtotime($_GET['from']) ? strtotime($_GET['from']) : strtotime('now');
  
  $period = $_GET['period'] ? $_GET['period'] : 'month';
  switch ( $period ) {
  case 'days':
    $now = strtotime('now',$from);
    break;
  case 'years':
    $now = strtotime((intval(date('Y'))+1).'-01-01',$from);
    break;
  case 'month':
    $now = strtotime(date('Y').'-'.(intval(date('n'))+1).'-01',$from);
    $break;
  case 'weeks':
  default:
    $period = 'weeks';
    $now = strtotime('next monday',$from);
    break;
  }
  
  $dates = array();
  for ( $i = 0 ; $i < 12 ; $i++ )
    $dates[] = date('Y-m-d',strtotime($i.' '.$period.' ago',$now));
  
	$query  = " SELECT d.date,
	                   sum((c.id IS NULL AND ps.id IS NULL)::integer) AS demanded,
	                   sum((ps.id IS NOT NULL AND c.id IS NULL)::integer) AS preselled,
	                   count(c.id) AS printed
	            FROM (SELECT '".implode("'::date AS date UNION SELECT '",$dates)."') AS d, reservation_pre p
	            LEFT JOIN reservation_cur c ON p.id = c.resa_preid AND NOT canceled
	            LEFT JOIN preselled ps ON ps.transaction = p.transaction
	            WHERE NOT annul
	              AND p.date >= d.date - '1 ".$period."'::interval
	              AND p.date < d.date
	            GROUP BY d.date
	            ORDER BY d.date DESC";
	$request = new bdRequest($bd,$query);

  function print_period($rec, $datas, $maxheight, $scale)
  {
    ?>
    <td class="gfx" style="height: <?php echo round($maxheight * 1.05) + 50 ?>px;">
      <table>
        <tr class="gfx">
          <?php foreach ( $datas as $data => $color ): ?>
          <td title="<?php echo intval($rec[$data]) ?>" class="<?php echo htmlsecure($data) ?>">
            <span style="padding-top: <?php echo round(intval($rec[$data])*$scale) ?>px;"></span>
          </td>
          <?php endforeach; ?>
        </tr>
        <tr>
          <?php foreach ( $datas as $data => $color ): ?>
          <td><span><?php echo intval($rec[$data]) ?></span></td>
          <?php endforeach; ?>
        </tr>
        <tr>
          <td><span>Dem.</span></td>
          <td><span>Pré.</span></td>
          <td><span>Rés.</span></td>
        </tr>
      </table>
    </td>
    <?php
  }
?>
<h1><?php echo $title ?></h1>
<?php includeLib("tree-view"); ?>
<?php require("actions.php"); ?>
<div class="body">
<form action="<?php echo htmlsecure($_SERVER['PHP_SELF']) ?>" method="get">
  <p>
    <select name="period">
      <option value="days"  <?php if ( $period == 'days'  ) echo 'selected="selected"' ?>>Jours</option>
      <option value="weeks" <?php if ( $period == 'weeks' ) echo 'selected="selected"' ?>>Semaines</option>
      <option value="month" <?php if ( $period == 'month' ) echo 'selected="selected"' ?>>Mois</option>
      <option value="years" <?php if ( $period == 'years' ) echo 'selected="selected"' ?>>Années</option>
    </select>
    <span class="input">Date: <input type="text" name="from" value="<?php echo date('Y-m-d',$from) != date('Y-m-d') ? date('Y-m-d',$from) : '' ?>" /></span>
    <input type="submit" name="submit" value="ok" />
  </p>
</form>
<table class="stats">
  <tfoot>
    <tr>
    <?php foreach ( $dates as $date ): ?>
      <td>
      <?php
        switch ( $period ) {
        case 'days':
          echo htmlsecure( $config["dates"]["DOTW"][ intval(date('w',strtotime($date))) ] );
          break;
        case 'weeks':
          echo htmlsecure('Semaine '.date('W',strtotime('1 '.$period.' ago',strtotime($date))));
          break;
        case 'month':
          echo htmlsecure($config["dates"]["moty"][date('n',strtotime('1 '.$period.' ago',strtotime($date))) - 1].' '.date('Y',strtotime($date)));
          break;
        case 'years':
          echo htmlsecure(intval(date('Y',strtotime($date)))-1);
          break;
        }
      ?>
      </td>
    <?php endforeach; ?>
    </tr>
  </tfoot>
  <tbody>
  <tr>
  <?php
    $datas = array(
      'demanded' => 'blue',
      'preselled' => 'orange',
      'printed' => 'red');
    $maxheight = '300px';
    
    $max = 1;
    while ( $rec = $request->getRecordNext() )
    foreach ( $datas as $data => $color )
      $max = intval($rec[$data]) > $max ? intval($rec[$data]) : $max;
    $request->firstRecord();
    
    $maxheight = '300'; // in "px"
    $scale = $maxheight / $max;
  ?>
  <?php while ( $rec = $request->getRecordNext() ): ?>
  <?php while ( date('Y-m-d',$now) != date('Y-m-d',strtotime($rec['date'])) ): ?>
    <td class="gfx">
      <table>
        <tr class="gfx">
          <?php foreach ( $datas as $data => $color ): ?>
          <td title="<?php echo intval($rec[$data]) ?>" class="<?php echo htmlsecure($data) ?>">
            <span style="padding-top: 0px;"></span>
          </td>
          <?php endforeach; ?>
        </tr>
        <tr>
          <?php foreach ( $datas as $data => $color ): ?>
          <td>
            <span>0</span>
          </td>
          <?php endforeach; ?>
        </tr>
        <tr>
          <td><span>Dem.</span></td>
          <td><span>Pré.</span></td>
          <td><span>Rés.</span></td>
        </tr>
      </table>
    </td>
    <?php $now = strtotime('1 '.$period.' ago',$now) ?>
  <?php endwhile; ?>
  <?php $now = strtotime('1 '.$period.' ago',$now); ?>
  <?php print_period($rec, $datas, $maxheight, $scale); ?>
  <?php endwhile; ?>
  <?php
    $maxdate = date('Y-m-d',strtotime('1 '.$period.' ago',strtotime($dates[count($dates)-1])));
    for ( ; date('Y-m-d',$now) != $maxdate ; $now = strtotime('1 '.$period.' ago',$now) )
      print_period(array(), $datas, $maxheight, $scale);
  ?>
  </tr>
  </tbody>
</table>
<p class="scale">Échelle: <?php echo $scale ?></p>
</div>
<?php
	includeLib("footer");
?>
