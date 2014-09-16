<?php
  $html = get_partial('contact/labels_html', array(
    'labels' => $labels,
    'params' => $params,
    'fields' => $fields,
  ));
  
  if ( sfConfig::get('sf_debug') )
    echo $html;
  else
  {
    $pdf = new sfDomPDFPlugin($html);
    $pdf->setPaper($params['page-format'],'portrait');
    $pdf->execute();
  }
