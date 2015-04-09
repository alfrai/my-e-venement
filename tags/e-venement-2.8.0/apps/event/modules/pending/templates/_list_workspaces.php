<?php
  $ws = array();
  foreach ( $manifestation->Gauges as $gauge )
    $ws[] = (string)$gauge->Workspace;
  
  echo '<span class="workspace">'.implode('</span>, <span class="workspace">', $ws).'</span>';
?>
