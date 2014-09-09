<?php
class liOnlinePaymentSystempayPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    require_once __DIR__.'/../lib/SystempayPayment.class.php';
  }
}
