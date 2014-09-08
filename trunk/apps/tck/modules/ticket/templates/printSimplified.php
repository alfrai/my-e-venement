<?php
  $pdf = new sfDomPDFPlugin();
  $pdf->setInput(get_partial('global/get_tickets_pdf', array('tickets_html' => $content)));
  echo $pdf->render();
?>
