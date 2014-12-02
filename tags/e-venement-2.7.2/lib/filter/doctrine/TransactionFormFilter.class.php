<?php

/**
 * Transaction filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class TransactionFormFilter extends BaseTransactionFormFilter
{
  /**
   * @see TraceableFormFilter
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('CrossAppLink'));
    $this->widgetSchema['organism_id'] = new sfWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Organism',
      'url'   => cross_app_url_for('rp','organism/ajax'),
    ));
    $this->validatorSchema['organism_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Organism',
      'required' => false,
    ));
    
    $this->widgetSchema   ['name'] = new sfWidgetFormInputText();
    $this->validatorSchema['name'] = new sfValidatorString(array(
      'required' => false,
    ));
    
    $this->widgetSchema   ['city'] = new sfWidgetFormInputText();
    $this->validatorSchema['city'] = new sfValidatorString(array(
      'required' => false,
    ));
    
    parent::configure();
  }
  public function setup()
  {
    $this->noTimestampableUnset = true;
    parent::setup();
  }
  
  public function addOrganismIdColumnQuery(Doctrine_Query $query, $field, $values)
  {
    $a = $query->getRootAlias();
    if ( !$query->contains("LEFT JOIN $a.Professional p") )
      $query->leftJoin("$a.Professional p");
    
    $query->andWhere("p.organism_id = ?",$values);
    
    return $query;
  }
  public function addNameColumnQuery(Doctrine_Query $query, $field, $values)
  {
    $a = $query->getRootAlias();
    
    if ( !$query->contains("LEFT JOIN $a.Professional p") )
      $query->leftJoin("$a.Professional p");
    if ( !$query->contains("LEFT JOIN $a.Contact c") )
      $query->leftJoin("$a.Contact c");
    if ( !$query->contains("LEFT JOIN p.Organism o") )
      $query->leftJoin("p.Organism o");
    
    $query->andWhere('LOWER(o.name) LIKE LOWER(?) OR LOWER(c.name) LIKE LOWER(?) OR LOWER(c.firstname) LIKE LOWER(?)',array(
        $values.'%',
        $values.'%',
        $values.'%',
      ));
    
    return $query;
  }
  public function addCityColumnQuery(Doctrine_Query $query, $field, $values)
  {
    $a = $query->getRootAlias();
    
    if ( !$query->contains("LEFT JOIN $a.Professional p") )
      $query->leftJoin("$a.Professional p");
    if ( !$query->contains("LEFT JOIN $a.Contact c") )
      $query->leftJoin("$a.Contact c");
    if ( !$query->contains("LEFT JOIN p.Organism o") )
      $query->leftJoin("p.Organism o");
    
    $query->andWhere('LOWER(o.city) LIKE LOWER(?) OR LOWER(c.city) LIKE LOWER(?)',array(
        $values.'%',
        $values.'%',
      ));
    
    return $query;
  }

  public function getFields()
  {
    return array_merge(array(
      'organism_id' => 'Organism Id',
      'name'        => 'Name',
      'city'        => 'City',
    ), parent::getFields());
  }
}
