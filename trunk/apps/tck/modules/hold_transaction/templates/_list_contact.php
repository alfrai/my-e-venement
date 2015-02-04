<?php use_helper('CrossAppLink') ?>
<?php if ( $hold_transaction->Transaction->contact_id ): ?>
<?php echo cross_app_link_to($hold_transaction->Transaction->Contact, 'rp', 'contact/edit?id='.$hold_transaction->Transaction->contact_id) ?>
<?php endif ?>
