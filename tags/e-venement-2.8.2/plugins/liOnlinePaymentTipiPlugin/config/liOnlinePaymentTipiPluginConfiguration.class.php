<?php
class liOnlinePaymentTipiPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    require_once __DIR__.'/../lib/TipiPayment.class.php';
  }
}
