<?php

/**
 * Order filter form.
 *
 * @package    e-venement
 * @subpackage filter
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class OrderFormFilter extends BaseOrderFormFilter
{
  /**
   * @see AccountingFormFilter
   */
  public function configure()
  {
    parent::configure();
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    $this->widgetSchema['created_at'] = new sfWidgetFormDateRange(array(
      'from_date' => new liWidgetFormDateText(array('culture' => 'fr')),
      'to_date'   => new liWidgetFormDateText(array('culture' => 'fr')),
      'template'  => __('<span class="dates"><span>from %from_date%</span> <span>to %to_date%</span>', null, 'sf_admin'),
    ));
    
    $this->widgetSchema['transaction_id'] = new sfWidgetFormInputText();
    
    $this->widgetSchema   ['manifestation_happens_at'] = new sfWidgetFormFilterDate(array(
      'from_date' => new liWidgetFormDateText(array('culture' => 'fr')),
      'to_date'   => new liWidgetFormDateText(array('culture' => 'fr')),
      'template'  => __('<span class="dates"><span>from %from_date%</span> <span>to %to_date%</span>', null, 'sf_admin'),
    ));
    $this->validatorSchema['manifestation_happens_at'] = new sfValidatorDateRange(array(
      'from_date' => new sfValidatorDate,
      'to_date'   => new sfValidatorDate,
      'required'  => false,
    ));
    
    $this->widgetSchema   ['event_name'] = new sfWidgetFormInput;
    $this->validatorSchema['event_name'] = new sfValidatorString(array('required' => false));
  }
  public function setup()
  {
    $this->noTimestampableUnset = true;
    parent::setup();
  }
  public function getFields()
  {
    $fields = parent::getFields();
    $fields['event_name'] = 'EventName';
    $fields['manifestation_happens_at'] = 'ManifestationHappensAt';
    return $fields;
  }
  
  public function addEventNameColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if ( !trim($value) )
      return $q;
    
    $q->andWhere('e.name ILIKE ?',$value.'%');
    
    return $q;
  }
  
  public function addManifestationHappensAtColumnQuery(Doctrine_Query $q, $field, $value)
  {
    if (!( $value && is_array($value) && trim($value['from']) && trim($value['to']) ))
      return $q;
    
    $q->andWhere('m.happens_at >= ?', $value['from'])
      ->andWhere('m.happens_at <= ?', $value['to']);
    
    return $q;
  }
}
