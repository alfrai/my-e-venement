<?php

/**
 * SurveyQuery form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SurveyQueryForm extends BaseSurveyQueryForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('I18N'));
    parent::configure();
    
    $this->widgetSchema['survey_id'] = new sfWidgetFormInputHidden;
    
    if ( !$this->object->isNew() )
      $this->object->Options[] = new SurveyQueryOption;
    $this->embedRelation('Options');
    
    $choices = sfConfig::get('app_query_widgets', array(
      'sfWidgetFormTextarea' => __('Text'),
      'sfWidgetFormInputText' => __('Line'),
      'sfWidgetFormInputCheckbox' => __('Checkbox'),
    ));
    foreach ( $choices as $widget => $string )
      $choices[$widget] = __($string);
    $choices[''] = '';
    asort($choices);
    
    $this->widgetSchema['type'] = new sfWidgetFormChoice(array(
      'choices' => $choices,
    ));
  }
  
  public function doBind(array $values)
  {
    $cultures = sfConfig::get('project_internals_cultures', array('fr' => 'Français'));
    if ( isset($values['Options']) && is_array($values['Options']) )
    {
      foreach ( $values['Options'] as $oid => $option )
      {
        $continue = true;
        if (!( isset($option['value']) && $option['value'] ))
          $continue = false;
        
        if ( $continue )
        foreach ( $cultures as $culture => $lang )
        if (!( isset($option[$culture]['name']) && $option[$culture]['name'] ))
        {
          $continue = false;
          break;
        }
        
        if ( !$continue )
        {
          unset($values['Options'][$oid]);
          unset($this->embeddedForms['Options'][$oid]);
          unset($this->object->Options[$oid]);
          unset($this->validatorSchema['Options'][$oid]);
        }
      }
      
      if ( count($values['Options']) == 0 )
        unset($values['Options']);
    }
    
    parent::doBind($values);
  }
}
