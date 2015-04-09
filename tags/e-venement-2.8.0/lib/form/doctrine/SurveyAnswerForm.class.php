<?php

/**
 * SurveyAnswer form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SurveyAnswerForm extends BaseSurveyAnswerForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    $this->widgetSchema['survey_query_id'] = new sfWidgetFormInputHidden;
    $this->widgetSchema['lang'] = new sfWidgetFormInputHidden;
  }
  
  public function forge(SurveyQuery $query)
  {
    $this->widgetSchema   ['value'] = $query->getWidget();
    $this->validatorSchema['value'] = $query->getValidator();
    
    $this->useFields(array(
      'value',
      'survey_query_id',
      'lang',
    ));
    
    return $this;
  }
  
  public function doSave($con = null)
  {
    if ( !trim($this->object->value) )
      return;
    parent::doSave($con);
  }
}
