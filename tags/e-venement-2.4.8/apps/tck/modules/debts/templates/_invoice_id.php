<?php if ($transaction->Invoice->count() > 0): ?>
#<?php echo link_to(sfConfig::get('app_seller_invoice_prefix').$transaction->Invoice[0]->id,'ticket/invoice?id='.$transaction->id, array('target' => 'blank', 'onclick' => 'javascript: setTimeout(function(){$("#transition").hide();},500);')) ?>
<?php endif ?>
