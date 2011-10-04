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
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
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
      
      $notice = __($form->getObject()->isNew() ? "The item was created successfully. Don't forget to update prices if necessary." : 'The item was updated successfully.');
      
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
        ->select('*, g.*, l.*, tck.*, happens_at > NOW() AS after, (CASE WHEN ( happens_at < NOW() ) THEN NOW()-happens_at ELSE happens_at-NOW() END) AS before')
        ->leftJoin('m.Tickets tck')
        ->orderBy('after DESC, before')
    ));
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
  }
  
  public function executeTemplating(sfWebRequest $request)
  {
    $this->form = new ManifestationTemplatingForm();
    
    $template = $request->getParameter('template');
    if ( $template )
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
      $this->form->bind($template);
      if ( $this->form->isValid() )
      {
        $this->form->save();
        $this->getUser()->setFlash('notice',__('The template has been applied correctly.'));
        $this->redirect('manifestation/templating');
      }
      else
      {
        $this->getUser()->setFlash('error',__('The template has not been applied correctly !'));
      }
    }
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
  
  public function executeDelete(sfWebRequest $request)
  {
    try {
      $this->securityAccessFiltering($request);
      parent::executeDelete($request);
    }
    catch ( Doctrine_Connection_Exception $e )
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error',__("Deleting this object has been canceled because of remaining links to externals (like tickets)."));
      $this->redirect('manifestation/show?id='.$this->getRoute()->getObject()->id);
    }
  }
  public function executeEdit(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeEdit($request);
    $this->form->prices = $this->getPrices();
    $this->form->spectators = $this->getSpectators();
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeShow($request);
    $this->form->prices = $this->getPrices();
    $this->form->spectators = $this->getSpectators();
  }
  
  protected function getPrices()
  {
    $q = Doctrine::getTable('Price')->createQuery('p')
      ->leftJoin('p.Tickets t')
      ->leftJoin('t.Transaction tr')
      ->leftJoin('tr.Contact c')
      ->leftJoin('tr.Professional pro')
      ->leftJoin('pro.Organism o')
      ->leftJoin('tr.Order order')
      ->leftJoin('t.Controls ctrl')
      ->leftJoin('ctrl.Checkpoint cp')
      ->andWhere('t.cancelling IS NULL')
      ->andWhere('t.duplicate IS NULL')
      ->andWhere('t.id NOT IN (SELECT tt.cancelling FROM ticket tt WHERE tt.cancelling IS NOT NULL)')
      ->andWhere('t.manifestation_id = ?',$this->manifestation->id)
      ->andWhere('cp.legal IS NULL OR cp.legal = true')
      ->orderBy('p.name, tr.id, o.name, c.name, c.firstname');
    return $q->execute();
  }
  protected function getSpectators()
  {
    $q = Doctrine::getTable('Transaction')->createQuery('tr')
      ->leftJoin('tr.Contact c')
      ->leftJoin('tr.Professional pro')
      ->leftJoin('tr.Order order')
      ->leftJoin('tck.Controls ctrl')
      ->leftJoin('tck.Price p')
      ->leftJoin('ctrl.Checkpoint cp')
      ->leftJoin('pro.Organism o')
      ->andWhere('tck.cancelling IS NULL')
      ->andWhere('tck.duplicate IS NULL')
      ->andWhere('tck.id NOT IN (SELECT tt.cancelling FROM ticket tt WHERE tt.cancelling IS NOT NULL)')
      ->andWhere('tck.manifestation_id = ?',$this->manifestation->id)
      ->andWhere('cp.legal IS NULL OR cp.legal = true')
      ->orderBy('c.name, c.firstname, o.name, p.name');
    return $q->execute();
  }
}
