<?php

/**
 * Survey form.
 *
 * @package    e-venement
 * @subpackage form
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SurveyPublicForm extends SurveyForm
{
  public function configure()
  {
    parent::configure();
    
    $useFields = array();
    foreach ( $this->object->Queries as $query )
    {
      $answer = new SurveyAnswer;
      $answer->survey_query_id = $query->id;
      
      $form = new SurveyAnswerForm($answer);
      $this->embedForm($query->id, $form->forge($query));
      $useFields[] = $query->id;
    }
    
    $this->useFields($useFields);
  }
}
