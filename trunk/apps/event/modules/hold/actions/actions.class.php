<?php

require_once dirname(__FILE__).'/../lib/holdGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/holdGeneratorHelper.class.php';

/**
 * hold actions.
 *
 * @package    e-venement
 * @subpackage hold
 * @author     Baptiste SIMON <baptiste.simon AT e-glop.net>
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class holdActions extends autoHoldActions
{
  public function executeCss(sfWebRequest $request)
  {
    $this->forward404Unless($manifestation = Doctrine::getTable('Manifestation')->createQuery('m')
      ->leftJoin('m.Holds h')
      ->andWhere('m.id = ?', str_replace('.css', '', $request->getParameter('manifestation_id')))
      ->fetchOne()
    );
    $this->holds = $manifestation->Holds;
  }
  public function executeGetTransactionIdForTicket(sfWebRequest $request)
  {
    $this->ticket = Doctrine::getTable('Ticket')->find($request->getParameter('ticket_id',0));
    $this->forward404Unless($this->ticket);
  }
  public function executeWaitingList(sfWebRequest $request)
  {
    $this->getContext()->getConfiguration()->loadHelpers('CrossAppLink');
    $this->redirect(cross_app_url_for('tck', 'hold_transaction/index?hold_id='.$request->getParameter('id')));
  }
  public function executeGetBackSeatsFromTransactionId(sfWebRequest $request)
  {
    $this->cpt = array('expected' => 0, 'realized' => 0);
    $template = 'Success';
    
    $q = Doctrine::getTable('Transaction')->createQuery('t')
      ->leftJoin('m.Holds h')
      ->andWhere('tck.printed_at IS NULL AND tck.integrated_at IS NULL')
      ->andWhere('tck.seat_id IS NOT NULL')
      ->andWhere('h.id = ?', $request->getParameter('id'))
      ->andWhere('t.id = ?', $this->transaction_id = $request->getParameter('source'))
      ->andWhere('t.closed = ? OR ?', array(false, $this->getUser()->hasCredential('tck-unblock')))
    ;
    if (!( $this->transaction = $q->fetchOne() ))
      return $template;
    $this->transaction->closed = false;
    $this->transaction->save();
    
    $this->cpt['expected'] = $this->transaction->Tickets->count();
    foreach ( $this->transaction->Tickets as $ticket )
    try
    {
      $hc = new HoldContent;
      $hc->seat_id = $ticket->seat_id;
      $hc->hold_id = $request->getParameter('id');
      $hc->save();
      
      $ticket->save();
      $this->cpt['realized']++;
    } catch ( Exception $e ) {
      error_log($e);
    }
    
    if ( $this->cpt['expected'] == $this->cpt['realized'] )
      $this->transaction->Order->delete();
  }
  public function executeGetTransactionId(sfWebRequest $request)
  {
    $this->transaction = new Transaction;
    $this->transaction->Order[0] = new Order; // book the future content in advance
    $this->transaction->save();
  }
  public function executeLinkSeat(sfWebRequest $request)
  {
    $this->res = array('success' => false, 'type' => 'add');
    $arr = array(
      'seat_id' => $request->getParameter('seat_id'),
      'hold_id' => $request->getParameter('id'),
    );
    
    $this->form = new HoldContentForm;
    $this->form->bind($arr + array(
      $this->form->getCSRFFieldName() => $this->form->getCSRFToken(),
    ));
    if ( $this->form->isValid() )
    {
      try {
        $this->form->save();
        $this->res['success'] = true;
      }
      catch ( Doctrine_Connection_Exception $e ) {
        $this->form = new HoldContentForm(Doctrine::getTable('HoldContent')->find($arr));
        $this->res['type'] = 'remove';
        
        // delete the HoldContent
        if ( Doctrine::getTable('HoldContent')->find($arr)->delete() );
          $this->res['success'] = true;
        
        // switch to a booked seat (w/ a ticket)
        try {
          $tid = trim($request->getParameter('transaction_id'));
          if ( ($transaction = Doctrine::getTable('Transaction')->find($tid)) instanceof Transaction
            && !$transaction->closed
            && $transaction->sf_guard_user_id == $this->getUser()->getId()
          )
          {
            $ticket = new Ticket;
            $ticket->price_name = 'WIP';
            $ticket->seat_id = $arr['seat_id'];
            $ticket->value = 0;
            $ticket->Transaction = $transaction;
            $ticket->Manifestation = Doctrine::getTable('Hold')->find($arr['hold_id'])->Manifestation;
            $ticket->save();
          }
        } catch ( Doctrine_Exception $e ) { error_log($e); }
      }
    }
    
    if ( sfConfig::get('sf_web_debug', false) && $request->hasParameter('debug') )
      return 'Success';
    return 'Json';
  }
  
  public function executeAjax(sfWebRequest $request)
  {
    $charset = sfConfig::get('software_internals_charset');
    $search  = $this->sanitizeSearch($request->getParameter('q'));
    
    $q = Doctrine::getTable('Hold')->createQuery('h')
      ->orderBy('ht.name')
      ->limit($request->getParameter('limit'))
      ->andWhere('ht.name ILIKE ?', '%'.$search.'%')
    ;
    
    switch ( $request->getParameter('with', 'next') ) {
    case 'next':
      $q->leftJoin('h.Next n')
        ->andWhere('n.id IS NOT NULL');
      break;
    case 'feeders':
      $q->leftJoin('h.Feeders f')
        ->andWhere('g.id IS NOT NULL');
      break;
    }

    $this->holds = array();
    foreach ( $q->execute() as $hold )
      $this->holds[$hold->id] = (string)$hold;
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
}
