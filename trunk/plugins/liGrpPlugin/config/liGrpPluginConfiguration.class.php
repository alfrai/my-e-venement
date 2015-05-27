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
*    Copyright (c) 2006-2015 Baptiste SIMON <baptiste.simon AT e-glop.net>
*    Copyright (c) 2006-2015 Libre Informatique [http://www.libre-informatique.fr/]
*
***********************************************************************************/
?>
<?php

class liGrpPluginConfiguration extends sfPluginConfiguration
{
  public function setup()
  {
    error_log('plugin setup');
    $this->dispatcher->connect('tck.ticket_post_cancelling', array($this, 'listenToPostCancelling'));
  }
  
  public function listenToPostCancelling(sfEvent $event)
  {
    $ticket = $event->getSubject();
    $q = Doctrine::getTable('EntryTickets')->createQuery('et')
      ->andWhere('et.gauge_id = ?', $ticket->gauge_id)
      ->andWhere('et.price_id = ?', $ticket->price_id)
      ->andWhere('et.quantity > 0')
      ->leftJoin('et.EntryElement ee')
      ->leftJoin('ee.ContactEntry ce')
      ->andWhere('ce.transaction_id = ?', $ticket->Cancelled->transaction_id)
    ;
    $et = $q->fetchOne();
    
    if ( $et )
    {
      if ( sfConfig::get('sf_web_debug', true) )
        $this->log(new liEvenementException('Removing one ticket in the "grp" module from a ticket cancellation (#'.$ticket->Cancelled->id.')'), false);
      $et->quantity--;
      $et->save();
    }
  }
  
  /**
   * Function that helps making dispatcher calls fail-proof
   * @param $e Exception
   * @return void
   **/
  public function log(Exception $e, $throw = true)
  {
    error_log('liGrpPlugin: '.$e->getMessage());
    if ( $throw && sfContext::hasInstance() && sfConfig::get('sf_web_debug', false) )
      throw $e;
  }
  
  /**
   * returns the dispatcher
   * @return sfEventDispatcher
   **/
  public function getDispatcher()
  {
    return $this->dispatcher;
  }
}

