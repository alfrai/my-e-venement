<?php include_partial('global/ariane',array('active' => 1)) ?>
<?php include_partial('global/oplog',array('contact' => isset($contact) ? $contact : NULL)) ?>
<?php include_partial('show_title',array('manifestation' => $manifestation)) ?>
<?php include_partial('show_gauges',array('gauges' => $gauges, 'manifestation' => $manifestation, 'form' => $form, 'mcp' => $mcp, )) ?>
<?php include_partial('show_footer') ?>
