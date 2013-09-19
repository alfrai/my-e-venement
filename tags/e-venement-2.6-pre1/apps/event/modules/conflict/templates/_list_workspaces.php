<?php
  $ws = array();
  foreach ( $manifestation->Gauges as $gauge )
    $ws[] = '<span class="workspace" title="'.$gauge->Workspace.'">'.$gauge->Workspace.'</span>';
  echo implode('', $ws);
?>
