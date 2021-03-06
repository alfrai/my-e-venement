<?php

/**
 * PricePOSTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PricePOSTable extends PluginPricePOSTable
{
  public function retrieveList()
  {
    $q = $this->createQuery('pos')
      ->leftJoin('pos.Price p')
    ;
    
    if ( !sfContext::hasInstance() )
      $q->leftJoin('p.Translation pt');
    else
      $q->leftJoin('p.Translation pt WITH pt.lang = ?', sfContext::getInstance()->getUser()->getCulture());
    
    return $q;
  }
  
  /**
   * Returns an instance of this class.
   *
   * @return object PricePOSTable
   */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('PricePOS');
  }
}
