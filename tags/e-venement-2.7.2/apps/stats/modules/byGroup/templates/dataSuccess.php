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
    $bars = array();
    $colors = array(
      $manifs[0]->id => array('#ec7890','#fe3462'),
      $manifs[1]->id => array('#eca478','#fe8134'),
      $manifs[2]->id => array('#789aec','#1245b9'),
      -1 => array('#7cec78','#17b912'),
    );
    foreach ( $manifs as $manif )
    {
      $id = !isset($colors[$manif->id]) ? -1 : $manif->id;
      $bars[$manif->id] = new stBarOutline( 40, $colors[$id][0], $colors[$id][1] );
      $bars[$manif->id]->key( $manif->getShortName(), 10 );
    }
    
    //Passing the random data to bar chart
    $names = $max = array();
    foreach ( $groups as $group )
    if ( isset($bars[$group['manifestation_id']]) )
    {
      $names[] = $group['name'];
      $max[] = $group['nb_entries'];
      $bars[$group['manifestation_id']]->add_link($group['nb_entries'],cross_app_url_for('rp','group/show?id='.$group['id'],true));
    }
    
    //Creating a stGraph object
    $g = new stGraph();
    //$g->title( __('Gauge filling'), '{font-size: 20px;}' );
    $g->bg_colour = '#E4F5FC';
    $g->bg_colour = '#FFFFFF';
    $g->set_inner_background( '#E3F0FD', '#CBD7E6', 90 );
    $g->x_axis_colour( '#8499A4', '#E4F5FC' );
    $g->y_axis_colour( '#8499A4', '#E4F5FC' );
 
    //Pass stBarOutline object i.e. $bar to graph
    $g->data_sets = $bars;
 
    //Setting labels for X-Axis
    $g->set_x_labels($names);
 
    // to set the format of labels on x-axis e.g. font, color, step
    $g->set_x_label_style( 10, '#18A6FF', 2, 1 );
 
    // To tick the values on x-axis
    // 2 means tick every 2nd value
    //$g->set_x_axis_steps( 1 );
 
    //set maximum value for y-axis
    //we can fix the value as 20, 10 etc.
    //but its better to use max of data
    $g->set_y_max($max);
    $g->y_label_steps( 4 );
    $g->set_y_legend( __('Percentage on gauge'), 12, '#18A6FF' );

    echo $g;
