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
    
    $subform = new sfForm;
    $at = false;
    foreach ( $this->object->ApplyTo as $at )
      $subform->embedForm('at-'.$at->id, new SurveyApplyToForm($at));
    if (!( $at && $at->isNew() ))
    {
      $at = new SurveyApplyTo;
      $at->Survey = $this->object;
      $subform->embedForm('at-new', new SurveyApplyToForm($at));
    }
    $this->embedForm('ApplyTo', $subform);
  }
}
