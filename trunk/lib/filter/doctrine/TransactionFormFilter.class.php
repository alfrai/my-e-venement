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
    $this->widgetSchema['organism_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
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
    
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputText();
    
    $this->widgetSchema   ['created_by'] = new sfWidgetFormDoctrineChoice(array(
      'model' => 'sfGuardUser',
      'add_empty' => true,
    ));
    $this->validatorSchema['created_by'] = new sfValidatorDoctrineChoice(array(
      'model'    => 'sfGuardUser',
      'required' => false,
    ));
    
    $this->widgetSchema   ['manifestation_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Manifestation',
      'url'   => cross_app_url_for('event','manifestation/ajax'),
    ));
    $this->validatorSchema['manifestation_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Manifestation',
      'required' => false,
    ));
    
    $this->widgetSchema   ['hold_id'] = new liWidgetFormDoctrineJQueryAutocompleter(array(
      'model' => 'Hold',
      'url'   => cross_app_url_for('event','hold/ajax'),
    ));
    $this->validatorSchema['hold_id'] = new sfValidatorDoctrineChoice(array(
      'model' => 'Hold',
      'required' => false,
    ));
    
    $this->widgetSchema   ['print_state'] = new sfWidgetFormChoice(array(
      'choices' => $choices = array(
        '' => '',
        'not-empty'   => 'not empty',
        'empty'       => 'empty',
        'not-printed' => 'not printed',
        'printed'     => 'printed',
        'ordered'     => 'ordered',
      ),
    ));
    $this->validatorSchema['print_state'] = new sfValidatorChoice(array(
      'choices' => $choices,
      'required' => false,
    ));
    
    parent::configure();
  }
  public function setup()
  {
    $this->noTimestampableUnset = true;
    parent::setup();
  }
  
  public function addHoldIdColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    
    $t = $q->getRootAlias();
    $q->leftJoin("$t.HoldTransaction ht")
      ->andWhere('ht.hold_id = ?', $values);
    
    return $q;
  }
  public function addManifestationIdColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    
    $t = $q->getRootAlias();
    $q->andWhere('tck.manifestation_id = ?', $values);
    
    return $q;
  }
    
  public function addCreatedByColumnQuery(Doctrine_Query $q, $field, $values)
  {
    if ( !$values )
      return $q;
    if ( !is_array($values) )
      $values = array($values);
    
    $a = $q->getRootAlias();
    $q->leftJoin("$a.Version v WITH version = 1")
      ->andWhereIn('v.sf_guard_user_id', $values);
    
    return $q;
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
  public function addPrintStateColumnQuery(Doctrine_Query $q, $field, $value)
  {
    $t = $q->getRootAlias();
    
    if ( !$q->contains("LEFT JOIN $t.Order order") )
      $q->leftJoin("$t.Order order");
    if ( !$q->contains("LEFT JOIN $t.Invoice i") )
      $q->leftJoin("$t.Invoice i");
    if ( !$q->contains("LEFT JOIN $t.Payments pay") )
      $q->leftJoin("$t.Payments pay");
    
    switch ( $value ) {
    case 'ordered':
      $q->andWhere('order.id IS NOT NULL AND tck.printed_at IS NULL AND tck.integrated_at IS NULL');
      break;
    case 'printed':
      $q->andWhere('tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL');
      break;
    case 'not-printed':
      $q->andWhere('order.id IS NULL AND tck.printed_at IS NULL AND tck.integrated_at IS NULL');
      break;
    case 'not-empty':
      $q->andWhere('(TRUE')
        ->andWhere('tck.id IS NOT NULL')
        ->orWhere('order.id IS NOT NULL')
        ->orWhere('i.id IS NOT NULL')
        ->orWhere('pay.id IS NOT NULL')
        ->andWhere('TRUE)')
      ;
      break;
    case 'empty':
      $q->andWhere('tck.id IS NULL')
        ->andWhere('order.id IS NULL')
        ->andWhere('i.id IS NULL')
        ->andWhere('pay.id IS NULL')
      ;
      break;
    }
    
    return $q;
  }

  public function getFields()
  {
    return array_merge(array(
      'organism_id' => 'OrganismId',
      'name'        => 'Name',
      'city'        => 'City',
      'print_state' => 'PrintState',
    ), parent::getFields());
  }
}
