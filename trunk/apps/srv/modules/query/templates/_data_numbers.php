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
    $g = new liGraph;
    $data = $sf_data->getRaw('data');
    
    // some colours
    $colours = array('#4ECDC4', '#ACC476', '#FF6B6B', '#C44D58', '#556270');
    $col_i = 0;
    
    // the content
    $bar = new liBarFilled($colours[$col_i]);
    $bar->set_values($data);
    $g->add_element($bar);
    $col_i++;
    
    // the y scale
    $y = new liYAxis;
    $top = ceil(max($data)+max($data)/10);
    $y->set_range(0, $top > 10 ? $top : 10, 1);
    $g->set_y_axis($y);
    
    echo $g;
