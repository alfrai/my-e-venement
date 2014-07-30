<?php

/**
 * SurveyAnswersGroup form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SurveyAnswersGroupForm extends BaseSurveyAnswersGroupForm
{
  /**
   * @see TraceableForm
   */
  public function configure()
  {
    parent::configure();
    $this->widgetSchema['contact_id'] = new sfWidgetFormInputHidden;
    
    $useFields = array('contact_id');
    foreach ( $this->object->Survey->Queries as $query )
    {
      $answer = new SurveyAnswer;
      $answer->Query = $query;
      $this->object->Answers[] = $answer;

      $form = new SurveyAnswerForm($answer);
      $this->embedForm($query->id, $form->forge($query));
      $useFields[] = $query->id;
    }
    $this->useFields($useFields);
  }
}
