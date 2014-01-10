<?php
    use_helper('Date');
    
    $g = new liGraph;
    $bars = new liBarStack;
    $bars->set_alpha(0.8);
    
    $max = 0;
    $x_labels = array();
    foreach ( $manifs as $manif )
    {
      $x_labels[] = new liXAxisLabel(((string)$manif->Event > '40' ? ($manif->Event->short_name ? $manif->Event->short_name : substr($manif->Event,0,40)) : $manif->Event).' @ '.format_date($manif->happens_at), '#000', 11, 45);
     
      $asked = sfConfig::get('project_tickets_count_demands',false) ? $manif->asked : 0;
      $free = $manif->gauge - $manif->printed - $manif->ordered - $asked;
      
      $bars->append_stack($values = array(
        $manif->printed,
        $manif->ordered,
        $asked,
        $free >= 0 ? $free : 0,
        $free <  0 ? $free : 0,
      ));
      
      $tmp = 0;
      foreach ( $values as $value )
        $tmp += $value;
      $max = $tmp > $max ? $tmp : $max;
    }
    
    $bars->set_keys(array(
      new liBarStackKey('#DE0202', __('Printed'), 11),
      new liBarStackKey('#FF7800', __('Ordered'), 11),
      sfConfig::get('project_tickets_cound_demands',false) ? new liBarStackKey('#00A0B0', __('Asked'), 11) : new liBarStackKey('', '', 11),
      new liBarStackKey('#20FF00', __('Free'), 11),
      new liBarStackKey('#490A3D', __('Overbooked'), 11),
    ));
    $bars->set_tooltip("#x_label#: #val#\nTotal: #total#");
    
    $y = new liYAxis;
    $y->set_range( 0, $max, round($max/100)*10 );
    
    $x = new liXAxis;
    $x->set_labels_from_array($x_labels);
    
    $g->add_element($bars);
    $g->set_x_axis($x);
    $g->add_y_axis($y);
    
    $tt = new liTooltip;
    $tt->set_hover();
    $g->set_tooltip($tt);
    
    echo $g;
?>
