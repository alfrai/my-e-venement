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
*    Copyright (c) 2006-2013 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2013 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php use_helper('Number') ?>
<?php if ( $manifestation->reservation_confirmed ): ?>
<?php
  $tickets = array('asked' => 0, 'ordered' => 0, 'printed' => 0, 'booked' => 0, 'total' => 0);
  
  $full = true;
  if ( $manifestation->Gauges->count() > 0 && !isset($manifestation->Gauges[0]->printed) )
  {
    foreach ( $manifestation->Gauges as $gauge )
    if ( isset($gauge->Workspace->Order[0])
      && !is_null($gauge->Workspace->Order[0]->rank)
      && $gauge->Workspace->Order[0]->rank > 999
      && $manifestation->expected_income )
    {
      $full = 'with_value';
      break;
    }
    
    $manifestation->Gauges = Doctrine::getTable('Gauge')->createQuery('g', $full)
      ->andWhere('g.manifestation_id = ?',$manifestation->id)
      ->execute();
  }
  
  foreach ( $manifestation->Gauges as $gauge )
  {
    if ( isset($gauge->Workspace->Order[0])
      && !is_null($gauge->Workspace->Order[0]->rank)
      && $gauge->Workspace->Order[0]->rank < 0 )
    {
      $tickets['total']   = $full === 'with_value' ? $manifestation->expected_income : $gauge->value;
      $tickets['asked']   = $gauge->asked;
      $tickets['ordered'] = $gauge->ordered;
      $tickets['printed'] = $gauge->printed;
      $tickets['booked']  = $gauge->ordered + $gauge->printed;
      if ( sfConfig::get('project_tickets_count_demands',false) )
        $tickets['booked'] += $gauge->asked;
      break;
    }
    else
    {
      $tickets['total']   = $full === 'with_value' ? $manifestation->expected_income : $tickets['total'] + $gauge->value;
      $tickets['asked']   += $gauge->asked;
      $tickets['ordered'] += $gauge->ordered;
      $tickets['printed'] += $gauge->printed;
      $tickets['booked']  += $gauge->ordered + $gauge->printed;
      if ( sfConfig::get('project_tickets_count_demands',false) )
        $tickets['booked'] += $gauge->asked;
    }
  }
?>
<?php if ( sfConfig::get('project_tickets_count_demands',false) ): ?>
<?php echo __('<strong class="booked">%%b%%</strong>/<strong class="total">%%t%%</strong> (<span title="sold" class="sold">%%p%%</span>-<span title="ordered" class="ordered">%%o%%</span>-<span title="asked" class="asked">%%a%%</span>)', array(
    '%%p%%' => $full === 'with_value' ? format_currency($tickets['printed'],'€') : $tickets['printed'],
    '%%o%%' => $full === 'with_value' ? format_currency($tickets['ordered'],'€') : $tickets['ordered'],
    '%%a%%' => $full === 'with_value' ? format_currency($tickets['asked'],'€') : $tickets['asked'],
    '%%b%%' => $full === 'with_value' ? format_currency($tickets['booked'],'€') : $tickets['booked'],
    '%%t%%' => $full === 'with_value' ? format_currency($tickets['total'],'€') : $tickets['total'],
  )) ?>
<?php else: ?>
<?php echo __('<strong class="booked">%%b%%</strong>/<strong class="total">%%t%%</strong> (<span title="sold" class="sold">%%p%%</span>-<span title="ordered" class="ordered">%%o%%</span>)', array(
    '%%p%%' => $full === 'with_value' ? format_currency($tickets['printed'],'€') : $tickets['printed'],
    '%%o%%' => $full === 'with_value' ? format_currency($tickets['ordered'],'€') : $tickets['ordered'],
    '%%b%%' => $full === 'with_value' ? format_currency($tickets['booked'],'€') : $tickets['booked'],
    '%%t%%' => $full === 'with_value' ? format_currency($tickets['total'],'€') : $tickets['total'],
  )) ?>
<?php endif ?>
<?php else: ?>
<?php echo image_tag('/sfDoctrinePlugin/images/delete.png', array('title' => __('Not confirmed'), 'class' => 'confirmed')) ?>
<?php echo image_tag(!$manifestation->reservation_optional ? '/sfDoctrinePlugin/images/tick.png' : '/sfDoctrinePlugin/images/delete.png', array('title' => $manifestation->reservation_optional ? __('Option') : __('Not an option'), 'class' => 'option')) ?>
<?php
  try { echo image_tag($manifestation->hasAnyConflict() ? '/sfDoctrinePlugin/images/delete.png' : '/sfDoctrinePlugin/images/tick.png', array('title' => $manifestation->hasAnyConflict() ? __('There are use conflicts') : __('There is no use conflict'), 'class' => 'conflict')); }
  catch ( liBookingException $e )
  { }
?>
<?php endif ?>
