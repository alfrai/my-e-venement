<?php

require_once dirname(__FILE__).'/../lib/surveyGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/surveyGeneratorHelper.class.php';

/**
 * survey actions.
 *
 * @package    e-venement
 * @subpackage survey
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class surveyActions extends autoSurveyActions
{
  public function executeExtractContent(sfWebRequest $request)
  {
    $this->executeEdit($request);
    require(__DIR__.'/extract-content.php');
  }
  public function executeExtract(sfWebRequest $request)
  {
    $this->executeEdit($request);
    require(__DIR__.'/extract.php');
  }
  public function executeAddQuery(sfWebRequest $request)
  {
    $this->redirect('query/new?survey-id='.$request->getParameter('id'));
  }
  
  public function executeShow(sfWebRequest $request)
  {
    parent::executeShow($request);
    $this->form = new SurveyPublicForm($this->survey);
  }
  
  public function executeCommit(sfWebRequest $request)
  {
    $params = $request->getParameter('survey');
    $request->setParameter('id', $params['id']);
    parent::executeShow($request);
    
    // add the lang param, adjusted to the user's culture
    foreach ( $params['answers'] as $id => $param )
    if ( is_array($param) && !(isset($params['answers'][$id]['lang']) && $params['answers'][$id]['lang']) )
      $params['answers'][$id]['lang'] = $this->getUser()->getCulture();
    
    // add the link to the user's contact
    if ( $this->getUser()->getContact() )
      $params['answers']['contact_id'] = $this->getUser()->getContact()->id;
    
    $this->form = new SurveyPublicForm($this->survey);
    $this->form->bind($params);
    if ( $this->form->isValid() )
    {
      $this->getContext()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('success', __('Submission recorded.'));
      
      $object = $this->form->save();
      $this->redirect('survey/show?id='.$this->survey->id);
    }
    
    $this->setTemplate('show');
  }
  
  public function executeSearch(sfWebRequest $request)
  {
    self::executeIndex($request);
    $table = Doctrine::getTable('Survey');
    
    $search = $this->sanitizeSearch($request->getParameter('s'));
    $this->pager->setQuery($table->search($search.'*',$this->pager->getQuery()));
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
    
    $this->setTemplate('index');
  }
  
  public static function sanitizeSearch($search)
  {
    $nb = mb_strlen($search);
    $charset = sfConfig::get('software_internals_charset');
    $transliterate = sfConfig::get('software_internals_transliterate',array());
    
    $search = str_replace(preg_split('//u', $transliterate['from'], -1), preg_split('//u', $transliterate['to'], -1), $search);
    $search = str_replace(array('@','.','-','+',',',"'"),' ',$search);
    $search = mb_strtolower(iconv($charset['db'],$charset['ascii'], mb_substr($search,$nb-1,$nb) == '*' ? mb_substr($search,0,$nb-1) : $search));
    return $search;
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $this->filters = true; // hack Beaulieu du 30/09/2013 à valider avant commit
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $q = Doctrine::getTable('Survey')
      ->createQuery()
      ->orderBy('name')
      ->limit($request->getParameter('limit'));
    $q = Doctrine_Core::getTable('Survey')
      ->search($search.'*',$q);
    $request = $q->execute()->getData();

    $surveys = array();
    foreach ( $request as $survey )
      $surveys[$survey->id] = (string) $survey;
    
    return $this->renderText(json_encode($surveys));
  }
}
