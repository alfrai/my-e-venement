<?php

require_once dirname(__FILE__).'/../lib/eventGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/eventGeneratorHelper.class.php';

/**
 * event actions.
 *
 * @package    symfony
 * @subpackage event
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class eventActions extends autoEventActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $cultures = array_keys(sfConfig::get('project_internals_cultures', array('fr' => 'Français')));
    
    // culture defined explicitly
    if ( $request->hasParameter('culture') && in_array($request->getParameter('culture'), $cultures) )
      $this->getUser()->setCulture($request->getParameter('culture'));
    else
    {
      // all the browser's languages
      $user_langs = array();
      foreach ( $request->getLanguages() as $lang )
      if ( !isset($user_lang[substr($lang, 0, 2)]) )
        $user_langs[substr($lang, 0, 2)] = $lang;
      
      // comparing to the supported languages
      $done = false;
      foreach ( $user_langs as $culture => $lang )
      if ( in_array($culture, $cultures) )
      {
        $done = $culture;
        $this->getUser()->setCulture($culture);
        break;
      }
      
      // culture by default
      if ( !$done )
        $this->getUser()->setCulture($cultures[0]);
    }
    
    // continue normal operations
    parent::executeIndex($request);
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->event = $this->getRoute()->getObject();
    $this->getUser()->getAttributeHolder()->remove('manifestation.filters');
    $this->getUser()->setAttribute('manifestation.filters', array('event_id' => $this->event->id), 'admin_module');
    $this->redirect('manifestation/index');
  }
  public function executeBatchDelete(sfWebRequest $request)
  {
    $this->redirect('event/index');
  }
  public function executeCreate(sfWebRequest $request)
  {
    $this->executeBatchDelete($request);
  }
  public function executeNew(sfWebRequest $request)
  {
    $this->executeBatchDelete($request);
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->executeEdit($request);
  }
}
