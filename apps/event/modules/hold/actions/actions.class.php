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
  public function executeGetTransactionId(sfWebRequest $request)
  {
    $this->transaction = new Transaction;
    //$this->transaction->HoldTransaction->hold_id = $request->getParameter('id');
    $this->transaction->Order[0] = new Order;
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
          if ( intval($tid).'' === ''.$tid )
          {
            $ticket = new Ticket;
            $ticket->price_name = 'WIP';
            $ticket->seat_id = $arr['seat_id'];
            $ticket->value = 0;
            $ticket->transaction_id = $tid;
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
