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
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
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
    
    $this->widgetSchema   ['date'] = new sfWidgetFormFilterDate(array(
      'from_date' => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'to_date'   => new liWidgetFormJQueryDateText(array('culture' => sfContext::getInstance()->getUser()->getCulture())),
      'template'  => '<span class="from">'.__('From %from_date%').'</span> <span class="to">'.__('to %to_date%').'</span>',
      'with_empty'=> false,
    ));
    $this->validatorSchema['date'] = new sfValidatorDateRange(array(
      'from_date'     => new sfValidatorDate(array(
        'required'    => false,
        'date_output' => 'Y-m-d',
        'with_time'   => false,
      )),
      'to_date'       => new sfValidatorDate(array(
        'required'    => false,
        'date_output' => 'Y-m-d',
        'with_time'   => false,
      )),
      'required' => false,
    ));
    
    $this->values['date'] = '';
    
    parent::configure();
  }
  
  public function addDateColumnQuery(Doctrine_Query $q, $field, $values)
  {
    $a = $q->getRootAlias();
    
    if (!( is_array($values) && $values && $values['from'] && $values['to'] ))
      return $q;
    
    $q->getRoot()->setDebtsListCondition($q, $values);
    return $q;
  }

   public function addAllColumnQuery(Doctrine_Query $q, $field, $values)
   {
     $a = $q->getRootAlias();
     
     if ( !$values )
       $q->andWhere("$a.closed = false");
     
     return $q;
   }

   public function getFields()
   {
     // the position of the "date" record in the array is very important because of this filter special behaviour
     return parent::getFields() + array(
       'date'  => 'Date', // MUST COME FIRST IN THE LIST
       'all'   => 'All',
     );
   }
}
