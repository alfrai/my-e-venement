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
*    Foundation, Inc., 5'.$rank.' Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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
  public function executeSlideHappensAt(sfWebRequest $request)
  {
    $this->manifestation = Doctrine::getTable('Manifestation')->createQuery('m')
      ->andWhere('m.id = ?',$request->getParameter('id'))
      ->fetchOne();
    $this->forward404Unless($request->hasParameter('days') && $request->hasParameter('minutes') && $this->manifestation);
    
    $this->manifestation->happens_at = date('Y-m-d H:i:s',
      strtotime($this->manifestation->happens_at) +
      $request->getParameter('days') * 24 * 60 * 60 +
      $request->getParameter('minutes') * 60 );
    
    $this->manifestation->save();
    
    return sfView::NONE;
  }
  
  public function executeSlideDuration(sfWebRequest $request)
  {
    $this->manifestation = Doctrine::getTable('Manifestation')->createQuery('m')
      ->andWhere('m.id = ?',$request->getParameter('id'))
      ->fetchOne();
    $this->forward404Unless($request->hasParameter('days') && $request->hasParameter('minutes') && $this->manifestation);
    
    $this->manifestation->duration = $str = $this->manifestation->duration +
      $request->getParameter('days') * 24 * 60 * 60 +
      $request->getParameter('minutes') * 60;
    
    $this->manifestation->save();
    
    return sfView::NONE;
  }
  
  public function executeExport(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers(array('Date','CrossAppLink'));
    $manifestation = $this->getRoute()->getObject();
    
    $q = new Doctrine_Query;
    $q->from('Contact c')
      ->leftJoin('c.Transactions t')
      ->leftJoin('t.Professional tp')
      ->leftJoin('t.Tickets tck')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('c.Professionals cp ON c.id = cp.contact_id AND (cp.id = tp.id OR cp.id IS NULL AND tp.id IS NULL)')
      ->select('c.*, cp.*')
      ->andWhere('m.id = ?',$manifestation->id)
      ->andWhere('tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT tck2.cancelling FROM Ticket tck2 WHERE tck2.cancelling IS NOT NULL)');
    
    switch ( $request->getParameter('status') ) {
    case 'asked':
      $q->leftJoin('t.Order o')
        ->andWhere('o.id IS NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL');
      break;
    case 'ordered':
      $q->leftJoin('t.Order o')
        ->andWhere('o.id IS NOT NULL')
        ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL');
      break;
    default:
      $q->andWhere('(tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL)');
      break;
    }

    $contacts = $q->execute();
    
    $group = new Group;
    $group->name = $manifestation.' / '.format_datetime(date('Y-m-d H:i:s'));
    $group->sf_guard_user_id = $this->getUser()->getId();
    
    foreach ( $contacts as $contact )
    if ( $contact->Professionals->count() > 0 )
    foreach ( $contact->Professionals as $professional )
      $group->Professionals[] = $professional;
    else
      $group->Contacts[] = $contact;
    
    $group->save();
    $this->redirect(cross_app_url_for('rp','group/show?id='.$group->id));
    return sfView::NONE;
  }
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
        
        $ws = $this->form->getWidgetSchema();
        $ws['duration']->setOption('default',$event->duration);
        $ws['vat_id']->setOption('default',$event->EventCategory->vat_id);
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
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],$request->getParameter('q'));
    
    $e = Doctrine_Core::getTable('Event')->search($search.'*',Doctrine::getTable('Event')->createQuery());
    
    $eids = array();
    foreach ( $e->execute() as $event )
      $eids[] = $event['id'];
    
    if ( count($eids) > 0 )
    {
      $q = Doctrine::getTable('Manifestation')
        ->createQuery()
        ->andWhereIn('event_id',$eids)
        ->orderBy('happens_at')
        ->limit($request->getParameter('limit'));
      $q = EventFormFilter::addCredentialsQueryPart($q);
      
      if ( $request->hasParameter('later') )
        $q->andWhere('happens_at > NOW()');
      
      $manifestations = $q->execute()->getData();
      
      $manifs = array();
      foreach ( $manifestations as $manif )
      if ( $request->getParameter('except',false) != $manif->id )
       $manifs[$manif->id] = (string) $manif;
    
      return $this->renderText(json_encode($manifs));
    }
    else
    {
      return $this->renderText(json_encode(array()));
    }
  }

  public function executeList(sfWebRequest $request)
  {
    $this->location_id = $request->getParameter('location_id');
    $this->event_id = $request->getParameter('event_id');
    
    $from = date('Y-m-01', $request->getParameter('start',strtotime('now')));
    $to = date('Y-m-01', $request->getParameter('end',strtotime('+ 1 month')));
    
    $q = Doctrine::getTable('Manifestation')->createQuery('m')
      ->select('m.*, l.*, e.*, g.*')
      ->andWhere('m.happens_at >= ?',$from)
      ->andWhere('m.happens_at <  ?',$to)
      ->orderBy('happens_at');
    if ( $this->location_id ) $q->andWhere('m.location_id = ?',$this->location_id);
    if ( $this->event_id ) $q->andwhere('m.event_id = ?',$this->event_id);
    EventFormFilter::addCredentialsQueryPart($q);
    $this->manifestations = $q->execute();
    $this->forward404Unless($this->manifestations);
  }
  public function executeEventList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('manifestation','index');
    
    $this->event_id = $request->getParameter('id');
    
    $this->pager = $this->configuration->getPager('Contact');
    $this->pager->setMaxPerPage(10);
    $this->pager->setQuery(
      EventFormFilter::addCredentialsQueryPart(
        Doctrine::getTable('Manifestation')->createQueryByEventId($this->event_id)
        ->select('*, g.*, l.*, tck.*, happens_at > NOW() AS after, (CASE WHEN ( happens_at < NOW() ) THEN NOW()-happens_at ELSE happens_at-NOW() END) AS before')
        //->leftJoin('m.Tickets tck')
        ->orderBy('before')
    ));
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
  }
  public function executeLocationList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('manifestation','index');
    
    $this->location_id = $request->getParameter('id');
    
    $this->pager = $this->configuration->getPager('Manifestation');
    $this->pager->setMaxPerPage(10);
    $this->pager->setQuery(
      EventFormFilter::addCredentialsQueryPart(
        Doctrine::getTable('Manifestation')->createQueryByLocationId($this->location_id)
        ->select('m.*, e.*, c.*, g.*, l.*')
        ->leftJoin('m.Color c')
        ->addSelect('m.happens_at > NOW() AS after, (CASE WHEN ( m.happens_at < NOW() ) THEN NOW()-m.happens_at ELSE m.happens_at-NOW() END) AS before')
        //->addSelect('tck.*')
        //->leftJoin('m.Tickets tck')
        ->orderBy('before')
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
    if ( intval($request->getParameter('id')).'' !== ''.$request->getParameter('id') )
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
    parent::executeEdit($request);
    $this->securityAccessFiltering($request);
    //$this->form->prices = $this->getPrices();
    //$this->form->spectators = $this->getSpectators();
    //$this->form->unbalanced = $this->getUnbalancedTransactions();
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->manifestation = $this->getRoute()->getObject();
    $this->securityAccessFiltering($request);
    $this->forward404Unless($this->manifestation);
    $this->form = $this->configuration->getForm($this->manifestation);
    //$this->form->prices = $this->getPrices();
    //$this->form->spectators = $this->getSpectators();
    $this->form->unbalanced = $this->getUnbalancedTransactions();
  }
  public function executeShowSpectators(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    $this->manifestation_id = $request->getParameter('id');
    $this->spectators = $this->getSpectators($request->getParameter('id'));
    $this->show_workspaces = Doctrine_Query::create()
      ->from('Gauge g')
      ->leftJoin('g.Manifestation m')
      ->andWhere('m.id = ?',$this->manifestation_id)
      ->execute()
      ->count() > 1;
    $this->setLayout('nude');
  }
  public function executeShowTickets(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    $this->prices = $this->getPrices($request->getParameter('id'));
    $this->setLayout('nude');
  }
  
  protected function countTickets($manifestation_id)
  {
    $q = '
      SELECT count(*) AS nb
      FROM ticket
      WHERE cancelling IS NULL
        AND duplicating IS NULL
        AND id NOT IN (SELECT cancelling FROM ticket WHERE cancelling IS NOT NULL)
        AND manifestation_id = :manifestation_id';
    $pdo = Doctrine_Manager::getInstance()->getCurrentConnection()->getDbh();
    $stmt = $pdo->prepare($q);
    $stmt->execute(array('manifestation_id' => $manifestation_id));
    $tmp = $stmt->fetchAll();
    
    return $tmp[0]['nb'];
  }
  
  protected function getPrices($manifestation_id = NULL)
  {
    $mid = $manifestation_id ? $manifestation_id : $this->manifestation->id;
    $nb = $this->countTickets($mid);
    $q = Doctrine::getTable('Price')->createQuery('p');
    
    if ( $nb < 7500 )
    $q->leftJoin('p.Tickets t')
      ->leftJoin('t.Duplicatas duplicatas')
      ->leftJoin('duplicatas.Cancelling cancelling2')
      ->leftJoin('t.Cancelling cancelling')
      ->leftJoin('t.Transaction tr')
      ->leftJoin('tr.Contact c')
      ->leftJoin('tr.Professional pro')
      ->leftJoin('pro.Organism o')
      ->leftJoin('tr.Order order')
      ->leftJoin('t.Controls ctrl')
      ->leftJoin('ctrl.Checkpoint cp')
      ->leftJoin('t.Gauge g')
      ->leftJoin('g.Workspace w')
      ->andWhere('t.cancelling IS NULL')
      ->andWhere('t.id NOT IN (SELECT tt2.cancelling FROM ticket tt2 WHERE tt2.cancelling IS NOT NULL)')
      ->andWhere('t.id NOT IN (SELECT tt.duplicating FROM Ticket tt WHERE tt.duplicating IS NOT NULL)')
      ->andWhere('t.manifestation_id = ?',$mid)
      ->andWhere('cp.legal IS NULL OR cp.legal = true')
      ->andWhereIn('g.workspace_id',array_keys($this->getUser()->getWorkspacesCredentials()))
      ->andWhere('p.id IN (SELECT up.price_id FROM UserPrice up WHERE up.sf_guard_user_id = ?) OR (SELECT count(up2.price_id) FROM UserPrice up2 WHERE up2.sf_guard_user_id = ?) = 0',array($this->getUser()->getId(),$this->getUser()->getId()))
      ->orderBy('g.workspace_id, w.name, p.name, tr.id, o.name, c.name, c.firstname');
    else
    {
      $params = array();
      for ( $i = 0 ; $i < 7 ; $i++ )
        $params[] = $mid;
      $q->select('p.*')
        ->andWhere('p.id IN (SELECT DISTINCT t0.price_id FROM Ticket t0 WHERE t0.manifestation_id = ?)', $params) // the X $mid is a hack for doctrine
        ->orderBy('p.name');
      $rank = 0;
      foreach ( array(
        'printed' => '(t%%i%%.printed_at IS NOT NULL OR t%%i%%.integrated_at IS NOT NULL)',
        'ordered' => 'NOT (t%%i%%.printed_at IS NOT NULL OR t%%i%%.integrated_at IS NOT NULL) AND t%%i%%.transaction_id IN (SELECT DISTINCT o%%i%%.transaction_id FROM Order o%%i%%)',
        'asked' => 'NOT (t%%i%%.printed_at IS NOT NULL OR t%%i%%.integrated_at IS NOT NULL) AND t%%i%%.transaction_id NOT IN (SELECT DISTINCT o%%i%%.transaction_id FROM Order o%%i%%)'
      ) as $col => $where )
      {
        $rank++;
        $q->addSelect('(SELECT count(t'.$rank.'.id) FROM Ticket t'.$rank.' LEFT JOIN t'.$rank.'.Gauge g'.$rank.' WHERE '.str_replace('%%i%%',$rank,$where).' AND t'.$rank.'.cancelling IS NULL AND t'.$rank.'.id NOT IN (SELECT ttd'.$rank.'.duplicating FROM Ticket ttd'.$rank.' WHERE ttd'.$rank.'.duplicating IS NOT NULL) AND t'.$rank.'.id NOT IN (SELECT tt'.$rank.'.cancelling FROM ticket tt'.$rank.' WHERE tt'.$rank.'.cancelling IS NOT NULL) AND t'.$rank.'.manifestation_id = ? AND g'.$rank.'.workspace_id IN ('.implode(',',array_keys($this->getUser()->getWorkspacesCredentials())).') AND t'.$rank.'.price_id = p.id) AS '.$col, $mid);
        $rank++;
        $q->addSelect('(SELECT sum(t'.$rank.'.value) FROM Ticket t'.$rank.' LEFT JOIN t'.$rank.'.Gauge g'.$rank.' WHERE '.str_replace('%%i%%',$rank,$where).' AND t'.$rank.'.cancelling IS NULL AND t'.$rank.'.duplicating IS NULL AND t'.$rank.'.id NOT IN (SELECT tt'.$rank.'.cancelling FROM ticket tt'.$rank.' WHERE tt'.$rank.'.cancelling IS NOT NULL) AND t'.$rank.'.manifestation_id = ? AND g'.$rank.'.workspace_id IN ('.implode(',',array_keys($this->getUser()->getWorkspacesCredentials())).') AND t'.$rank.'.price_id = p.id) AS '.$col.'_value', $mid);
      }
    }
    $e = $q->execute();
    return $e;
  }
  
  protected function getSpectators($manifestation_id = NULL)
  {
    $mid = $manifestation_id ? $manifestation_id : $this->manifestation->id;
    $nb = $this->countTickets($mid);
    $q = Doctrine_Query::create()->from('Transaction tr')
      ->leftJoin('tr.Contact c')
      ->leftJoin('c.Groups gc')
      ->leftJoin('gc.Picture gcp')
      ->leftJoin('tr.Professional pro')
      ->leftJoin('pro.Groups gpro')
      ->leftJoin('gpro.Picture gprop')
      ->leftJoin('tr.Order order')
      ->leftJoin('tr.User u')
      ->leftJoin('pro.Organism o');
      
    if ( $nb < 7500 )
    $q->leftJoin('tr.Tickets tck')
      ->leftJoin('tck.Duplicatas duplicatas')
      ->leftJoin('duplicatas.Cancelling cancelling2')
      ->leftJoin('tck.Cancelling cancelling')
      ->leftJoin('tr.Invoice invoice')
      ->leftJoin('tck.Cancelled cancelled')
      ->leftJoin('tck.Manifestation m')
      ->leftJoin('tck.Controls ctrl')
      ->leftJoin('tck.Price p')
      ->leftJoin('ctrl.Checkpoint cp')
      ->leftJoin('tck.Gauge g')
      ->leftJoin('g.Workspace w')
      ->andWhere('tck.cancelling IS NULL')
      ->andWhere('tck.id NOT IN (SELECT tt2.cancelling FROM ticket tt2 WHERE tt2.cancelling IS NOT NULL)')
      ->andWhere('tck.id NOT IN (SELECT tt3.duplicating FROM ticket tt3 WHERE tt3.duplicating IS NOT NULL)') // we want only the last duplicates (or originals if no duplication has been made)
      ->andWhere('tck.manifestation_id = ?',$manifestation_id ? $manifestation_id : $this->manifestation->id)
      ->andWhere('(cp.legal IS NULL OR cp.legal = true)')
      ->andWhereIn('g.workspace_id',array_keys($this->getUser()->getWorkspacesCredentials()))
      ->andWhere('p.id IN (SELECT up.price_id FROM UserPrice up WHERE up.sf_guard_user_id = ?) OR (SELECT count(up2.price_id) FROM UserPrice up2 WHERE up2.sf_guard_user_id = ?) = 0',array($this->getUser()->getId(),$this->getUser()->getId()))
      ->orderBy('c.name, c.firstname, o.name, p.name, g.workspace_id, w.name, tr.id');
    else
    {
      $q->select('tr.*, c.*, pro.*, o.*, order.*, u.*')
        ->andWhere('tr.id IN (SELECT DISTINCT t0.transaction_id FROM Ticket t0 WHERE t0.manifestation_id = ?)', $mid)
        ->orderBy('c.name, c.firstname, o.name, tr.id');
      $rank = 0;
      foreach ( array(
        'printed' => '(t%%i%%.printed_at IS NOT NULL OR t%%i%%.integrated_at IS NOT NULL)',
        'ordered' => 'NOT (t%%i%%.printed_at IS NOT NULL OR t%%i%%.integrated_at IS NOT NULL) AND t%%i%%.transaction_id IN (SELECT DISTINCT o%%i%%.transaction_id FROM Order o%%i%%)',
        'asked' => 'NOT (t%%i%%.printed_at IS NOT NULL OR t%%i%%.integrated_at IS NOT NULL) AND t%%i%%.transaction_id NOT IN (SELECT DISTINCT o%%i%%.transaction_id FROM Order o%%i%%)'
      ) as $col => $where )
      {
        $rank++;
        $q->addSelect('(SELECT count(t'.$rank.'.id) FROM Ticket t'.$rank.' LEFT JOIN t'.$rank.'.Gauge g'.$rank.' WHERE '.str_replace('%%i%%',$rank,$where).' AND t'.$rank.'.cancelling IS NULL AND t'.$rank.'.id NOT IN (SELECT ttd'.$rank.'.duplicating FROM Ticket ttd'.$rank.' WHERE ttd'.$rank.'.duplicating IS NOT NULL) AND t'.$rank.'.id NOT IN (SELECT tt'.$rank.'.cancelling FROM ticket tt'.$rank.' WHERE tt'.$rank.'.cancelling IS NOT NULL) AND g'.$rank.'.workspace_id IN ('.implode(',',array_keys($this->getUser()->getWorkspacesCredentials())).') AND t'.$rank.'.transaction_id = tr.id) AS '.$col);
        $rank++;
        $q->addSelect('(SELECT sum(t'.$rank.'.value) FROM Ticket t'.$rank.' LEFT JOIN t'.$rank.'.Gauge g'.$rank.' WHERE '.str_replace('%%i%%',$rank,$where).' AND t'.$rank.'.cancelling IS NULL AND t'.$rank.'.duplicating IS NULL AND t'.$rank.'.id NOT IN (SELECT tt'.$rank.'.cancelling FROM ticket tt'.$rank.' WHERE tt'.$rank.'.cancelling IS NOT NULL) AND g'.$rank.'.workspace_id IN ('.implode(',',array_keys($this->getUser()->getWorkspacesCredentials())).') AND t'.$rank.'.transaction_id = tr.id) AS '.$col.'_value');
      }
    }
    
    $spectators = $q->execute();
    return $spectators;
  }

  protected function getUnbalancedTransactions()
  {
    $con = Doctrine_Manager::getInstance()->connection();
    $st = $con->execute(
      //"SELECT DISTINCT t.*, tl.id AS translinked,
      "SELECT DISTINCT t.*,
              (SELECT CASE WHEN sum(ttt.value) IS NULL THEN 0 ELSE sum(ttt.value) END
               FROM Ticket ttt
               WHERE ttt.transaction_id = t.id
                 AND (ttt.printed_at IS NOT NULL OR ttt.integrated_at IS NOT NULL OR cancelling IS NOT NULL)
                 AND ttt.duplicating IS NULL) AS topay,
              (SELECT CASE WHEN sum(ppp.value) IS NULL THEN 0 ELSE sum(ppp.value) END FROM Payment ppp WHERE ppp.transaction_id = t.id) AS paid,
              c.id AS c_id, c.name, c.firstname,
              p.name AS p_name, o.id AS o_id, o.name AS o_name, o.city AS o_city
       FROM transaction t
       LEFT JOIN contact c ON c.id = t.contact_id
       LEFT JOIN professional p ON p.id = t.professional_id
       LEFT JOIN organism o ON p.organism_id = o.id
       LEFT JOIN transaction tl ON tl.transaction_id = t.id
       WHERE t.id IN (SELECT DISTINCT tt.transaction_id FROM Ticket tt WHERE tt.manifestation_id = ".intval($this->manifestation->id).")
         AND (SELECT CASE WHEN sum(tt.value) IS NULL THEN 0 ELSE sum(tt.value) END FROM Ticket tt WHERE tt.transaction_id = t.id AND (tt.printed_at IS NOT NULL OR tt.integrated_at IS NOT NULL OR tt.cancelling IS NOT NULL) AND tt.duplicating IS NULL)
          != (SELECT CASE WHEN sum(pp.value) IS NULL THEN 0 ELSE sum(pp.value) END FROM Payment pp WHERE pp.transaction_id = t.id)
       ORDER BY t.id ASC");
    $transactions = $st->fetchAll();
    return $transactions;
  }
}
