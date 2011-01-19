<?php

/**
 * default actions.
 *
 * @package    e-venement
 * @subpackage default
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class defaultActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $q = Doctrine::getTable('Email')->createQuery();
    $a = $q->getRootAlias();
    $q->orderBy("$a.updated_at DESC, $a.created_at DESC")
      ->limit(5);
    $this->emails = $q->execute();
    
    $q = Doctrine::getTable('Contact')->createQuery();
    $a = $q->getRootAlias();
    $q->orderBy("$a.updated_at DESC, $a.created_at DESC")
      ->limit(5);
    $this->contacts = $q->execute();
    
    $q = Doctrine::getTable('Organism')->createQuery();
    $q->orderBy("$a.updated_at DESC, $a.created_at DESC")
      ->limit(5);
    $this->organisms = $q->execute();
  }
  
  public function executeTest(sfWebRequest $request)
  {
    print_r($this->getUser()->getCredentials());
    echo $this->getUser()->hasCredential('pr-contact-edit') ? 'ok' : 'ko';
    return sfView::NONE;
  }
}
