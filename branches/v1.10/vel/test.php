<?php
  $arr = array(
    'baptiste.simon@foodpath.eu',
    'Baptiste',
    'SIMON',
    md5('glop'),
    'aqolni21n_ç145q,',
  );
  
  echo implode('',$arr);
  echo "\n";
  echo md5(implode('',$arr));
  echo "\n";
?>
