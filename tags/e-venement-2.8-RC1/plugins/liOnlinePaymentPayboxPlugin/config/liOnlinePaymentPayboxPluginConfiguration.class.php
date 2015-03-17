<?php
class liOnlinePaymentPayboxPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    require_once __DIR__.'/../lib/PayboxPayment.class.php';
  }
}
