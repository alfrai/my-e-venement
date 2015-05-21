<?php

/**
 * HoldTransaction filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class HoldTransactionFormFilter extends BaseHoldTransactionFormFilter
{
  public function configure()
  {
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputText;
  }
  
  /*
  public function addHoldIdColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !$value )
      return $q;
    
    $a = $q->getRootAlias();
    $q->select('*');
    $q->leftJoin("$a.Hold h")
      ->andWhere('(TRUE')
      ->andWhere('h.id = ?', $value) // first level
      ->orWhere('h.id = (SELECT hh.next FROM Hold hh WHERE hh.id = ?)', $value) // first "next"
      ->orWhere('h.id = (SELECT hh2.next FROM Hold hh2 WHERE hh2.id = ((SELECT hhh2.next FROM Hold hhh2 WHERE hhh2.id = ?)))', $value) // second "next"
      ->orWhere('h.id = (SELECT hh3.next FROM Hold hh3 WHERE hh3.id = ((SELECT hhh3.next FROM Hold hhh3 WHERE hhh3.id = ((SELECT hhhh3.next FROM Hold hhhh3 WHERE hhhh3.id = ?)))))', $value) // fourth "next"
      ->andWhere('TRUE)') // that's it, stop at 4 levels
    ;
    return $q;
  }
  */
}
