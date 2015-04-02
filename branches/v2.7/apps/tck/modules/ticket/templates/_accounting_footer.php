<div class="footer"><?php
  $translate = array(
    '%%transaction_id%%' => $transaction->id,
    '%%order_id%%' => $type == 'order' ? $transaction->Order[0]->id : '',
    '%%invoice_id%%' => $type == 'invoice' ? sfConfig::get('app_seller_invoice_prefix').$transaction->Invoice[0]->id : '',
  );
  echo str_replace(array_keys($translate),array_values($translate),nl2br(sfConfig::get('app_seller_invoice_footer')));
?></div>
