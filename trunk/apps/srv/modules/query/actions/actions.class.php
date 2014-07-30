<?php

require_once dirname(__FILE__).'/../lib/queryGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/queryGeneratorHelper.class.php';

/**
 * query actions.
 *
 * @package    e-venement
 * @subpackage query
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class queryActions extends autoQueryActions
{
  public function executeNew(sfWebRequest $request)
  {
    parent::executeNew($request);
    if ( $sid = $request->getParameter('survey-id', false) )
      $this->form->setDefault('survey_id', $sid);
  }
  
  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->dispatcher->notify(new sfEvent($this, 'admin.delete_object', array('object' => $this->getRoute()->getObject())));
    
    $query = $this->getRoute()->getObject();
    $survey_id = $query->survey_id;
    $query->delete();

    $this->getUser()->setFlash('notice', 'The item was deleted successfully.');

    $this->redirect('survey/edit?id='.$survey_id);
  }
  
  public function executeBackToSurvey(sfWebRequest $request)
  {
    if ( $request->hasParameter('id') )
    {
      $query = Doctrine::getTable('SurveyQuery')->findOneById($request->getParameter('id'));
      $this->redirect('survey/edit?id='.$query->survey_id);
    }
    else
      $this->redirect('@survey');
  }
}
