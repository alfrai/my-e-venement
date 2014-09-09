<?php
class liOnlinePaymentCitelisPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    require_once __DIR__.'/../lib/CitelisPayment.class.php';
  }
}
