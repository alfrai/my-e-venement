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
  protected $special_filters = array();
  
  public function executeEdit(sfWebRequest $request)
  {
    parent::executeEdit($request);
    $this->redirect('survey/edit?id='.$this->survey_answer->Query->survey_id);
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    foreach ( $request->getParameter('filters', array()) as $field => $value )
    if ( $field && $value )
    {
      $this->special_filters[$field] = $value;
      if ( !$request->getParameter('page') )
        $this->setPage(1);
    }
    parent::executeIndex($request);
  }
  
  protected function getFilters()
  {
    if ( !$this->special_filters )
      return parent::getFilters();
    return $this->special_filters;
  }
  
  protected function getSort()
  {
    if ( !$this->special_filters )
      return parent::getSort();
    return array('survey_answers_group_id, q.rank', '');
  }
}
