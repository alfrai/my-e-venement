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
    
    parent::configure();
  }
  
  public function addDateColumnQuery(Doctrine_Query $query, $field, $values)
  {
    $a = $query->getRootAlias();
    
    TransactionTable::addDebtsListBaseSelect($query);
    $query
      ->addSelect('(SELECT SUM(tck.value) FROM Ticket tck WHERE '.TransactionTable::getDebtsListTicketsCondition('tck',$values).') AS outcomes')
      ->addSelect("(SELECT SUM(p.value)   FROM Payment p  WHERE p.transaction_id = t.id AND p.created_at < '".$values."') AS incomes")
      ->where('((SELECT SUM(tck2.value)  FROM Ticket tck2 WHERE '.TransactionTable::getDebtsListTicketsCondition('tck2',$values).') - (SELECT sum(p2.value) FROM Payment p2 WHERE p2.transaction_id = t.id AND p2.created_at < ?)) != 0',$values);
    
    return $query;
  }

  public function getFields()
  {
    return array_merge(array(
      'date' => 'Date',
    ), parent::getFields());
  }
}
