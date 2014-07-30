<?php

require_once dirname(__FILE__).'/../lib/answerGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/answerGeneratorHelper.class.php';

/**
 * answer actions.
 *
 * @package    e-venement
 * @subpackage answer
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class answerActions extends autoAnswerActions
{
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    $this->redirect('survey/edit?id='.$this->survey_answer->Query->survey_id);
  }
}
