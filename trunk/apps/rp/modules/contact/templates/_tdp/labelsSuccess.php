<?php
  $html = get_partial('contact/labels_html', array(
    'labels' => $labels,
    'params' => $params,
    'fields' => $fields,
  ));
  
  if ( sfConfig::get('sf_web_debug') )
  {
    $sf_response->setContentType('text/html');
    echo $html;
  }
  else
  {
    $pdf = new sfDomPDFPlugin($html);
    $pdf->setPaper($params['page-format'],'portrait');
    $pdf->execute();
  }
