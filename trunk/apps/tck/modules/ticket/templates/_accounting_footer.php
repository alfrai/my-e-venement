<p class="footer"><?php
  $invoice = sfConfig::get('app_seller_invoice');
  echo nl2br($invoice['footer'])
?></p>
