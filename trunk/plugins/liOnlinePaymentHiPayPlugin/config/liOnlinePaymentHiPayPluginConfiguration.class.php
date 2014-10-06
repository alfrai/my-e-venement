<?php
class liOnlinePaymentHiPayPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    require_once __DIR__.'/../lib/HiPayPayment.class.php';
    require_once __DIR__.'/../lib/HiPayPaymentParams.class.php';
  }
}
