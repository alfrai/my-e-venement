<?php

/**
 * PriceTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class PriceTable extends PluginPriceTable
{
  public function createQueryToFindTheMostExpansiveForGauge($gauge_id)
  {
    $q = $this->createQuery('p')
      ->leftJoin('p.Workspaces ws')
      ->leftJoin('ws.Gauges wsg WITH wsg.id = ?', $gauge_id)
      ->leftJoin('ws.Users wsu')
      ->leftJoin('p.PriceGauges pg')
      ->leftJoin('pg.Gauge g WITH g.id = ?', $gauge_id)
      ->leftJoin('p.PriceManifestations pm')
      ->leftJoin('pm.Manifestation m')
      ->leftJoin('m.Gauge mg WITH mg.id = ?', $gauge_id)
      ->andWhere('mg.id IS NOT NULL OR g.id IS NOT NULL')
      ->andWhere('wsg.id IS NOT NULL')
      ->orderBy('pg.value DESC, pm.value DESC')
      ->select('p.*, (CASE WHEN pg.value IS NULL THEN pm.value ELSE pg.value END) AS real_value')
    ;
    return $q;
  }
  
  protected function getCredentials($q, $alias, $override_credentials)
  {
    if ( sfContext::hasInstance() && ($user = sfContext::getInstance()->getUser()) && $user->getId()
      && (!$override_credentials || !$user->isSuperAdmin() && !$user->hasCredential('event-admin-price')) )
      $q->andWhere("$alias.id IN (SELECT up.price_id FROM UserPrice up WHERE up.sf_guard_user_id = ?) OR (SELECT count(up2.price_id) FROM UserPrice up2 WHERE up2.sf_guard_user_id = ?) = 0",array($user->getId(),$user->getId()));
    $q->leftJoin("$alias.Translation pt");
    
    return $q;
  }
  
  public function createQuery($alias = 'p', $override_credentials = true)
  {
    $q = $this->getCredentials(parent::createQuery($alias), $alias, $override_credentials);
    
    if ( $dom = sfConfig::get('project_internals_users_domain', null) )
      $q->leftJoin("$alias.Ranks pr WITH pr.domain ILIKE '%$dom' OR pr.domain = '$dom'");
    else
      $q->leftJoin("$alias.Ranks pr");
      
    $q->orderBy("pr.rank, $alias.id");

    return $q;
  }
  
  public function getPriceList()
  {
    $q = parent::createQuery();
    $alias = $q->getRootAlias();
    
    $q = $this->getCredentials($q, $alias, true)
      ->orderBy("pt.name");
      
    return $q;
  }
  
  public function getEventPrice()
  {
    $q = $this->createQuery();
    $root = $q->getRootAlias();

    $q->leftJoin("$root.PricePOS pos")
      ->andWhere('pos.id IS NULL');

    return $q;
  }
  
  public function getPosPrice()
  {
    $q = $this->createQuery();
    $root = $q->getRootAlias();

    $q->innerJoin("$root.PricePOS pos");

    return $q;
  }
  
  public function fetchOneByName($name)
  {
    $q = $this->createQuery('p')->andWhere('pt.name = ?',$name);
    return $q->fetchOne();
  }
  
  /**
   * Returns an instance of this class.
   *
   * @return object PriceTable
   */
  public static function getInstance()
  {
    return Doctrine_Core::getTable('Price');
  }
}
