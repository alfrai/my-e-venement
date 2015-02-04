<?php use_helper('CrossAppLink') ?>
<?php if ( $hold_transaction->Transaction->professional_id ): ?>
<?php echo cross_app_link_to($hold_transaction->Transaction->Professional->full_desc, 'rp', 'organism/edit?id='.$hold_transaction->Transaction->Professional->organism_id) ?>
<?php endif ?>
