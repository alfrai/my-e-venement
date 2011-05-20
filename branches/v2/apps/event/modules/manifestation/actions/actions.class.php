<?php
/**********************************************************************************
*
*	    This file is part of e-venement.
*
*    e-venement is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License as published by
*    the Free Software Foundation; either version 2 of the License.
*
*    e-venement is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with e-venement; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
*    Copyright (c) 2006-2011 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2011 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

require_once dirname(__FILE__).'/../lib/manifestationGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/manifestationGeneratorHelper.class.php';

/**
 * manifestation actions.
 *
 * @package    e-venement
 * @subpackage manifestation
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class manifestationActions extends autoManifestationActions
{
  public function executeNew(sfWebRequest $request)
  {
    parent::executeNew($request);
    
    if ( $request->getParameter('event') )
    {
      $event = Doctrine::getTable('Event')->findOneBySlug($request->getParameter('event'));
      if ( $event->id )
      {
        $this->form->getWidget('event_id')->setDefault($event->id);
        $this->form->getObject()->event_id = $event->id;
      }
    }
    if ( $request->getParameter('location') )
    {
      $location = Doctrine::getTable('Location')->findOneBySlug($request->getParameter('location'));
      if ( $location->id )
      $this->form->getWidget('location_id')->setDefault($location->id);
    }
  }
  
  /*
   * overriding that to redirect the user to the parent event/location's screen
   * instead of the list of manifestations
   *
   */
  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
    if ($form->isValid())
    {
      // "credentials"
      $form->updateObject($request->getParameter($form->getName()), $request->getFiles($form->getName()));
      if ( !in_array($form->getObject()->Event->meta_event_id,array_keys($this->getUser()->getMetaEventsCredentials())) )
      {
        $this->getUser()->setFlash('error', "You don't have permissions to modify this event.");
        $this->redirect('@manifestation_new');
      }
      
      $notice = $form->getObject()->isNew() ? "The item was created successfully. Don't forget to update prices if necessary." : 'The item was updated successfully.';
      
      $manifestation = $form->save();

      $this->dispatcher->notify(new sfEvent($this, 'admin.save_object', array('object' => $manifestation)));

      if ($request->hasParameter('_save_and_add'))
      {
        $this->getUser()->setFlash('notice', $notice.' You can add another one below.');

        $this->redirect('@manifestation_new');
      }
      else
      {
        $this->getUser()->setFlash('notice', $notice);
        
        $this->redirect(array('sf_route' => 'manifestation_edit', 'sf_subject' => $manifestation));
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }
  }

  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('@event');
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfContext::getInstance()->getConfiguration()->charset;
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $e = Doctrine_Core::getTable('Event')->search($search.'*',Doctrine::getTable('Event')->createQuery());
    
    $eids = array();
    foreach ( $e->execute() as $event )
      $eids[] = $event['id'];
    
    $q = Doctrine::getTable('Manifestation')
      ->createQuery()
      ->andWhereIn('event_id',$eids)
      ->orderBy('happens_at')
      ->limit($request->getParameter('limit'));
    $q = EventFormFilter::addCredentialsQueryPart($q);
    $request = $q->execute()->getData();
    
    $organisms = array();
    foreach ( $request as $organism )
      $organisms[$organism->id] = (string) $organism;
    
    return $this->renderText(json_encode($organisms));
  }

  public function executeEventList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('manifestation','index');
    
    $this->event_id = $request->getParameter('id');
    
    $this->pager = $this->configuration->getPager('Contact');
    $this->pager->setMaxPerPage(5);
    $this->pager->setQuery(
      EventFormFilter::addCredentialsQueryPart(
        Doctrine::getTable('Manifestation')->createQueryByEventId($this->event_id)
        ->select('*, happens_at > NOW() AS after, (CASE WHEN ( happens_at < NOW() ) THEN NOW()-happens_at ELSE happens_at-NOW() END) AS before')
        ->orderBy('after DESC, before')
    ));
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
  }
  
  protected function securityAccessFiltering(sfWebRequest $request)
  {
    if ( intval($request->getParameter('id')).'' != ''.$request->getParameter('id') )
      return;
    
    if ( !in_array($this->getRoute()->getObject()->Event->meta_event_id,array_keys($this->getUser()->getMetaEventsCredentials())) )
    {
      $this->getUser()->setFlash('error',"You can't access this object, you don't have the required permissions.");
      $this->redirect('@event');
    }
  }
  
  public function executeEdit(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeEdit($request);
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeShow($request);
  }
}
