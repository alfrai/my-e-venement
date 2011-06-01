<?php

/**
 * ledger actions.
 *
 * @package    e-venement
 * @subpackage ledger
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ledgerActions extends sfActions
{
 /**
  * Executes index action
  *
  * @param sfRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->redirect('ledger/sales');
  }
  
  public function executeSales(sfWebRequest $request)
  {
    $this->form = new LedgerCriteriasForm();
    
    $criterias = $request->getParameter($this->form->getName());
    
    if ( !$criterias['users'][0] && count($criterias['users']) == 1 )
      unset($criterias['users']);
    
    $this->form->bind($criterias, $request->getFiles($this->form->getName()));
    if ( !$this->form->isValid() )
    {
      $user->setFlash('error','Submitted values are invalid');
    }
    
    $dates = array(
      $criterias['dates']['from']['day']
        ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
        : strtotime('1 month ago 0:00'),
      $criterias['dates']['to']['day']
        ? strtotime($criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'])
        : strtotime('tomorrow 0:00'),
    );
    
    if ( $dates[0] > $dates[1] )
    {
      $buf = $dates[1];
      $dates[1] = $dates[0];
      $dates[0] = $buf;
    }
    
    $q = Doctrine::getTable('Event')->createQuery('e')
      ->leftJoin('e.Manifestations m')
      ->leftJoin('m.Location l')
      ->leftJoin('m.Tickets tck')
      ->leftJoin('tck.Transaction t')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism o')
      ->leftJoin('tck.User u')
      ->andWhere('tck.created_at >= ? AND tck.created_at < ?',array(
        date('Y-m-d',$dates[0]),
        date('Y-m-d',$dates[1]),
      ))
      ->andWhere('tck.duplicate IS NULL')
      ->andWhere('tck.printed = TRUE')
      ->orderBy('e.name, m.happens_at, l.name, tck.price_name, tck.created_at');
    
    $q->andWhereIn('t.type',array('normal', 'cancellation'));
    
    if ( ($criterias['users']) > 0 && $criterias['users'][0] )
      $q->andWhereIn('t.sf_guard_user_id',$criterias['users']);
    
    $this->events = $q->execute();
    $this->dates = $dates;
  }
  
  public function executeCash(sfWebRequest $request)
  {
    $this->form = new LedgerCriteriasForm();
    
    $criterias = $request->getParameter($this->form->getName());
    
    if ( !$criterias['users'][0] && count($criterias['users']) == 1 )
      unset($criterias['users']);
    
    $this->form->bind($criterias, $request->getFiles($this->form->getName()));
    if ( !$this->form->isValid() )
    {
      $user->setFlash('error','Submitted values are invalid');
    }
    
    $dates = array(
      $criterias['dates']['from']['day']
        ? strtotime($criterias['dates']['from']['year'].'-'.$criterias['dates']['from']['month'].'-'.$criterias['dates']['from']['day'])
        : strtotime('1 month ago 0:00'),
      $criterias['dates']['to']['day']
        ? strtotime($criterias['dates']['to']['year'].'-'.$criterias['dates']['to']['month'].'-'.$criterias['dates']['to']['day'])
        : strtotime('tomorrow 0:00'),
    );
    
    if ( $dates[0] > $dates[1] )
    {
      $buf = $dates[1];
      $dates[1] = $dates[0];
      $dates[0] = $buf;
    }
    
    $q = Doctrine::getTable('PaymentMethod')->createQuery('m')
      ->leftJoin('m.Payments p')
      ->leftJoin('p.Transaction t')
      ->leftJoin('t.Contact c')
      ->leftJoin('t.Professional pro')
      ->leftJoin('pro.Organism o')
      ->leftJoin('p.User u')
      ->leftJoin('u.MetaEvents')
      ->leftJoin('u.Workspaces')
      ->andWhere('p.created_at >= ? AND p.created_at < ?',array(
        date('Y-m-d',$dates[0]),
        date('Y-m-d',$dates[1]),
      ))
      ->orderBy('m.name, m.id, p.value, p.created_at, t.id');
    
    if ( ($criterias['users']) > 0 && $criterias['users'][0] )
      $q->andWhereIn('p.sf_guard_user_id',$criterias['users']);
    
    $this->methods = $q->execute();
    $this->dates = $dates;
  }
}
