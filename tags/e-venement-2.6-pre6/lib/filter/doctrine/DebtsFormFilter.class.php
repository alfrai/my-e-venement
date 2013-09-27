<?php

/**
 * Transaction filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class DebtsFormFilter extends TransactionFormFilter
{
  /**
   * @see TraceableFormFilter
   */
  public function configure()
  {
    $this->widgetSchema   ['date'] = new liWidgetFormDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture()));
    $this->validatorSchema['date'] = new sfValidatorDate(array(
      'required' => false,
    ));
    
    $this->widgetSchema   ['all'] = new sfWidgetFormInputCheckbox(array(
      'value_attribute_value' => 1,
    ));
    $this->validatorSchema['all'] = new sfValidatorBoolean(array(
      'required'  => false,
    ));
    
    parent::configure();
  }
  
  public function addDateColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    
    if ( $values )
    {
      TransactionTable::addDebtsListBaseSelect($q);
      $q->addSelect('(SELECT SUM(tck.value)  FROM Ticket tck  WHERE '.TransactionTable::getDebtsListTicketsCondition('tck',$values).') AS outcomes')
        ->addSelect("(SELECT SUM(pp.value)   FROM Payment pp  WHERE pp.transaction_id = t.id AND pp.created_at < '".$values."') AS incomes")
        ->andWhere('((SELECT (CASE WHEN COUNT(tck3.id) = 0 THEN 0 ELSE SUM(tck3.value) END) FROM Ticket tck3 WHERE '.TransactionTable::getDebtsListTicketsCondition('tck3',$values).') - (SELECT (CASE WHEN COUNT(p3.id) = 0 THEN 0 ELSE SUM(p3.value) END) FROM Payment p3 WHERE p3.transaction_id = t.id AND p3.created_at < ?)) != 0',$values);
    }
    
    return $q;
  }

   public function addAllColumnQuery(Doctrine_Query $q, $field, $values)
   {
     $a = $q->getRootAlias();
     
     if ( !$values )
       $q->andWhere('t.closed = false');
     
     return $q;
   }

   public function getFields()
   {
     // the position of the "date" record in the array is very important because of this filter special behaviour
     return array_merge(array(
       'date'  => 'Date',
       'all'   => 'All',
     ),parent::getFields());
   }
}
