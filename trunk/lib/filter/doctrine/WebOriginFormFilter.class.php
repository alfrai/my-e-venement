<?php

/**
 * WebOrigin filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class WebOriginFormFilter extends BaseWebOriginFormFilter
{
  protected $noTimestampableUnset = true;
  protected $embedded = false;
  const SQL_REGEX_URL_FORMAT = '^([a-zA-Z]+)://(([a-z0-9-]+\.)+[a-z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(:[0-9]+){0,1}(/{0,1}|/.*)$';
  const SQL_REGEX_DOMAIN_FORMAT = '~^(.{0,1}([a-z0-9-]+\.)+[a-z]{2,6}|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(:[0-9]+)?~ix';
  
  /**
   * @see TraceableFormFilter
   */
  public function configure()
  {
    parent::configure();
    
    // recorded filters
    $this->widgetSchema   ['recorded_filters'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'Filter',
      'order_by'  => array('name',''),
      'query'     => $q = Doctrine::getTable('Filter')->createQuery('f')->andWhere('type = ?', 'web_origin'),
      'multiple'  => true,
    ));
    $this->validatorSchema['recorded_filters'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'Filter',
      'query'     => $q,
      'multiple'  => true,
      'required'  => false,
    ));
    if ( sfContext::hasInstance() && sfContext::getInstance()->getUser()->getId() )
      $q->andWhere('f.sf_guard_user_id = ?', sfContext::getInstance()->getUser()->getId());
    $this->widgetSchema   ['not_recorded_filters'] = new sfWidgetFormDoctrineChoice(array(
      'model'     => 'Filter',
      'order_by'  => array('name',''),
      'query'     => $q,
      'multiple'  => true,
    ));
    $this->validatorSchema['not_recorded_filters'] = new sfValidatorDoctrineChoice(array(
      'model'     => 'Filter',
      'query'     => $q,
      'multiple'  => true,
      'required'  => false,
    ));
    
    $this->widgetSchema   ['referer_domain'] = new sfWidgetFormInputText;
    $this->validatorSchema['referer_domain'] = new sfValidatorRegex(array(
      'pattern'  => self::SQL_REGEX_DOMAIN_FORMAT,
      'required' => false,
    ));
    $this->widgetSchema   ['done_deal'] = new sfWidgetFormInputCheckbox(array(
      'value_attribute_value' => 1,
    ));
    $this->validatorSchema['done_deal'] = new sfValidatorBoolean(array(
      'true_values' => array(1),
      'required' => false,
    ));
    
    $this->widgetSchema   ['sf_guard_user_id']->setOption('multiple', true)->setOption('order_by', array('username',''))->setOption('add_empty', false);
    $this->validatorSchema['sf_guard_user_id']->setOption('multiple', true);
    
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInput;
    
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $q = 'SELECT DISTINCT campaign, campaign IS NULL FROM web_origin ORDER BY campaign IS NULL DESC, campaign';
    $stmt = $pdo->prepare($q);
    $stmt->execute();
    $campaigns = $stmt->fetchAll();
    $choices = array();
    foreach ( $campaigns as $c )
      $choices[$c['campaign'] ? $c['campaign'] : -1] = $c['campaign'];
    $this->widgetSchema   ['campaign'] = new sfWidgetFormChoice(array(
      'choices' => $choices,
      'multiple' => true,
    ));
  }
  
  public function getFields()
  {
    return parent::getFields() + array(
      'referer_domain'    => 'RefererDomain',
      'done_deal'         => 'DoneDeal',
      'recorded_filters'  => 'RecordedFilters',
      'not_recorded_filters'  => 'NotRecordedFilters',
    );
  }
  
  public function isEmbedded($bool = NULL)
  {
    if ( !is_null($bool) )
      $this->embedded = $bool;
    return $this->embedded;
  }
  
  public function addNotRecordedFiltersColumnQuery(Doctrine_Query $q, $field, $values)
  { return $this->addRecordedFiltersColumnQuery($q, $field, $values, true); }
  public function addRecordedFiltersColumnQuery(Doctrine_Query $q, $field, $values, $not = false)
  {
    if ( $this->isEmbedded() || !$values )
      return $q;
    
    $q2 = Doctrine::getTable('Filter')->createQuery('f')
      ->andWhere('f.type = ?', 'web_origin')
      ->andWhereIn('f.id', $values);
    if ( sfContext::hasInstance() && sfContext::getInstance()->getUser()->getId() )
      $q2->andWhere('f.sf_guard_user_id = ?', sfContext::getInstance()->getUser()->getId());
    
    $ids = array();
    foreach ( $q2->execute() as $filter )
    {
      $form = new WebOriginFormFilter;
      $form->isEmbedded(true);
      
      $query = $form->buildQuery(unserialize($filter->filter));
      $a = $query->getRootAlias();
      $query->select("$a.id");
      
      foreach ( $query->fetchArray() as $rec )
        $ids[] = $rec['id'];
    }
    
    $a = $q->getRootAlias();
    if ( $not )
      $q->andWhereNotIn("$a.id", $ids);
    else
      $q->andWhereIn("$a.id", $ids);
    
    return $q;
  }
  
  public function addUserAgentColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    $a = $q->getRootAlias();
    $q->andWhere("$a.$field ILIKE ?", '%'.$value['text'].'%');
    
    return $q;
  }
  
  public function addRefererDomainColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    $q->andWhere("regexp_replace(wo.referer, '".self::SQL_REGEX_URL_FORMAT."', ?, 'ix') ILIKE ?", array('\2', '%'.$value));
    return $q;
  }
  public function addDoneDealColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    $q->andWhere('o.id IS NOT NULL OR p.id IS NOT NULL');
    return $q;
  }
  
  public function addFirstPageColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value['text'] )
      return $q;
    
    $q->andWhere('wo.first_page ILIKE ?', '%'.$value['text']);
    return $q;
  }
  public function addIpaddressColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value['text'] )
      return $q;
    
    $q->andWhere('wo.ipaddress ILIKE ?', $value['text'].'%');
    return $q;
  }
  public function addCampaignColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    
    $noc = false;
    foreach ( $values as $i => $value )
    if ( $value == '-1' )
    {
      $noc = true;
      unset($values[$i]);
    }
    
    $q->andWhere('(TRUE');
    if ( $noc )
      $q->andWhere('wo.campaign IS NULL OR wo.campaign = ?', '');
    
    
    if ( $values )
    {
      if ( $noc ) $q->orWhere('TRUE');
      $q->andWhereIn('wo.campaign', $values);
    }
    $q->andWhere('TRUE)');
    
    // pfiiiou
    return $q;
  }
}
