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
*    Copyright (c) 2006-2014 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2014 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php
  $data = $request->getParameter('auto_ranks', array());
  foreach ( array(
    'id'  => 'intval',
    'top' => 'intval',
    'num_mini' => 'intval',
    'num_maxi' => 'intval',
    'row_min'  => 'strval',
    'row_max'  => 'strval',
    'row_hop'  => 'intval',
    'num_hop'  => 'intval',
    'format'   => 'strval',
  ) as $field => $callback )
  {
    if (!( isset($data[$field]) && call_user_func($callback, $data[$field]).'' === ''.$data[$field] ))
      throw new liSeatedException('Given data do not permit the seat recording (bad data on '.$field.').');
    $data[$field] = call_user_func($callback, $data[$field]);
  }
  
  $counter = array('rows' => 0, 'seats' => 0);
  $ranges = array(
    'rows'  => range($data['row_min'], $data['row_max']),
    'seats' => range($data['num_mini'], $data['num_maxi']),
  );
  error_log(print_r($data,true));
  foreach ( $ranges['rows']  as $row )
  {
    for ( $i = 0 ; $i < (isset($data['contiguous']) ? 1 : 2) ; $i++ )
    {
      $counter['seats'] = 0;
      foreach ( $ranges['seats'] as $seat )
      if (!( !isset($data['contiguous']) && $seat % 2 == $i ))
      {
        $num = str_replace('%num%', $seat, $data['format']);
        $num = str_replace('%row%', $row,  $num);
        
        $q = Doctrine::getTable('Seat')->createQuery('s')
          ->andWhere('s.name = ?', $num)
          ->andWhere('s.seated_plan_id = ?', $data['id'])
        ;
        $seat = $q->fetchOne();
        if ( !$seat )
          continue;
        
        $rank = $data['top'] + $counter['seats']*$data['num_hop'] + $counter['rows']*$data['row_hop'];
        $seat->rank = $rank;
        $saved = $seat->trySave();
        if ( sfConfig::get('sf_debug',false) )
          error_log("$num set with rank $rank, ".($saved ? '' : 'not ').'saved');
        
        $counter['seats']++;
      } // endforeach
    } // endfor
    
    $counter['rows']++;
  }
  
  return sfView::NONE;
