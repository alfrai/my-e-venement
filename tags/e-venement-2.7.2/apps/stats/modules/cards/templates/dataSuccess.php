
<?php
    // the graph
    $g = new liGraph;
    $pie = new liPie;
    
    $data = array();
    foreach ( $mc as $value )
    {
      $byvalue = false;
      if ( isset($accounting['price']) && is_array($accounting['price']) )
      foreach ( $accounting['price'] as $price )
      if ( $price )
        $byvalue = true;
      
      $data[]   = new liPieValue(
        $byvalue ? round(round($value['nb']/365)*$accounting['price'][$value['name']],2) : $nb = round($value['nb']/365),
        $nb.' '.$value['name']
      );
    }
    
    $pie->set_values($data);
    $pie->set_tooltip(
      $byvalue
      ? __('#label# for an amount of #val#').": #percent#\nTotal: #total#"
      : "#label#: #percent#\nTotal: #total#"
    );
    
    $g->add_element($pie);
    echo $g;
