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
  }
  public function executeCash(sfWebRequest $request)
  {
    $this->form = new LedgerCriteriasForm();
    $criterias = $request->getParameter('criterias');
    print_r($criterias);
    $this->form->bind($criterias);
    if ( $this->form->isValid() )
    {
      die('glop');
    }
    else
    {
      die('pas glop');
    }
      $dates = array(
        $request->getParameter('b') ? $request->getParameter('b') : strtotime('1 month ago 0:00'),
        $request->getParameter('e') ? $request->getParameter('e') : strtotime('tomorrow 0:00'),
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
      ->andWhere('p.created_at >= ? AND p.created_at < ?',array(date('Y-m-d',$dates[0]),date('Y-m-d',$dates[1])))
      ->orderBy('m.name, m.id, p.value, p.created_at, t.id');
    
    if ( $request->hasParameter('by_seller') )
    {
      if ( intval($request->getParameter('by_seller')) > 0 )
      {
        $q->leftJoin('p.User u')
          ->andWhere('p.sf_guard_user_id = ?',
          intval($request->getParameter('by_seller')) > 0
            ? intval($request->getParameter('by_seller'))
            : $this->getUser()->getId()
        );
      }
    }
    
    $this->methods = $q->execute();
    $this->dates = $dates;
  }
}
