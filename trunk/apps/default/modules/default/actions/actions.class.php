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
    $q = Doctrine::getTable('Email')->createQuery('e');
    $q->orderBy("e.updated_at DESC, e.created_at DESC")
      ->limit(5);
    $this->emails = $q->execute();
    
    $q = Doctrine::getTable('Contact')->createQuery('c');
    $q->orderBy("c.updated_at DESC, c.created_at DESC")
      ->limit(5);
    $this->contacts = $q->execute();
    
    $q = Doctrine::getTable('Organism')->createQuery('o');
    $q->orderBy("o.updated_at DESC, o.created_at DESC")
      ->limit(5);
    $this->organisms = $q->execute();
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m');
    $q->orderBy("m.happens_at")
      ->andWhere("m.happens_at > now()")
      ->limit(5);
    $this->manifestations = $q->execute();
  }
  
  public function executeTest(sfWebRequest $request)
  {
    print_r($this->getUser()->getCredentials());
    echo $this->getUser()->hasCredential('pr-contact-edit') ? 'ok' : 'ko';
    return sfView::NONE;
  }
  
  public function executeError(sfWebRequest $request)
  {
  }
  public function executeError404(sfWebRequest $request)
  {
  }
}
