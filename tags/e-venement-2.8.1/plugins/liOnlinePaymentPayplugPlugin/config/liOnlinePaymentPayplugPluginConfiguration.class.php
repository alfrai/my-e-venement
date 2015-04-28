<?php
class liOnlinePaymentPayplugPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    require_once __DIR__.'/../lib/PayplugPayment.class.php';
  }
}
