<?php

/**
 * Invoice filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class InvoiceFormFilter extends BaseInvoiceFormFilter
{
  /**
   * @see AccountingFormFilter
   */
  public function configure()
  {
    parent::configure();
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    $this->widgetSchema   ['id'] = new sfWidgetFormInput;
    $this->validatorSchema['id'] = new sfValidatorDoctrineChoice(array(
      'model'    => 'Invoice',
      'required' => false,
    ));
    
    $this->widgetSchema['created_at'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormDateText(),
      'to_date'   => new liWidgetFormDateText(),
      'template'  => __('<span class="dates"><span>from %from_date%</span> <span>to %to_date%</span></span>'),
    ));
    
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputText();
    
    $this->widgetSchema   ['tickets_value'] = new sfWidgetFormInputText();
    $this->validatorSchema['tickets_value'] = new sfValidatorInteger(array(
      'required' => false,
    ));
  }
  public function setup()
  {
    $this->noTimestampableUnset = true;
    parent::setup();
  }
  public function getFields()
  {
    return array_merge(array(
      'tickets_value' => 'TicketsValue',
      'id' => 'Id',
    ), parent::getFields());
  }
  
  public function addIdColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $fieldName = $this->getFieldName($field);
    if ( $value )
    {
      $a = $query->getRootAlias();
      $query->andWhere("$a.$fieldName = ?",$value);
    }
    return $query;
    
    //return $this->addNumberQuery($query, $field, $value);
  }
  public function addTicketsValueColumnQuery(Doctrine_Query $query, $field, $value)
  {
    $fieldName = $this->getFieldName($field);
    if ( $value )
    {
      $a = $query->getRootAlias();
      $query->andWhere("(SELECT sum(value) FROM Ticket tck LEFT JOIN tck.Transaction tr WHERE tr.id = $a.transaction_id AND tck.duplicating IS NULL AND (printed OR integrated)) = ?",$value);
    }
    
    return $query;
  }
}
