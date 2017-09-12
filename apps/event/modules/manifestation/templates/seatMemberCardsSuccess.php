<?php
    $pks = $sf_data->getRaw('tickets')->getPrimaryKeys();
    echo json_encode($pks && $pks != array(null) ? $pks : array());
