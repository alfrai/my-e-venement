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
    
    $group = new SurveyAnswersGroup;
    $this->object->AnswersGroups[] = $group;
    $this->embedForm('answers', new SurveyAnswersGroupForm($group));
    $this->useFields(array('answers'));
  }
}
