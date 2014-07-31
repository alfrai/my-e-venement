<?php

/**
 * Survey form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SurveyForm extends BaseSurveyForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    
    if ( !$this->object->isNew() )
      $this->object->ApplyTo[] = new SurveyApplyTo;
    $this->embedRelation('ApplyTo');
  }
  
  public function doBind(array $values)
  {
    parent::doBind($values);
    
    $cols = array(
      'everywhere', 'date_from', 'date_to',
      'manifestation_id', 'group_id',
      'contact_id', 'organism_id', 'professional_id',
    );
    
    // if an ApplyTo sub-record has no relevant information inside, delete it
    foreach ( $this->values['ApplyTo'] as $key => $at )
    {
      $delete = true;
      foreach ( $cols as $col )
      if ( isset($at[$col]) && $at[$col] )
      {
        $delete = false;
        break;
      }
      
      if ( $delete )
      {
        unset($this->values['ApplyTo'][$key]);
        unset($this->embeddedForms['ApplyTo'][$key]);
        unset($this->object->ApplyTo[$key]);
        unset($this->validatorSchema['ApplyTo'][$key]);
      }
    }
  }
}
