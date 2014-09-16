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
<?php
    use_helper('Date');
    
    $g = new liGraph;
    $line = new liLine;
    
    $dot = new liDotSolid;
    $dot->size(3)->halo_size(1)->colour('#f00000');
    $line->set_width(2);
    $line->set_colour('#fe3462');
    $line->set_default_dot_style($dot);
    $line->set_key(__('Debts'),11);
    
    // data
    $names = $max = array();
    foreach ( $dates as $date )
    {
      $names[] = new liXAxisLabel(format_date($date['date']), '#000', 11, 45); // legend
      $values[] = $date['outcome'] - $date['income']; // content
    }
    
    // finitions
    $line->set_values($values);
    
    $y = new liYAxis;
    $y->set_range(0, max($values), round(max($values)/100)*10);
    
    $x = new liXAxis;
    $x->set_labels_from_array($names);
    
    $g->add_element($line);
    $g->set_x_axis($x);
    $g->add_y_axis($y);
    
    echo $g;
