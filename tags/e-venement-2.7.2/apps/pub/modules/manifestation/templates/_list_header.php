<?php include_partial('global/ariane',array('active' => 1)) ?>
<?php include_partial('event/description', array('manifestations' => $pager->getResults(), 'event' => $filters)) ?>
