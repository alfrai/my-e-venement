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
    require(dirname(__FILE__).'/slide-happens-at.php');
    return sfView::NONE;
  }
  
  public function executeSlideDuration(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/slide-duration.php');
    return sfView::NONE;
  }
  
  public function executeExport(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/export.php');
    return sfView::NONE;
  }
  public function executeCsv(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/csv.php');
  }
  public function executeDuplicate(sfWebRequest $request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
    
    $manif = Doctrine_Query::create()->from('Manifestation m')
      ->leftJoin('m.PriceManifestations p')
      ->leftJoin('m.Gauges g')
      ->leftJoin('m.Organizers o')
      ->andWhere('m.id = ?',$request->getParameter('id',0))
      ->fetchOne()
      ->duplicate();
    
    $this->getUser()->setFlash('notice',__('The manifestation has been duplicated successfully.'));
    $this->redirect('manifestation/edit?id='.$manif->id);
  }
  public function executePeriodicity(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/periodicity.php');
  }
  public function executeNew(sfWebRequest $request)
  {
    if ( !$this->getUser()->hasCredential('event-reservation-change-contact') && !$this->getUser()->getContact() )
    {
      if ( $request->hasParameter('event') )
        $event_id = $request->hasParameter('event')
          ? Doctrine::getTable('Event')->findOneBySlug($request->getParameter('event'))->id
          : $event_id = 0;
      
      $this->getUser()->setFlash('error','You cannot access this object, you do not have the required credentials.');
      $this->redirect($event_id ? 'event/show?id='.$event_id : 'event/index');
    }
    
    parent::executeNew($request);
    
    if ( $request->getParameter('event') )
    {
      $event = Doctrine::getTable('Event')->findOneBySlug($request->getParameter('event'));
      if ( $event->id )
      {
        $this->form->setDefault('event_id', $event->id);
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
      $this->form->setDefault('location_id', $location->id);
    }
    
    // booking_list
    if ( ($list = $request->getParameter('booking_list', $this->getUser()->getFlash('booking_list',array())))
      && is_array($list) )
      $this->form->setDefault('booking_list', $list);
  }
  
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('@event');
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $search  = iconv($charset['db'],$charset['ascii'],strtolower($request->getParameter('q')));
    
    $eids = array();
    if ( $search )
    {
      $e = Doctrine_Core::getTable('Event')->search($search.'*',Doctrine::getTable('Event')->createQuery());
      foreach ( $e->execute() as $event )
        $eids[] = $event['id'];
    }
    
    if (!( $max = $request->getParameter('max',sfConfig::get('app_manifestations_max_ajax')) ))
    {
      $conf = sfConfig::get('app_transaction_manifs', array());
      $max = isset($conf['max_display']) && $conf['max_display'] ? $conf['max_display'] : 10;
    }
    
    $q = Doctrine::getTable('Manifestation')
      ->createQuery('m')
      ->leftJoin('m.Color c')
      ->orderBy('m.happens_at')
      ->limit($request->getParameter('limit',$max));
    if ( $eids )
      $q->andWhereIn('m.event_id',$eids);
    elseif ( $search )
      $q->andWhere('m.event_id IS NULL');
    
    if ( $e = $request->getParameter('except',false) )
      $q->andWhereNotIn('m.id', is_array($e) ? $e : array($e));
     
    $q = EventFormFilter::addCredentialsQueryPart($q);
    
    if ( !$search
      || $request->hasParameter('later')
      || $request->getParameter('except_transaction',false) && !$this->getUser()->hasCredential('tck-unblock') )
      $q->andWhere('m.happens_at > NOW()');
    
    $manifestations = $q->select('m.*, e.*, c.*')->execute();
    
    $manifs = array();
    foreach ( $manifestations as $manif )
    {
      $go = true;
      if ( $request->getParameter('except_transaction',false) )
      {
        $go = $manif->reservation_confirmed;
        $go = $go && Doctrine_Query::create()->from('ticket tck')
          ->andWhere('tck.manifestation_id = ?', $manif->id)
          ->andWhere('tck.transaction_id = ?', intval($request->getParameter('except_transaction')))
          ->count() == 0;
      }
      
      if ( $go )
      {
        if ( $request->hasParameter('keep-order') )
        {
          $manifs[] = array(
            'name'  => (string)$manif,
            'color' => (string)$manif->Color,
            'id'    => $manif->id,
          );
        }
        else
        {
          $manifs[$manif->id] = $request->hasParameter('with_colors')
            ? array('name' => (string)$manif, 'color' => (string)$manif->Color)
            : (string)$manif;
        }
      }
    }
    
    if ( $request->hasParameter('debug') && $this->getContext()->getConfiguration()->getEnvironment() == 'dev' )
    {
      $this->getResponse()->setContentType('text/html');
      sfConfig::set('sf_debug',true);
      $this->setLayout('layout');
    }
    else
    {
      sfConfig::set('sf_debug',false);
      sfConfig::set('sf_escaping_strategy', false);
    }
    
    $this->json = $manifs;
  }

  public function executeList(sfWebRequest $request)
  {
    require(dirname(__FILE__).'/list.php');
  }
  public function executeEventList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('manifestation','index');
    
    $this->event_id = $request->getParameter('id');
    
    $this->pager = $this->configuration->getPager('Contact');
    $this->pager->setMaxPerPage(10);
    $this->pager->setQuery(
      $q = EventFormFilter::addCredentialsQueryPart(
        Doctrine::getTable('Manifestation')->createQueryByEventId($this->event_id)
        ->select('*, g.*, l.*, tck.*, m.happens_at > NOW() AS after, (CASE WHEN happens_at < NOW() THEN NOW()-happens_at ELSE happens_at-NOW() END) AS before')
        ->andWhere('m.reservation_confirmed = TRUE OR m.contact_id = ? OR ?', array(
          $this->getUser()->getContactId(),
          $this->getUser()->hasCredential(array(
            'event-access-all',
          ), false)))
        //->leftJoin('m.Tickets tck')
        ->orderBy('after DESC, before')
    ));
    $this->pager->setPage($request->getParameter('page') ? $request->getParameter('page') : 1);
    $this->pager->init();
  }
  public function executeLocationList(sfWebRequest $request)
  {
    if ( !$request->getParameter('id') )
      $this->forward('manifestation','index');
    
    $place = !$request->hasParameter('resource');
    
    $this->location_id = $request->getParameter('id');
    
    $this->pager = $this->configuration->getPager('Manifestation');
    $this->pager->setMaxPerPage(10);
    $this->pager->setQuery(
      EventFormFilter::addCredentialsQueryPart(
        Doctrine::getTable('Manifestation')->createQueryByLocationId($this->location_id)
        ->select('m.*, e.*, c.*, g.*, l.*')
        ->leftJoin('m.Color c')
        ->andWhere('m.reservation_confirmed = TRUE OR m.contact_id = ?', $this->getUser()->getContactId())
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
    require(dirname(__FILE__).'/templating.php');
  }
  
  protected function securityAccessFiltering(sfWebRequest $request, $deep = true)
  {
    if ( intval($request->getParameter('id')).'' !== ''.$request->getParameter('id') )
      return;
    
    $sf_user = $this->getUser();
    $manifestation = $this->getRoute()->getObject();
    if ( !in_array($manifestation->Event->meta_event_id,array_keys($sf_user->getMetaEventsCredentials())) )
    {
      $this->getUser()->setFlash('error',"You cannot access this object, you do not have the required credentials.");
      $this->redirect('@event');
    }
    
    $config = sfConfig::get('app_manifestation_reservations',array('enable' => false));
    if ( !$sf_user->hasCredential('event-manif-edit-confirmed') && !(isset($config['let_restricted_users_confirm']) && $config['let_restricted_users_confirm']) )
      error_log('no edition');
    if ( $deep )
    if ( $manifestation->contact_id !== $sf_user->getContactId() && !$sf_user->hasCredential('event-access-all')
      || $manifestation->reservation_confirmed && !$sf_user->hasCredential('event-manif-edit-confirmed') && $manifestation->contact_id !== $sf_user->getContactId()
      || !(isset($config['let_restricted_users_confirm']) && $config['let_restricted_users_confirm']) && !$sf_user->hasCredential('event-manif-edit-confirmed') )
    {
      $this->getUser()->setFlash('error',"You cannot edit this object, you do not have the required credentials.");
      $this->redirect('manifestation/show?id='.$manifestation->id);
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
    
    //$this->form->prices = $this->getPrices();
    //$this->form->spectators = $this->getSpectators();
    //$this->form->unbalanced = $this->getUnbalancedTransactions();
  }
  public function executeUpdate(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request);
    parent::executeUpdate($request);
    //$this->form->prices = $this->getPrices();
    //$this->form->spectators = $this->getSpectators();
    //$this->form->unbalanced = $this->getUnbalancedTransactions();
  }
  public function executeShow(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request, false);
    $this->manifestation = $this->getRoute()->getObject();
    $this->forward404Unless($this->manifestation);
    $this->form = $this->configuration->getForm($this->manifestation);
    //$this->form->prices = $this->getPrices();
    //$this->form->spectators = $this->getSpectators();
    $this->form->unbalanced = $this->getUnbalancedTransactions();
  }
  public function executeVersions(sfWebRequest $request)
  {
    $this->executeShow($request);
    
    if ( !($v = $request->getParameter('version',false)) )
      $v = $this->manifestation->version > 1 ? $this->manifestation->version - 1 : 1;
    
    if ( intval($v).'' == ''.$v )
    foreach ( $this->manifestation->Version as $version )
    if ( $version->version == $v )
    {
      $this->manifestation->current_version = $version;
      break;
    }
    
    if ( !$this->manifestation->current_version )
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('I18N');
      $this->getUser()->setFlash('error', __('You have requested the version %%v%% that does not exist', array('%%v%%' => $v)));
      $this->redirect('manifestation/show?id='.$this->manifestation->id);
    }
  }
  
  public function executeShowSpectators(sfWebRequest $request)
  {
    $this->securityAccessFiltering($request, false);
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
    $this->securityAccessFiltering($request, false);
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
  
  protected function getSpectators($manifestation_id = NULL, $only_printed_tck = false)
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
    $q->leftJoin('tr.Tickets tck'.($only_printed_tck ? ' ON tck.transaction_id = tr.id AND (tck.printed_at IS NOT NULL OR tck.integrated_at IS NOT NULL OR tck.cancelling IS NOT NULL)' : ''))
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
        $this->getUser()->setFlash('success', $notice.' You can add another one below.');

        $this->redirect('@manifestation_new');
      }
      else
      {
        $this->getUser()->setFlash('success', $notice);
        
        $this->redirect(array('sf_route' => 'manifestation_edit', 'sf_subject' => $manifestation));
      }
    }
    else
    {
      $this->getUser()->setFlash('error', 'The item has not been saved due to some errors.', false);
    }
  }
}
