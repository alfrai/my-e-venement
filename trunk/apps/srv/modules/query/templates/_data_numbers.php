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
    use_helper('Number');
    $g = new liGraph;
    $data = $sf_data->getRaw('data');
    
    // some colours
    $colours = array(
      '#FF6B6B',
      '#4ECDC4',
      '#ACC476',
      '#556270',
      '#C44D58',
    );
    $col_i = 0;
    
    // lines for average & standard deviation
    $dot = new liDotSolid;
    $dot->size(3)->halo_size(1);
    foreach ( $arr = array('average', 'deviation') as $name )
    if ( isset($data[$name]) )
    {
      $area = new liArea();
      $area->set_colour($colours[$col_i++]);
      $area->set_key(__(ucfirst($name).': '.format_number(round($data[$name],2))), 12);
      $g->add_element($area);
      
      unset($data[$name]);
    }
    
    // the content
    $bar = new liBarFilled($colours[$col_i++]);
    $bar->set_values($data);
    $g->add_element($bar);
    
    // the y scale
    $y = new liYAxis;
    $top = ceil(max($data)+max($data)/10);
    $y->set_range(0, $top > 10 ? $top : 10, 1);
    $g->set_y_axis($y);
    
    echo $g;
